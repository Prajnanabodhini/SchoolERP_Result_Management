<?php

namespace App\Services;

use App\Models\ExamMaster;
use App\Models\Standard;
use App\Helpers\ExamStructureHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Synchronizes the exam structure into exam_master_subjects.
 *
 * Two sources of truth:
 *   • Excel  →  syncIfDraft() / syncAllDrafts()           (legacy, guarded)
 *   • DB     →  syncExamMasterFromDatabase()              (current Exam Structure page)
 *               syncStandardFromDatabase()
 *               syncAllFromDatabase()
 *
 * NOTE: the DB-driven methods do NOT check hasDependentData().
 *       exam_master_subjects is a CONFIG table, not a marks table.
 *       Student answers live in student_marks, keyed by subject_id —
 *       so updating max/pass config is safe even after marks exist.
 */
class ExamStructureSyncService
{
    private const SKILL_SUBJECT_TYPE_IDS = [2, 3];

    public function __construct(
        private readonly ExamStructureExcelService $excel
    ) {
    }

    /* ================================================================
     | EXCEL-DRIVEN SYNC — legacy, still guarded
     * ================================================================ */

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
        if (!$examType) return false;

        $structure = $this->excel->getStructure(
            (int) $exam->standard_id,
            $examType
        );

        if (!($structure['success'] ?? false)) return false;

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

            DB::table('exam_masters')->where('id', $exam->id)->update($payload);
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
                    if ($this->syncIfDraft($exam)) $count++;
                }
            });

        return $count;
    }

    /* ================================================================
     | ⭐ DATABASE-DRIVEN SYNC — no hasDependentData guard.
     | Config (max/pass/total) is always safe to update.
     * ================================================================ */

    public function syncExamMasterFromDatabase(ExamMaster $exam): bool
    {
        $standard = Standard::find($exam->standard_id);
        if (!$standard) return false;

        $sheet = ExamStructureHelper::findSheetForStandard($standard->standard_name);
        if (!$sheet) return false;

        $examType = $this->resolveExamType($exam);
        if (!$examType) return false;

        $subjects = DB::table('standard_wise_subjects as sws')
            ->join('subjects as s', 's.id', '=', 'sws.subject_id')
            ->where('sws.standard_id', $exam->standard_id)
            ->where('sws.is_active', 1)
            ->where('s.is_active', 1)
            ->whereNotIn('s.subject_type_id', self::SKILL_SUBJECT_TYPE_IDS)
            ->orderBy('sws.sort_order')
            ->orderBy('s.id')
            ->select([
                's.id as subject_id',
                's.subject_name',
                's.subject_code',
                's.short_name',
                'sws.sort_order',
                'sws.is_optional',
            ])
            ->get();

        if ($subjects->isEmpty()) return false;

        DB::transaction(function () use ($exam, $examType, $sheet, $subjects): void {

            $cols = Schema::getColumnListing('exam_master_subjects');

            $existing = DB::table('exam_master_subjects')
                ->where('exam_master_id', $exam->id)
                ->get()
                ->keyBy('subject_id');

            foreach ($subjects as $ss) {

                $excel = ExamStructureHelper::matchExcelSubject(
                    $ss->subject_name, $examType, $sheet
                );

                if (!$excel) {
                    /* No structure row → keep existing; skip if none */
                    if ($existing->has($ss->subject_id)) continue;
                    continue;
                }

                $tMax  = (float) ($excel['theory_max_marks']        ?? 0);
                $tPass = (float) ($excel['theory_passing_marks']    ?? 0);
                $oMax  = (float) ($excel['oral_max_marks']          ?? 0);
                $oPass = (float) ($excel['oral_passing_marks']      ?? 0);
                $pMax  = (float) ($excel['practical_max_marks']     ?? 0);
                $pPass = (float) ($excel['practical_passing_marks'] ?? 0);

                $maxMarks  = (float) ($excel['total_max_marks']     ?? 0);
                $passMarks = (float) ($excel['total_passing_marks'] ?? 0);

                if ($maxMarks  <= 0) $maxMarks  = $tMax  + $oMax  + $pMax;
                if ($passMarks <= 0) $passMarks = $tPass + $oPass + $pPass;

                $opt = [
                    'exam_type'               => $examType,
                    'theory_max_marks'        => $tMax,
                    'theory_passing_marks'    => $tPass,
                    'oral_max_marks'          => $oMax,
                    'oral_passing_marks'      => $oPass,
                    'practical_max_marks'     => $pMax,
                    'practical_passing_marks' => $pPass,
                    'total_max_marks'         => $maxMarks,
                    'total_passing_marks'     => $passMarks,
                    'is_optional'             => (int) ($ss->is_optional ?? 0),
                ];

                $row = $existing->get($ss->subject_id);

                if ($row) {
                    /* UPDATE in-place — preserve row id and any other columns */
                    $update = [
                        'max_marks'      => $maxMarks,
                        'passing_marks'  => $passMarks,
                        'display_order'  => (int) ($ss->sort_order ?? 0),
                    ];
                    foreach ($opt as $c => $val) {
                        if (in_array($c, $cols, true)) $update[$c] = $val;
                    }
                    if (in_array('updated_at', $cols, true)) $update['updated_at'] = now();

                    DB::table('exam_master_subjects')
                        ->where('id', $row->id)
                        ->update($update);

                } else {
                    /* INSERT new row */
                    $data = [
                        'exam_master_id' => $exam->id,
                        'standard_id'    => $exam->standard_id,
                        'subject_id'     => $ss->subject_id,
                        'subject_name'   => $ss->subject_name,
                        'max_marks'      => $maxMarks,
                        'passing_marks'  => $passMarks,
                        'display_order'  => (int) ($ss->sort_order ?? 0),
                    ];
                    foreach ($opt as $c => $val) {
                        if (in_array($c, $cols, true)) $data[$c] = $val;
                    }
                    if (in_array('created_at', $cols, true)) $data['created_at'] = now();
                    if (in_array('updated_at', $cols, true)) $data['updated_at'] = now();

                    DB::table('exam_master_subjects')->insert($data);
                }
            }

            /* Refresh has_theory / has_oral / has_practical flags */
            $fresh = DB::table('exam_master_subjects')
                ->where('exam_master_id', $exam->id)
                ->get();

            $flags = [
                'has_theory'    => $fresh->contains(fn ($r) => (float) $r->theory_max_marks    > 0) ? 1 : 0,
                'has_oral'      => $fresh->contains(fn ($r) => (float) $r->oral_max_marks      > 0) ? 1 : 0,
                'has_practical' => $fresh->contains(fn ($r) => (float) $r->practical_max_marks > 0) ? 1 : 0,
            ];
            $payload = array_filter(
                $flags,
                fn ($k) => Schema::hasColumn('exam_masters', $k),
                ARRAY_FILTER_USE_KEY
            );
            if (Schema::hasColumn('exam_masters', 'updated_at')) {
                $payload['updated_at'] = now();
            }
            if (!empty($payload)) {
                DB::table('exam_masters')->where('id', $exam->id)->update($payload);
            }
        });

        return true;
    }

    public function syncStandardFromDatabase(int $standardId, ?string $examType = null): int
    {
        $count = 0;

        ExamMaster::query()
            ->where('standard_id', $standardId)
            ->where('is_active', 1)
            ->orderBy('id')
            ->chunkById(50, function ($exams) use (&$count, $examType): void {
                foreach ($exams as $exam) {
                    if ($examType !== null) {
                        $resolved = $this->resolveExamType($exam);
                        if ($resolved !== $examType) continue;
                    }
                    if ($this->syncExamMasterFromDatabase($exam)) $count++;
                }
            });

        return $count;
    }

    public function syncAllFromDatabase(): int
    {
        $count = 0;

        ExamMaster::query()
            ->where('is_active', 1)
            ->orderBy('id')
            ->chunkById(50, function ($exams) use (&$count): void {
                foreach ($exams as $exam) {
                    if ($this->syncExamMasterFromDatabase($exam)) $count++;
                }
            });

        return $count;
    }

    /* ================================================================
     | HELPERS
     * ================================================================ */

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
                'theory_max_marks','theory_passing_marks',
                'oral_max_marks','oral_passing_marks',
                'practical_max_marks','practical_passing_marks',
                'structure_source_hash','excel_row_number','is_optional',
            ] as $column) {
                if (!in_array($column, $columns, true)) continue;

                if ($column === 'structure_source_hash')   $data[$column] = $structure['file_hash'];
                elseif ($column === 'excel_row_number')    $data[$column] = $row['excel_row'];
                elseif ($column === 'is_optional')         $data[$column] = (int) ($row['is_optional'] ?? 0);
                else                                       $data[$column] = $row[$column];
            }

            if (in_array('created_at', $columns, true)) $data['created_at'] = now();
            if (in_array('updated_at', $columns, true)) $data['updated_at'] = now();

            DB::table('exam_master_subjects')->insert($data);
        }
    }

    /** Kept for legacy Excel sync — the DB sync does not use it. */
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
            'UNIT TEST 1','UNIT TEST 2','UNIT TEST 3','UNIT TEST 4',
            'TERM 1','TERM 2','ANNUAL',
        ] as $candidate) {
            if (str_starts_with($name, $candidate)) return $candidate;
        }

        return null;
    }

    private function sum(array $rows, string $field): float
    {
        return (float) collect($rows)
            ->sum(fn (array $row) => (float) ($row[$field] ?? 0));
    }
}