<?php

namespace App\Services;

use App\Models\ExamMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Synchronizes the Excel definition into exam_master_subjects only while an
 * examination is still unused. Once marks/results exist, the database snapshot
 * is preserved permanently for that examination.
 */
class ExamStructureSyncService
{
    public function __construct(
        private readonly ExamStructureExcelService $excel
    ) {
    }

    public function syncIfDraft(ExamMaster $exam): bool
    {
        if ($this->hasDependentData((int) $exam->id)) {
            return false;
        }

        $currentHash = $this->excel->fileHash();
        $storedHash = Schema::hasColumn('exam_masters', 'exam_structure_hash')
            ? (string) ($exam->exam_structure_hash ?? '')
            : '';

        if ($storedHash !== '' && hash_equals($storedHash, $currentHash)) {
            return false;
        }

        $examType = $this->resolveExamType($exam);

        if (!$examType) {
            return false;
        }

        $structure = $this->excel->getStructure(
            (int) $exam->standard_id,
            $examType
        );

        if (!($structure['success'] ?? false)) {
            return false;
        }

        DB::transaction(function () use ($exam, $structure): void {
            DB::table('exam_master_subjects')
                ->where('exam_master_id', $exam->id)
                ->delete();

            $this->insertSnapshot(
                (int) $exam->id,
                (int) $exam->standard_id,
                $structure
            );

            $payload = [
                'max_marks' => $this->sum($structure['subjects'], 'max_marks'),
                'passing_marks' => $this->sum($structure['subjects'], 'passing_marks'),
            ];

            if (Schema::hasColumn('exam_masters', 'exam_structure_hash')) {
                $payload['exam_structure_hash'] = $structure['file_hash'];
            }
            if (Schema::hasColumn('exam_masters', 'exam_type')) {
                $payload['exam_type'] = $structure['exam_type'];
            }
            if (Schema::hasColumn('exam_masters', 'has_theory')) {
                $payload['has_theory'] = (bool) ($structure['flags']['has_theory'] ?? false);
            }
            if (Schema::hasColumn('exam_masters', 'has_oral')) {
                $payload['has_oral'] = (bool) ($structure['flags']['has_oral'] ?? false);
            }
            if (Schema::hasColumn('exam_masters', 'has_practical')) {
                $payload['has_practical'] = (bool) ($structure['flags']['has_practical'] ?? false);
            }

            foreach ([
                'theory_max_marks',
                'theory_passing_marks',
                'oral_max_marks',
                'oral_passing_marks',
                'practical_max_marks',
                'practical_passing_marks',
            ] as $legacy) {
                if (Schema::hasColumn('exam_masters', $legacy)) {
                    $payload[$legacy] = 0;
                }
            }

            if (Schema::hasColumn('exam_masters', 'updated_at')) {
                $payload['updated_at'] = now();
            }

            DB::table('exam_masters')
                ->where('id', $exam->id)
                ->update($payload);
        });

        return true;
    }

    public function syncAllDrafts(): int
    {
        $count = 0;
        $hash = $this->excel->fileHash();

        ExamMaster::query()
            ->where('is_active', 1)
            ->when(
                Schema::hasColumn('exam_masters', 'exam_structure_hash'),
                fn ($q) => $q->where(function ($q2) use ($hash) {
                    $q2->whereNull('exam_structure_hash')
                        ->orWhere('exam_structure_hash', '<>', $hash);
                })
            )
            ->orderBy('id')
            ->chunkById(50, function ($exams) use (&$count): void {
                foreach ($exams as $exam) {
                    if ($this->syncIfDraft($exam)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    private function insertSnapshot(int $examMasterId, int $standardId, array $structure): void
    {
        $columns = Schema::getColumnListing('exam_master_subjects');

        foreach ($structure['subjects'] as $row) {
            $data = [
                'exam_master_id' => $examMasterId,
                'standard_id' => $standardId,
                'subject_id' => (int) $row['subject_id'],
                'subject_name' => $row['subject_name'],
                'max_marks' => $row['max_marks'],
                'passing_marks' => $row['passing_marks'],
                'display_order' => $row['sort_order'],
            ];

            foreach ([
                'theory_max_marks',
                'theory_passing_marks',
                'oral_max_marks',
                'oral_passing_marks',
                'practical_max_marks',
                'practical_passing_marks',
                'structure_source_hash',
                'excel_row_number',
                'is_optional',
            ] as $column) {
                if (!in_array($column, $columns, true)) {
                    continue;
                }

                if ($column === 'structure_source_hash') {
                    $data[$column] = $structure['file_hash'];
                } elseif ($column === 'excel_row_number') {
                    $data[$column] = $row['excel_row'];
                } elseif ($column === 'is_optional') {
                    $data[$column] = (int) ($row['is_optional'] ?? 0);
                } else {
                    $data[$column] = $row[$column];
                }
            }

            if (in_array('created_at', $columns, true)) {
                $data['created_at'] = now();
            }
            if (in_array('updated_at', $columns, true)) {
                $data['updated_at'] = now();
            }

            DB::table('exam_master_subjects')->insert($data);
        }
    }

    private function hasDependentData(int $examId): bool
    {
        foreach (['student_marks', 'student_results', 'marks_entries'] as $table) {
            if (
                Schema::hasTable($table)
                && DB::table($table)->where('exam_master_id', $examId)->exists()
            ) {
                return true;
            }
        }

        return false;
    }

    private function resolveExamType(ExamMaster $exam): ?string
    {
        if (Schema::hasColumn('exam_masters', 'exam_type')) {
            $value = trim((string) ($exam->exam_type ?? ''));
            if ($value !== '') {
                return $this->excel->normalizeExamType($value);
            }
        }

        $name = strtoupper(trim((string) $exam->exam_name));
        foreach ([
            'UNIT TEST 1',
            'UNIT TEST 2',
            'UNIT TEST 3',
            'UNIT TEST 4',
            'TERM 1',
            'TERM 2',
            'ANNUAL',
        ] as $candidate) {
            if (str_starts_with($name, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function sum(array $rows, string $field): float
    {
        return (float) collect($rows)
            ->sum(fn (array $row) => (float) ($row[$field] ?? 0));
    }
}
