<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Throwable;

class AllocationStatusHelper
{
    /**
     * Fallback: all active divisions.
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
     * ✅ NEW: does this (year, standard, division) have at least one student?
     * Fails OPEN on exception so ERP outages don't block legit work.
     */
    private static function divisionHasStudents($yearId, int $standardId, int $divisionId): bool
    {
        try {
            $students = StudentHelper::getStudentsDirectERP($yearId, $standardId, $divisionId);

            if (is_array($students))       return count($students) > 0;
            if ($students instanceof \Countable)   return count($students) > 0;
            if ($students instanceof \Traversable) {
                foreach ($students as $_) { return true; }
                return false;
            }

            return !empty($students);

        } catch (Throwable $e) {
            // Fail open — assume division has students
            return true;
        }
    }

    /**
     * ERP-valid division IDs for a single exam.
     * ✅ NOW: only returns divisions that actually have students.
     */
    private static function validDivisionIdsForExam(int $examId): array
    {
        static $cache = [];

        if (array_key_exists($examId, $cache)) {
            return $cache[$examId];
        }

        $exam = DB::table('exam_masters')
            ->where('id', $examId)
            ->select('id', 'academic_year_id', 'standard_id')
            ->first();

        if (!$exam) {
            return $cache[$examId] = self::activeDivisionIds();
        }

        $erpDivisions = StudentHelper::getERPDivisionsForStandard(
            (int) $exam->academic_year_id,
            (int) $exam->standard_id
        );

        if ($erpDivisions->isEmpty()) {
            return $cache[$examId] = self::activeDivisionIds();
        }

        /*
         |----------------------------------------------------------
         | ★ Filter out divisions that have no students in ERP.
         | This is the fix: without it, empty divisions like NURSERY B
         | are always counted as "required", so their subjects always
         | show up as pending and never clear.
         |----------------------------------------------------------
         */
        $academicYear = DB::table('academic_years')
            ->where('id', $exam->academic_year_id)
            ->first();

        $yearId = $academicYear->old_year_id
            ?? substr($academicYear->year_name ?? '', 0, 4);

        $withStudents = [];
        foreach ($erpDivisions as $div) {
            $divId = (int) $div->id;
            if (self::divisionHasStudents($yearId, (int) $exam->standard_id, $divId)) {
                $withStudents[] = $divId;
            }
        }

        /*
         |----------------------------------------------------------
         | Safety net: if ALL divisions came back empty (likely an
         | ERP mismatch), fall back to the original list so we don't
         | accidentally mark the exam as fully unconfigured.
         |----------------------------------------------------------
         */
        if (empty($withStudents)) {
            $withStudents = $erpDivisions
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->all();
        }

        return $cache[$examId] = $withStudents;
    }

    /**
     * Batch version: [examId => [divisionId, ...]]
     */
    private static function validDivisionIdsForExams(array $examIds): array
    {
        $out = [];

        foreach ($examIds as $id) {
            $out[$id] = self::validDivisionIdsForExam((int) $id);
        }

        return $out;
    }

    /**
     * Pending count for one exam.
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

        $divisionIds = self::validDivisionIdsForExam($examId);
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
            ->select('id', 'standard_id', 'academic_year_id')
            ->get()
            ->keyBy('id');

        /* Per-exam ERP-valid divisions (already filtered by students) */
        $divisionByExam = self::validDivisionIdsForExams($examIds);

        $subjectCounts = DB::table('exam_master_subjects')
            ->whereIn('exam_master_id', $examIds)
            ->select('exam_master_id', DB::raw('COUNT(DISTINCT subject_id) as cnt'))
            ->groupBy('exam_master_id')
            ->pluck('cnt', 'exam_master_id')
            ->toArray();

        /* All allocated (exam, subject, division) triples */
        $allocatedRows = DB::table('teacher_subject_allocations as tsa')
            ->join('teacher_class_allocations as tca', 'tca.id', '=', 'tsa.teacher_class_allocation_id')
            ->whereIn('tsa.exam_master_id', $examIds)
            ->select('tsa.exam_master_id', 'tsa.subject_id', 'tca.division_id')
            ->distinct()
            ->get()
            ->groupBy('exam_master_id');

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

            $divisionIds = $divisionByExam[$examId] ?? [];

            if (empty($divisionIds)) {
                $result[$examId] = 0;
                continue;
            }

            $allocatedInValidDivs = 0;
            if (isset($allocatedRows[$examId])) {
                foreach ($allocatedRows[$examId] as $row) {
                    if (in_array((int) $row->division_id, $divisionIds, true)) {
                        $allocatedInValidDivs++;
                    }
                }
            }

            $requiredCount = $subjectCount * count($divisionIds);
            $result[$examId] = max(0, $requiredCount - $allocatedInValidDivs);
        }

        return $result;
    }

    /**
     * Pending detail per (subject × division) for one exam.
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

        /* Only ERP-valid divisions for this exam */
        $divisionIds = self::validDivisionIdsForExam($examId);

        $divisions = DB::table('divisions')
            ->whereIn('id', $divisionIds)
            ->where('is_active', 1)
            ->orderBy('division_name')
            ->get();

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

        $divisionIds = self::validDivisionIdsForExam($examId);

        $divisions = DB::table('divisions')
            ->whereIn('id', $divisionIds)
            ->where('is_active', 1)
            ->orderBy('division_name')
            ->get();

        $allocatedMap = DB::table('teacher_subject_allocations as tsa')
            ->join('teacher_class_allocations as tca', 'tca.id', '=', 'tsa.teacher_class_allocation_id')
            ->where('tsa.exam_master_id', $examId)
            ->where('tca.standard_id', $standardId)
            ->whereIn('tsa.subject_id', $subjectIds)
            ->whereIn('tca.division_id', $divisionIds)
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
     * Batch version of pendingBreakdownForExam.
     */
    public static function pendingBreakdownForExams(array $examIds): array
    {
        if (empty($examIds)) return [];

        $exams = DB::table('exam_masters')
            ->whereIn('id', $examIds)
            ->select('id', 'standard_id', 'academic_year_id')
            ->get()
            ->keyBy('id');

        /* Per-exam ERP-valid divisions (already filtered by students) */
        $divisionByExam = self::validDivisionIdsForExams($examIds);

        /* Load division names once */
        $allDivisionIds = [];
        foreach ($divisionByExam as $ids) {
            foreach ($ids as $id) {
                $allDivisionIds[$id] = true;
            }
        }
        $allDivisionIds = array_keys($allDivisionIds);

        $divisionMap = DB::table('divisions')
            ->whereIn('id', $allDivisionIds)
            ->where('is_active', 1)
            ->orderBy('division_name')
            ->get()
            ->keyBy('id');

        $subjectCounts = DB::table('exam_master_subjects')
            ->whereIn('exam_master_id', $examIds)
            ->select('exam_master_id', DB::raw('COUNT(DISTINCT subject_id) as cnt'))
            ->groupBy('exam_master_id')
            ->pluck('cnt', 'exam_master_id')
            ->toArray();

        $allocatedRows = DB::table('teacher_subject_allocations as tsa')
            ->join('teacher_class_allocations as tca', 'tca.id', '=', 'tsa.teacher_class_allocation_id')
            ->whereIn('tsa.exam_master_id', $examIds)
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

            $divisionIds = $divisionByExam[$examId] ?? [];
            if (empty($divisionIds)) {
                $result[$examId] = [];
                continue;
            }

            $perDivision = [];
            if (isset($allocatedRows[$examId])) {
                foreach ($allocatedRows[$examId] as $row) {
                    if (in_array((int) $row->division_id, $divisionIds, true)) {
                        $perDivision[$row->division_id][] = $row->subject_id;
                    }
                }
            }

            $breakdown = [];
            foreach ($divisionIds as $divId) {
                $div = $divisionMap[$divId] ?? null;
                if (!$div) continue;

                $allocated = isset($perDivision[$divId])
                    ? count(array_unique($perDivision[$divId]))
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