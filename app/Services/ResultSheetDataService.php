<?php

namespace App\Services;

use App\Helpers\ResultSheetHelper;
use App\Helpers\StudentHelper;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\UserDesignation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResultSheetDataService
{
    /*
    |--------------------------------------------------------------------------
    | BUILD RESULT SHEET DATA
    |--------------------------------------------------------------------------
    */

    public function build(
        int $academicYearId,
        int $examMasterId,
        int $standardId,
        int $divisionId
    ): array {

        $academicYear = AcademicYear::find($academicYearId);
        $exam         = ExamMaster::find($examMasterId);
        $standard     = Standard::find($standardId);
        $division     = Division::find($divisionId);

        if (!$academicYear || !$exam || !$standard || !$division) {
            return [
                'error' => 'Invalid Academic Year, Exam, Standard or Division selected.',
            ];
        }

        $passPercentage = ResultSheetHelper::getPassingPercentage($standardId);

        /*
        |--------------------------------------------------------------------------
        | BUILD DISPLAY SUBJECT COLUMNS
        |--------------------------------------------------------------------------
        */

        $displayColumns = $this->buildDisplayColumns(
            $examMasterId,
            $standardId,
            $passPercentage
        );

        if ($displayColumns->isEmpty()) {
            return [
                'error' =>
                    'No active Academic Standard Wise Subject Mapping found for '
                    . ($standard->standard_name ?? 'selected Standard') . '.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD ACTUAL STUDENT MARKS
        |--------------------------------------------------------------------------
        */

        $markRows = $this->loadStudentMarks(
            $academicYearId,
            $examMasterId,
            $standardId,
            $divisionId
        );

        /*
        |--------------------------------------------------------------------------
        | COMMON MARK GROUPING
        |--------------------------------------------------------------------------
        */

        $marksByStudent = ResultSheetHelper::groupMarksByStudentAndSubject(
            $markRows,
            $displayColumns
        );

        /*
        |--------------------------------------------------------------------------
        | LOAD STUDENTS FROM OLD ERP
        |--------------------------------------------------------------------------
        */

        $erpStudents = $this->loadERPStudents(
            $academicYearId,
            $standardId,
            $divisionId,
            $marksByStudent->keys()->map(fn ($id) => (int) $id)->all()
        );

        /*
        |--------------------------------------------------------------------------
        | COMMON STUDENT BUILDING
        |--------------------------------------------------------------------------
        */

        $students = ResultSheetHelper::buildStudents(
            $erpStudents,
            $marksByStudent,
            $displayColumns,
            $passPercentage
        );

        /*
        |--------------------------------------------------------------------------
        | STAFF DESIGNATIONS
        |--------------------------------------------------------------------------
        */

        $designationData = $this->getDesignations(
            $academicYearId,
            $standardId,
            $divisionId
        );

        /*
        |--------------------------------------------------------------------------
        | TOTAL MAXIMUM MARKS (most common max among students)
        |--------------------------------------------------------------------------
        */

        $totalMaxMarks = 0.0;

        if ($students->isNotEmpty()) {

            $maxValues = $students
                ->pluck('academic_max_display')
                ->filter(fn ($value) => is_numeric($value) && (float) $value > 0)
                ->map(fn ($value) => (float) $value);

            if ($maxValues->isNotEmpty()) {

                $mode = $maxValues
                    ->countBy(fn ($value) => number_format($value, 4, '.', ''))
                    ->sortDesc()
                    ->keys()
                    ->first();

                $totalMaxMarks = $mode !== null
                    ? (float) $mode
                    : (float) $maxValues->max();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | VIEW DATA
        |--------------------------------------------------------------------------
        */

        return [
            'error' => null,

            'viewData' => [

                'exams'         => ExamMaster::orderByDesc('id')->get(),
                'standards'     => Standard::orderBy('display_order')->get(),
                'divisions'     => Division::orderBy('division_name')->get(),
                'academicYears' => AcademicYear::orderByDesc('id')->get(),

                'results'        => $students,
                'displayColumns' => $displayColumns,
                'totalMaxMarks'  => $totalMaxMarks,
                'passPercentage' => $passPercentage,

                'exam'         => $exam,
                'standard'     => $standard,
                'division'     => $division,
                'academicYear' => $academicYear,

                'classTeacher' => $designationData['classTeacher'],
                'principal'    => $designationData['principal'],
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | BUILD DISPLAY COLUMNS
    |--------------------------------------------------------------------------
    | All exam_master_subjects rows are loaded in ONE query before the loop.
    | The per-subject lookup then happens in memory — no per-subject DB hit.
    */

    private function buildDisplayColumns(
        int $examMasterId,
        int $standardId,
        int $passPercentage
    ): Collection {

        /* ---- Load standard-wise subject mappings ---- */
        $subjects = DB::table('standard_wise_subjects as sws')
            ->leftJoin('subjects as s', 's.id', '=', 'sws.subject_id')
            ->where('sws.standard_id', $standardId)
            ->where('sws.is_active', 1)
            ->orderBy('sws.sort_order')
            ->orderBy('sws.id')
            ->select([
                'sws.id as mapping_id',
                'sws.subject_id',
                's.subject_name',
                's.subject_code',
                's.short_name',
                's.subject_type_id',
                'sws.subject_name as standard_subject_name',
                'sws.is_optional',
                'sws.sort_order',
            ])
            ->get();

        /* ---- Preload ALL exam_master_subjects rows for this exam + standard ---- */
        $configs       = collect();
        $legacyConfigs = collect();

        if (Schema::hasTable('exam_master_subjects')) {

            $configs = DB::table('exam_master_subjects')
                ->where('exam_master_id', $examMasterId)
                ->where('standard_id', $standardId)
                ->get()
                ->keyBy('subject_id');

            $mappingIds = $subjects
                ->pluck('mapping_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0);

            if ($mappingIds->isNotEmpty()) {
                $legacyConfigs = DB::table('exam_master_subjects')
                    ->where('exam_master_id', $examMasterId)
                    ->where('standard_id', $standardId)
                    ->whereIn('subject_id', $mappingIds->all())
                    ->get()
                    ->keyBy('subject_id');
            }
        }

        $columns = collect();

        foreach ($subjects as $subject) {

            $subjectId = (int) ($subject->subject_id ?? 0);
            if ($subjectId <= 0) continue;

            /* Academic subjects only */
            if ((int) ($subject->subject_type_id ?? 1) !== 1) continue;

            $subjectName = trim((string) (
                $subject->subject_name
                ?? $subject->standard_subject_name
                ?? ''
            ));
            if ($subjectName === '') continue;

            /* ---- In-memory config lookup ---- */
            $config = $configs->get($subjectId);

            if (!$config) {
                $mappingId = (int) ($subject->mapping_id ?? 0);
                if ($mappingId > 0) {
                    $config = $legacyConfigs->get($mappingId);
                }
            }

            $maxMarks     = $config ? (float) ($config->max_marks     ?? 0) : 0.0;
            $passingMarks = $config ? (float) ($config->passing_marks ?? 0) : 0.0;

            if ($passingMarks <= 0 && $maxMarks > 0) {
                $passingMarks = ResultSheetHelper::calculatePassingMarks(
                    $maxMarks,
                    $passPercentage
                );
            }

            /* ---- Subject code normalization ---- */
            $code       = trim((string) ($subject->subject_code ?? ''));
            $normalized = ResultSheetHelper::normalizeText($subjectName);

            if ($normalized === 'HISTORY') {
                $code = 'HIST';
            } elseif ($normalized === 'GEOGRAPHY') {
                $code = 'GEO';
            }

            if ($code === '') {
                $cleanSubjectName = preg_replace('/[^A-Za-z0-9]/', '', $subjectName);
                $code = strtoupper(substr($cleanSubjectName ?? '', 0, 4));
            }

            $columns->push((object) [
                'key'           => 'SUBJECT_' . $subjectId,
                'mapping_id'    => (int) ($subject->mapping_id ?? 0),
                'subject_id'    => $subjectId,
                'subject_name'  => $subjectName,
                'subject_code'  => $code,
                'short_name'    => $subject->short_name ?: $subjectName,
                'max_marks'     => $maxMarks,
                'passing_marks' => $passingMarks,
                'is_optional'   => (int) ($subject->is_optional ?? 0),
                'sort_order'    => (int) ($subject->sort_order ?? 9999),
            ]);
        }

        return $columns
            ->sortBy(fn ($subject) => $this->subjectSortWeight($subject))
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | SUBJECT SORT WEIGHT
    |--------------------------------------------------------------------------
    */

    private function subjectSortWeight($subject): int
    {
        $name = strtoupper(trim((string) ($subject->subject_name ?? '')));
        $code = strtoupper(trim((string) ($subject->subject_code ?? '')));

        if ($name === 'ENGLISH' || $code === 'ENG') return 10;
        if ($name === 'HINDI'   || $code === 'HIN') return 20;
        if ($name === 'SANSKRIT'|| $code === 'SAN') return 20;
        if ($name === 'MARATHI' || $code === 'MAR') return 30;

        return 1000 + (int) ($subject->sort_order ?? 9999);
    }

    /*
    |--------------------------------------------------------------------------
    | LOAD STUDENT MARKS
    |--------------------------------------------------------------------------
    */

    private function loadStudentMarks(
        int $academicYearId,
        int $examMasterId,
        int $standardId,
        int $divisionId
    ): Collection {

        if (!Schema::hasTable('student_marks')) {
            return collect();
        }

        $columns = Schema::getColumnListing('student_marks');

        $query = DB::table('student_marks as sm');

        foreach ([
            'academic_year_id' => $academicYearId,
            'exam_master_id'   => $examMasterId,
            'standard_id'      => $standardId,
            'division_id'      => $divisionId,
        ] as $field => $value) {
            if (in_array($field, $columns, true)) {
                $query->where('sm.' . $field, $value);
            }
        }

        $hasAllocation = Schema::hasTable('teacher_subject_allocations')
            && in_array('teacher_subject_allocation_id', $columns, true);

        if ($hasAllocation) {
            $query->leftJoin(
                'teacher_subject_allocations as tsa',
                'tsa.id',
                '=',
                'sm.teacher_subject_allocation_id'
            );
        }

        $query->select('sm.*');

        if ($hasAllocation) {
            $query->addSelect('tsa.subject_id as allocation_subject_id');
        }

        if (in_array('id', $columns, true)) {
            $query->orderByDesc('sm.id');
        }

        return $query->get();
    }

    /*
    |--------------------------------------------------------------------------
    | LOAD ERP STUDENTS
    |--------------------------------------------------------------------------
    | Uses getStudentsDirectERPSlim() which selects only 6 columns and
    | has no TRY_CAST ORDER BY. This is the single biggest speed win.
    */

    private function loadERPStudents(
        int $academicYearId,
        int $standardId,
        int $divisionId,
        array $fallbackStudentIds = []
    ): Collection {

        try {
            $students = StudentHelper::getStudentsDirectERPSlim(
                $academicYearId,
                $standardId,
                $divisionId
            );

            $students = collect($students);

            if ($students->isNotEmpty()) {
                return $students;
            }

        } catch (\Throwable) {
            /* fall through to id-based fallback */
        }

        return collect(
            $this->loadERPStudentsByIds($fallbackStudentIds)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LOAD ERP STUDENTS BY IDS (fallback)
    |--------------------------------------------------------------------------
    */

    private function loadERPStudentsByIds(array $studentIds): array
    {
        $students = [];

        if (empty($studentIds)) {
            return $students;
        }

        try {
            $rows = DB::connection('sqlsrv_olderp')
                ->table('FeeMstStudent as f')
                ->leftJoin('SubStudentMst as ss', 'ss.Studentid', '=', 'f.Studentid')
                ->whereIn('f.Studentid', array_map('intval', $studentIds))
                ->select([
                    'f.Studentid',
                    'f.studname',
                    'f.fathername',
                    'f.gender',
                    'ss.rollno',
                ])
                ->get();

            foreach ($rows as $row) {
                $id = (int) ($row->Studentid ?? 0);
                if ($id > 0 && !isset($students[$id])) {
                    $students[$id] = $row;
                }
            }

        } catch (\Throwable) {
            /* keep the result page usable */
        }

        return $students;
    }

    /*
    |--------------------------------------------------------------------------
    | DESIGNATIONS
    |--------------------------------------------------------------------------
    */

    private function getDesignations(
        int $academicYearId,
        int $standardId,
        int $divisionId
    ): array {

        $classTeacher = null;
        $principal    = null;

        /* ---- CLASS TEACHER — exact match ---- */
        try {
            $classTeacher = UserDesignation::query()
                ->with(['user', 'designation'])
                ->where('academic_year_id', $academicYearId)
                ->where('standard_id', $standardId)
                ->where('division_id', $divisionId)
                ->whereHas('designation', function ($query) {
                    $query->where(function ($q) {
                        $q->whereRaw('UPPER(TRIM(designation_name)) LIKE ?', ['%CLASS TEACHER%'])
                          ->orWhereRaw('UPPER(TRIM(designation_code)) LIKE ?', ['%CLASS_TEACHER%'])
                          ->orWhereRaw('UPPER(TRIM(designation_code)) LIKE ?', ['%CLASS-TEACHER%']);
                    });
                })
                ->orderByDesc('id')
                ->first();
        } catch (\Throwable) {
            $classTeacher = null;
        }

        /* ---- CLASS TEACHER — fallback without year ---- */
        if (!$classTeacher) {
            try {
                $classTeacher = UserDesignation::query()
                    ->with(['user', 'designation'])
                    ->where('standard_id', $standardId)
                    ->where('division_id', $divisionId)
                    ->whereHas('designation', function ($query) {
                        $query->where(function ($q) {
                            $q->whereRaw('UPPER(TRIM(designation_name)) LIKE ?', ['%CLASS TEACHER%'])
                              ->orWhereRaw('UPPER(TRIM(designation_code)) LIKE ?', ['%CLASS_TEACHER%'])
                              ->orWhereRaw('UPPER(TRIM(designation_code)) LIKE ?', ['%CLASS-TEACHER%']);
                        });
                    })
                    ->orderByDesc('id')
                    ->first();
            } catch (\Throwable) {
                $classTeacher = null;
            }
        }

        /* ---- PRINCIPAL — PRIMARY (PRINCIPAL-PRI) ---- */
        try {
            $principal = UserDesignation::query()
                ->with(['user', 'designation'])
                ->whereHas('designation', function ($query) {
                    $query->where(function ($q) {
                        $q->whereRaw('UPPER(TRIM(designation_name)) = ?', ['PRINCIPAL-PRI'])
                          ->orWhereRaw('UPPER(TRIM(designation_code)) = ?', ['PRINCIPAL-PRI']);
                    });
                })
                ->orderByDesc('id')
                ->first();
        } catch (\Throwable) {
            $principal = null;
        }

        /* ---- PRINCIPAL — SECONDARY (PRINCIPAL-SEC) ---- */
        if (!$principal) {
            try {
                $principal = UserDesignation::query()
                    ->with(['user', 'designation'])
                    ->whereHas('designation', function ($query) {
                        $query->where(function ($q) {
                            $q->whereRaw('UPPER(TRIM(designation_name)) = ?', ['PRINCIPAL-SEC'])
                              ->orWhereRaw('UPPER(TRIM(designation_code)) = ?', ['PRINCIPAL-SEC']);
                        });
                    })
                    ->orderByDesc('id')
                    ->first();
            } catch (\Throwable) {
                $principal = null;
            }
        }

        /* ---- PRINCIPAL — generic fallback ---- */
        if (!$principal) {
            try {
                $principal = UserDesignation::query()
                    ->with(['user', 'designation'])
                    ->whereHas('designation', function ($query) {
                        $query->where(function ($q) {
                            $q->whereRaw('UPPER(TRIM(designation_name)) LIKE ?', ['%PRINCIPAL%'])
                              ->orWhereRaw('UPPER(TRIM(designation_code)) LIKE ?', ['%PRINCIPAL%']);
                        });
                    })
                    ->orderByDesc('id')
                    ->first();
            } catch (\Throwable) {
                $principal = null;
            }
        }

        return [
            'classTeacher' => $classTeacher,
            'principal'    => $principal,
        ];
    }
}