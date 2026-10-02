<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class AllocationStatusHelper
{
    /**
     * Get active division IDs (used as the "expected divisions" set).
     */
    private static function activeDivisionIds(): array
    {
        static $cached = null;
        if ($cached === null) {
            $cached = DB::table('divisions')
                ->where('is_active', 1)
                ->pluck('id')
                ->toArray();
        }
        return $cached;
    }

    /**
     * Pending count for one exam.
     *
     * Logic:
     *   Required pairs  = subjects × divisions
     *   Allocated pairs = unique (subject, division) rows from tsa → tca
     *   Pending         = required − allocated
     *
     * Return:
     *   > 0  → pending subjects
     *   0    → all subjects have teachers in every division (READY)
     *   -1   → exam has no subjects configured (NOT CONFIGURED)
     */
    public static function pendingCountForExam(int $examId): int
    {
        $exam = DB::table('exam_masters')->where('id', $examId)->first();
        if (!$exam) return 0;

        $standardId = (int) ($exam->standard_id ?? 0);
        if (!$standardId) return -1;

        $subjectIds = DB::table('exam_master_subjects')
            ->where('exam_master_id', $examId)
            ->pluck('subject_id')
            ->unique()
            ->toArray();

        if (empty($subjectIds)) return -1;

        $divisionIds = self::activeDivisionIds();
        if (empty($divisionIds)) return 0;

        $requiredCount = count($subjectIds) * count($divisionIds);

        $allocatedPairs = DB::table('teacher_subject_allocations as tsa')
            ->join('teacher_class_allocations as tca', 'tca.id', '=', 'tsa.teacher_class_allocation_id')
            ->where('tsa.exam_master_id', $examId)
            ->where('tca.standard_id', $standardId)
            ->whereIn('tsa.subject_id', $subjectIds)
            ->whereIn('tca.division_id', $divisionIds)
            ->select('tsa.subject_id', 'tca.division_id')
            ->distinct()
            ->get();

        $allocatedCount = $allocatedPairs->count();

        return max(0, $requiredCount - $allocatedCount);
    }

    /**
     * Batch version for multiple exams.
     * Returns: [exam_id => count or -1]
     */
    public static function pendingCountForExams(array $examIds): array
    {
        if (empty($examIds)) return [];

        $exams = DB::table('exam_masters')
            ->whereIn('id', $examIds)
            ->select('id', 'standard_id')
            ->get()
            ->keyBy('id');

        $divisionIds   = self::activeDivisionIds();
        $divisionCount = count($divisionIds);

        $subjectCounts = DB::table('exam_master_subjects')
            ->whereIn('exam_master_id', $examIds)
            ->select('exam_master_id', DB::raw('COUNT(DISTINCT subject_id) as cnt'))
            ->groupBy('exam_master_id')
            ->pluck('cnt', 'exam_master_id')
            ->toArray();

        // Unique (exam, subject, division) allocated triple
        $allocatedRows = DB::table('teacher_subject_allocations as tsa')
            ->join('teacher_class_allocations as tca', 'tca.id', '=', 'tsa.teacher_class_allocation_id')
            ->whereIn('tsa.exam_master_id', $examIds)
            ->whereIn('tca.division_id', $divisionIds)
            ->select('tsa.exam_master_id', 'tsa.subject_id', 'tca.division_id')
            ->distinct()
            ->get()
            ->groupBy('exam_master_id')
            ->map(fn ($rows) => $rows->count())
            ->toArray();

        $result = [];
        foreach ($examIds as $examId) {
            $exam = $exams[$examId] ?? null;

            if (!$exam) {
                $result[$examId] = 0;
                continue;
            }

            $subjectCount = (int) ($subjectCounts[$examId] ?? 0);

            if ($subjectCount === 0) {
                $result[$examId] = -1;
                continue;
            }

            if ($divisionCount === 0) {
                $result[$examId] = 0;
                continue;
            }

            $requiredCount  = $subjectCount * $divisionCount;
            $allocatedCount = (int) ($allocatedRows[$examId] ?? 0);

            $result[$examId] = max(0, $requiredCount - $allocatedCount);
        }

        return $result;
    }

    /**
     * Pending detail per (subject × division) for one exam.
     * Returns array of objects: [subject_id, subject_name, subject_code,
     *                            division_id, division_name, allocated]
     */
    public static function subjectStatusForExam(int $examId): array
    {
        $exam = DB::table('exam_masters')->where('id', $examId)->first();
        if (!$exam) return [];

        $standardId = (int) ($exam->standard_id ?? 0);

        $subjects = DB::table('exam_master_subjects as ems')
            ->join('subjects as s', 's.id', '=', 'ems.subject_id')
            ->where('ems.exam_master_id', $examId)
            ->select('s.id as subject_id', 's.subject_name', 's.subject_code')
            ->orderBy('ems.display_order')
            ->orderBy('s.id')
            ->get();

        $divisions = DB::table('divisions')
            ->where('is_active', 1)
            ->orderBy('division_name')
            ->get();

        // Allocated: map subject_id → [division_id, ...]
        $allocatedMap = DB::table('teacher_subject_allocations as tsa')
            ->join('teacher_class_allocations as tca', 'tca.id', '=', 'tsa.teacher_class_allocation_id')
            ->where('tsa.exam_master_id', $examId)
            ->where('tca.standard_id', $standardId)
            ->select('tsa.subject_id', 'tca.division_id')
            ->get()
            ->groupBy('subject_id')
            ->map(fn ($rows) => $rows->pluck('division_id')->unique()->toArray())
            ->toArray();

        $result = [];
        foreach ($subjects as $s) {
            $allocatedDivisions = $allocatedMap[$s->subject_id] ?? [];

            foreach ($divisions as $div) {
                $result[] = (object) [
                    'subject_id'    => $s->subject_id,
                    'subject_name'  => $s->subject_name,
                    'subject_code'  => $s->subject_code,
                    'division_id'   => $div->id,
                    'division_name' => $div->division_name,
                    'allocated'     => in_array($div->id, $allocatedDivisions, true),
                ];
            }
        }

        return $result;
    }

    /**
     * Summary of all exams with pending allocations.
     */
    public static function summaryForAcademicYear(?int $yearId = null): array
    {
        $exams = DB::table('exam_masters')
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->select('id', 'exam_name', 'academic_year_id')
            ->orderByDesc('id')
            ->get();

        if ($exams->isEmpty()) return [];

        $examIds       = $exams->pluck('id')->toArray();
        $pendingCounts = self::pendingCountForExams($examIds);

        $result = [];
        foreach ($exams as $exam) {
            $count = $pendingCounts[$exam->id] ?? 0;

            if ($count > 0) {
                $result[] = (object) [
                    'exam_id'          => (int) $exam->id,
                    'exam_name'        => $exam->exam_name,
                    'academic_year_id' => (int) ($exam->academic_year_id ?? 0),
                    'pending'          => (int) $count,
                ];
            }
        }

        return $result;
    }

    public static function totalPendingCount(?int $yearId = null): int
    {
        $summary = self::summaryForAcademicYear($yearId);
        return array_sum(array_column($summary, 'pending'));
    }

        /**
     * Division-wise pending breakdown for one exam.
     * Returns: ['A' => 3, 'B' => 2] (only divisions with pending > 0)
     */
    public static function pendingBreakdownForExam(int $examId): array
    {
        $exam = DB::table('exam_masters')->where('id', $examId)->first();
        if (!$exam) return [];

        $standardId = (int) ($exam->standard_id ?? 0);
        if (!$standardId) return [];

        $subjectIds = DB::table('exam_master_subjects')
            ->where('exam_master_id', $examId)
            ->pluck('subject_id')
            ->unique()
            ->toArray();

        if (empty($subjectIds)) return [];

        $divisions = DB::table('divisions')
            ->where('is_active', 1)
            ->orderBy('division_name')
            ->get();

        $allocatedMap = DB::table('teacher_subject_allocations as tsa')
            ->join('teacher_class_allocations as tca', 'tca.id', '=', 'tsa.teacher_class_allocation_id')
            ->where('tsa.exam_master_id', $examId)
            ->where('tca.standard_id', $standardId)
            ->whereIn('tsa.subject_id', $subjectIds)
            ->select('tsa.subject_id', 'tca.division_id')
            ->get()
            ->groupBy('division_id')
            ->map(fn ($rows) => $rows->pluck('subject_id')->unique()->count())
            ->toArray();

        $subjectCount = count($subjectIds);
        $breakdown    = [];

        foreach ($divisions as $div) {
            $allocated = (int) ($allocatedMap[$div->id] ?? 0);
            $pending   = $subjectCount - $allocated;

            if ($pending > 0) {
                $breakdown[$div->division_name] = $pending;
            }
        }

        return $breakdown;
    }

    /**
     * Batch version of pendingBreakdownForExam for multiple exams.
     * Returns: [exam_id => ['A' => 3, 'B' => 2], ...]
     */
    public static function pendingBreakdownForExams(array $examIds): array
    {
        if (empty($examIds)) return [];

        $exams = DB::table('exam_masters')
            ->whereIn('id', $examIds)
            ->select('id', 'standard_id')
            ->get()
            ->keyBy('id');

        $divisions = DB::table('divisions')
            ->where('is_active', 1)
            ->orderBy('division_name')
            ->get();

        $divisionIds = $divisions->pluck('id')->toArray();

        $subjectCounts = DB::table('exam_master_subjects')
            ->whereIn('exam_master_id', $examIds)
            ->select('exam_master_id', DB::raw('COUNT(DISTINCT subject_id) as cnt'))
            ->groupBy('exam_master_id')
            ->pluck('cnt', 'exam_master_id')
            ->toArray();

        $allocatedRows = DB::table('teacher_subject_allocations as tsa')
            ->join('teacher_class_allocations as tca', 'tca.id', '=', 'tsa.teacher_class_allocation_id')
            ->whereIn('tsa.exam_master_id', $examIds)
            ->whereIn('tca.division_id', $divisionIds)
            ->select('tsa.exam_master_id', 'tsa.subject_id', 'tca.division_id')
            ->get()
            ->groupBy('exam_master_id');

        $result = [];

        foreach ($examIds as $examId) {
            $exam = $exams[$examId] ?? null;
            if (!$exam) {
                $result[$examId] = [];
                continue;
            }

            $subjectCount = (int) ($subjectCounts[$examId] ?? 0);
            if ($subjectCount === 0) {
                $result[$examId] = [];
                continue;
            }

            // Count distinct subjects allocated per division for this exam
            $perDivision = [];
            if (isset($allocatedRows[$examId])) {
                foreach ($allocatedRows[$examId] as $row) {
                    $perDivision[$row->division_id][] = $row->subject_id;
                }
            }

            $breakdown = [];
            foreach ($divisions as $div) {
                $allocated = isset($perDivision[$div->id])
                    ? count(array_unique($perDivision[$div->id]))
                    : 0;
                $pending = $subjectCount - $allocated;

                if ($pending > 0) {
                    $breakdown[$div->division_name] = $pending;
                }
            }

            $result[$examId] = $breakdown;
        }

        return $result;
    }
}