<?php

namespace App\Services;

use App\Models\ExamMaster;
use App\Models\ExamStructure;
use App\Models\Standard;
use App\Helpers\ExamStructureHelper;
use Illuminate\Support\Facades\DB;

class ExamStructureSyncService
{
    public function syncAllFromDatabase(): int
    {
        $examMasters = ExamMaster::where('is_active', 1)->get();
        $count = 0;

        foreach ($examMasters as $examMaster) {
            $count += $this->syncExamMaster($examMaster);
        }

        return $count;
    }

    public function syncStandardFromDatabase(int $standardId, ?string $examType = null): int
    {
        $query = ExamMaster::where('is_active', 1)->where('standard_id', $standardId);
        
        if ($examType) {
            $normalizedType = ExamStructureHelper::normalizeExamType($examType);
            $query->where(function($q) use ($normalizedType) {
                $q->where('exam_type', $normalizedType)
                  ->orWhere('exam_name', 'like', "%{$normalizedType}%");
            });
        }
        
        $examMasters = $query->get();
        $count = 0;

        foreach ($examMasters as $examMaster) {
            $count += $this->syncExamMaster($examMaster);
        }

        return $count;
    }

    private function syncExamMaster(ExamMaster $examMaster): int
    {
        $standard = Standard::find($examMaster->standard_id);
        if (!$standard) return 0;

        $standardKey = ExamStructureHelper::getStandardSheetKey($standard->standard_name);
        if (!$standardKey) return 0;

        // Determine exam type from ExamMaster or fallback to parsing Exam Name
        $examType = $examMaster->exam_type ?? ExamStructureHelper::normalizeExamType($examMaster->exam_name);

        $structures = ExamStructure::where('standard_key', $standardKey)
            ->where('exam_type', $examType)
            ->where('is_active', 1)
            ->get();

        if ($structures->isEmpty()) {
            return 0;
        }

        $count = 0;

        foreach ($structures as $row) {
            $subjectId = $row->subject_id;
            
            // Fallback if subject_id is null in exam_structures
            if (!$subjectId) {
                $subject = DB::table('subjects')
                    ->whereRaw('UPPER(TRIM(subject_name)) = ?', [strtoupper(trim($row->subject_name))])
                    ->first();
                if ($subject) {
                    $subjectId = $subject->id;
                    // Update exam_structures for future reference
                    DB::table('exam_structures')->where('id', $row->id)->update(['subject_id' => $subjectId]);
                }
            }

            if (!$subjectId) continue;

            // Upsert into exam_master_subjects
            DB::table('exam_master_subjects')->updateOrInsert(
                [
                    'exam_master_id' => $examMaster->id,
                    'subject_id' => $subjectId,
                ],
                [
                    'standard_id' => $standard->id,
                    'subject_name' => $row->subject_name,
                    'max_marks' => $row->total_max_marks,
                    'passing_marks' => $row->total_passing_marks,
                    'theory_max_marks' => $row->theory_max_marks,
                    'theory_passing_marks' => $row->theory_passing_marks,
                    'oral_max_marks' => $row->oral_max_marks,
                    'oral_passing_marks' => $row->oral_passing_marks,
                    'practical_max_marks' => $row->practical_max_marks,
                    'practical_passing_marks' => $row->practical_passing_marks,
                    'total_max_marks' => $row->total_max_marks,
                    'total_passing_marks' => $row->total_passing_marks,
                    'exam_type' => $examType,
                    'updated_at' => now(),
                ]
            );
            $count++;
        }

        return $count;
    }
}