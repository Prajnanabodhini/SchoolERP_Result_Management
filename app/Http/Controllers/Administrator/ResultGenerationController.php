<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\ExamMaster;
use App\Models\Division;
use App\Models\AcademicYear;

class ResultGenerationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | RESULT GENERATION INDEX
    |--------------------------------------------------------------------------
    |
    | Standard is automatically determined from the selected Exam.
    |
    */

    public function index()
    {
        $exams = ExamMaster::orderBy('display_order')
            ->orderBy('exam_name')
            ->get();

        $divisions = Division::orderBy('display_order')
            ->get();

        $academicYears = AcademicYear::orderByDesc('id')
            ->get();

        return view(
            'administrator.result-generation.index',
            compact(
                'exams',
                'divisions',
                'academicYears'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE CANONICAL SUBJECT ID
    |--------------------------------------------------------------------------
    |
    | teacher_subject_allocations.subject_id may contain:
    |
    | 1. subjects.id
    | 2. standard_wise_subjects.id from old allocation data
    |
    | This method converts it to subjects.id.
    |
    */

    private function resolveCanonicalSubjectId(
        $storedSubjectId,
        $standardId
    ) {
        if (
            !$storedSubjectId ||
            !$standardId
        ) {
            return null;
        }

        $storedSubjectId = (int) $storedSubjectId;
        $standardId = (int) $standardId;


        /*
        |--------------------------------------------------------------------------
        | CASE 1
        |--------------------------------------------------------------------------
        | Stored value is already subjects.id
        |
        */

        $directSubjectExists =
            DB::table('subjects')
                ->where(
                    'id',
                    $storedSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->exists();


        if ($directSubjectExists) {

            $validMapping =
                DB::table('standard_wise_subjects')
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'subject_id',
                        $storedSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->exists();


            if ($validMapping) {
                return $storedSubjectId;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CASE 2
        |--------------------------------------------------------------------------
        | Stored value is standard_wise_subjects.id
        |
        */

        $mapping =
            DB::table('standard_wise_subjects')
                ->where(
                    'id',
                    $storedSubjectId
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();


        if (
            $mapping &&
            !empty($mapping->subject_id)
        ) {

            return (int) $mapping->subject_id;
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE RESULTS
    |--------------------------------------------------------------------------
    */

    public function generate(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | REQUEST VALUES
        |--------------------------------------------------------------------------
        */

        $academicYearId = (int) $request->academic_year_id;
        $examMasterId   = (int) $request->exam_master_id;
        $divisionId     = (int) $request->division_id;


        /*
        |--------------------------------------------------------------------------
        | BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        if (!$academicYearId) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Please select Academic Year.'
                );
        }


        if (!$examMasterId || !$divisionId) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Please select Exam and Division.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | GET EXAM
        |--------------------------------------------------------------------------
        */

        $exam =
            ExamMaster::where(
                'id',
                $examMasterId
            )
            ->where(
                'is_active',
                1
            )
            ->first();


        if (!$exam) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam was not found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD FROM EXAM
        |--------------------------------------------------------------------------
        */

        $standardId =
            (int) (
                $exam->standard_id
                ?? 0
            );


        if ($standardId <= 0) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam does not have a Standard assigned.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | GENERATE INSIDE TRANSACTION
        |--------------------------------------------------------------------------
        */

        try {

            DB::transaction(
                function () use (
                    $academicYearId,
                    $examMasterId,
                    $standardId,
                    $divisionId
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | STEP 1
                    |--------------------------------------------------------------------------
                    | GET FINALLY SUBMITTED / LOCKED MARKS
                    |
                    | IMPORTANT:
                    |
                    | We DO NOT throw an error if no locked marks exist.
                    |
                    */

                    $lockedMarks =
                        DB::table('student_marks')
                            ->where(
                                'academic_year_id',
                                $academicYearId
                            )
                            ->where(
                                'exam_master_id',
                                $examMasterId
                            )
                            ->where(
                                'standard_id',
                                $standardId
                            )
                            ->where(
                                'division_id',
                                $divisionId
                            )
                            ->where(
                                'is_locked',
                                1
                            )
                            ->orderBy('id')
                            ->get();


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 2
                    |--------------------------------------------------------------------------
                    | IF NO LOCKED MARKS, USE EXISTING MARKS
                    |
                    | This allows result generation before final submission.
                    |
                    */

                    if ($lockedMarks->isEmpty()) {

                        $lockedMarks =
                            DB::table('student_marks')
                                ->where(
                                    'academic_year_id',
                                    $academicYearId
                                )
                                ->where(
                                    'exam_master_id',
                                    $examMasterId
                                )
                                ->where(
                                    'standard_id',
                                    $standardId
                                )
                                ->where(
                                    'division_id',
                                    $divisionId
                                )
                                ->orderBy('id')
                                ->get();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 3
                    |--------------------------------------------------------------------------
                    | LOAD EXAM SUBJECT CONFIGURATION
                    |--------------------------------------------------------------------------
                    |
                    | Exam Master Subjects are the authoritative subject list
                    | for result generation.
                    |
                    */

                    $examSubjectConfigs =
                        DB::table('exam_master_subjects')
                            ->where(
                                'exam_master_id',
                                $examMasterId
                            )
                            ->where(
                                'standard_id',
                                $standardId
                            )
                            ->orderBy('display_order')
                            ->orderBy('id')
                            ->get();


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 4
                    |--------------------------------------------------------------------------
                    | NORMALIZE EXAM SUBJECT IDs
                    |--------------------------------------------------------------------------
                    */

                    $normalizedExamSubjects =
                        collect();


                    foreach (
                        $examSubjectConfigs as $config
                    ) {

                        $canonicalSubjectId =
                            $this->resolveCanonicalSubjectId(
                                $config->subject_id ?? null,
                                $standardId
                            );


                        /*
                        |--------------------------------------------------------------
                        | If exam_master_subjects.subject_id is already a valid
                        | subjects.id, retain it.
                        |--------------------------------------------------------------
                        */

                        if (!$canonicalSubjectId) {

                            $directExists =
                                !empty($config->subject_id)
                                &&
                                DB::table('subjects')
                                    ->where(
                                        'id',
                                        (int) $config->subject_id
                                    )
                                    ->where(
                                        'is_active',
                                        1
                                    )
                                    ->exists();


                            if ($directExists) {
                                $canonicalSubjectId =
                                    (int) $config->subject_id;
                            }
                        }


                        if ($canonicalSubjectId) {

                            $config->canonical_subject_id =
                                $canonicalSubjectId;

                            $normalizedExamSubjects->push(
                                $config
                            );
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 5
                    |--------------------------------------------------------------------------
                    | GET TSA IDS FROM EXISTING MARKS
                    |--------------------------------------------------------------------------
                    */

                    $tsaIds =
                        $lockedMarks
                            ->pluck(
                                'teacher_subject_allocation_id'
                            )
                            ->filter()
                            ->map(
                                fn ($id) => (int) $id
                            )
                            ->unique()
                            ->values();


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 6
                    |--------------------------------------------------------------------------
                    | LOAD TSA
                    |--------------------------------------------------------------------------
                    */

                    $tsaMap =
                        collect();


                    if ($tsaIds->isNotEmpty()) {

                        $tsaMap =
                            DB::table(
                                'teacher_subject_allocations'
                            )
                            ->whereIn(
                                'id',
                                $tsaIds->toArray()
                            )
                            ->get()
                            ->keyBy('id');
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 7
                    |--------------------------------------------------------------------------
                    | NORMALIZE SUBJECT IDs ON MARKS
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $lockedMarks as $mark
                    ) {

                        $canonicalSubjectId = null;


                        /*
                        |--------------------------------------------------------------
                        | TSA is authoritative when available.
                        |--------------------------------------------------------------
                        */

                        if (
                            !empty(
                                $mark->teacher_subject_allocation_id
                            )
                        ) {

                            $tsa =
                                $tsaMap->get(
                                    (int)
                                    $mark->teacher_subject_allocation_id
                                );


                            if ($tsa) {

                                $canonicalSubjectId =
                                    $this->resolveCanonicalSubjectId(
                                        $tsa->subject_id ?? null,
                                        $standardId
                                    );
                            }
                        }


                        /*
                        |--------------------------------------------------------------
                        | Fallback to student_marks.subject_id
                        |--------------------------------------------------------------
                        */

                        if (!$canonicalSubjectId) {

                            $canonicalSubjectId =
                                $this->resolveCanonicalSubjectId(
                                    $mark->subject_id ?? null,
                                    $standardId
                                );


                            /*
                            |----------------------------------------------------------
                            | Direct subjects.id fallback
                            |----------------------------------------------------------
                            */

                            if (!$canonicalSubjectId) {

                                $directExists =
                                    !empty($mark->subject_id)
                                    &&
                                    DB::table('subjects')
                                        ->where(
                                            'id',
                                            (int) $mark->subject_id
                                        )
                                        ->where(
                                            'is_active',
                                            1
                                        )
                                        ->exists();


                                if ($directExists) {

                                    $canonicalSubjectId =
                                        (int)
                                        $mark->subject_id;
                                }
                            }
                        }


                        if ($canonicalSubjectId) {

                            $mark->canonical_subject_id =
                                (int)
                                $canonicalSubjectId;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 8
                    |--------------------------------------------------------------------------
                    | REMOVE MARKS WITH UNRESOLVED SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    $lockedMarks =
                        $lockedMarks
                            ->filter(
                                fn ($mark) =>
                                    !empty(
                                        $mark->canonical_subject_id
                                    )
                            )
                            ->values();


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 9
                    |--------------------------------------------------------------------------
                    | UNIQUE MARKS
                    |--------------------------------------------------------------------------
                    |
                    | Keep latest record for each student + subject.
                    |
                    */

                    $uniqueMarks =
                        collect();


                    foreach (
                        $lockedMarks as $mark
                    ) {

                        $key =
                            (int)
                            $mark->student_id
                            .
                            '_'
                            .
                            (int)
                            $mark->canonical_subject_id;


                        $uniqueMarks->put(
                            $key,
                            $mark
                        );
                    }


                    $uniqueMarks =
                        $uniqueMarks->values();


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 10
                    |--------------------------------------------------------------------------
                    | GET STUDENT IDS
                    |--------------------------------------------------------------------------
                    */

                    $studentIds =
                        $uniqueMarks
                            ->pluck('student_id')
                            ->filter()
                            ->map(
                                fn ($id) => (int) $id
                            )
                            ->unique()
                            ->values();


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 11
                    |--------------------------------------------------------------------------
                    | IF NO MARKS EXIST, GET STUDENTS DIRECTLY
                    |--------------------------------------------------------------------------
                    |
                    | This is the important part.
                    |
                    | Result generation will not stop just because there are
                    | no marks.
                    |
                    */

                    if ($studentIds->isEmpty()) {

                        $studentQuery =
                            DB::table('students');


                        /*
                        |--------------------------------------------------------------
                        | Academic year filter
                        |--------------------------------------------------------------
                        */

                        if (
                            Schema::hasColumn(
                                'students',
                                'academic_year_id'
                            )
                        ) {

                            $studentQuery->where(
                                'academic_year_id',
                                $academicYearId
                            );
                        }


                        /*
                        |--------------------------------------------------------------
                        | Standard filter
                        |--------------------------------------------------------------
                        */

                        if (
                            Schema::hasColumn(
                                'students',
                                'standard_id'
                            )
                        ) {

                            $studentQuery->where(
                                'standard_id',
                                $standardId
                            );
                        }


                        /*
                        |--------------------------------------------------------------
                        | Division filter
                        |--------------------------------------------------------------
                        */

                        if (
                            Schema::hasColumn(
                                'students',
                                'division_id'
                            )
                        ) {

                            $studentQuery->where(
                                'division_id',
                                $divisionId
                            );
                        }


                        /*
                        |--------------------------------------------------------------
                        | Get IDs
                        |--------------------------------------------------------------
                        */

                        $studentIds =
                            $studentQuery
                                ->pluck('id')
                                ->map(
                                    fn ($id) => (int) $id
                                )
                                ->unique()
                                ->values();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 12
                    |--------------------------------------------------------------------------
                    | DELETE OLD GENERATED RESULTS
                    |--------------------------------------------------------------------------
                    */

                    $existingResultIds =
                        DB::table('student_results')
                            ->where(
                                'academic_year_id',
                                $academicYearId
                            )
                            ->where(
                                'exam_master_id',
                                $examMasterId
                            )
                            ->where(
                                'standard_id',
                                $standardId
                            )
                            ->where(
                                'division_id',
                                $divisionId
                            )
                            ->pluck('id');


                    if (
                        $existingResultIds->isNotEmpty()
                    ) {

                        DB::table(
                            'student_result_details'
                        )
                        ->whereIn(
                            'student_result_id',
                            $existingResultIds->toArray()
                        )
                        ->delete();


                        DB::table(
                            'student_results'
                        )
                        ->whereIn(
                            'id',
                            $existingResultIds->toArray()
                        )
                        ->delete();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 13
                    |--------------------------------------------------------------------------
                    | GENERATE STUDENT RESULTS
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $studentIds as $studentId
                    ) {

                        /*
                        |--------------------------------------------------------------
                        | Student's marks
                        |--------------------------------------------------------------
                        */

                        $marks =
                            $uniqueMarks
                                ->filter(
                                    fn ($mark) =>
                                        (int)
                                        $mark->student_id
                                        ===
                                        (int)
                                        $studentId
                                )
                                ->values();


                        /*
                        |--------------------------------------------------------------------------
                        | TOTALS
                        |--------------------------------------------------------------------------
                        */

                        $totalMax =
                            0;

                        $totalObtained =
                            0;

                        $failedSubjects =
                            0;


                        /*
                        |--------------------------------------------------------------------------
                        | RESULT SUBJECT LIST
                        |--------------------------------------------------------------------------
                        |
                        | Start with Exam Master subjects.
                        |
                        */

                        $resultSubjects =
                            $normalizedExamSubjects
                                ->keyBy(
                                    'canonical_subject_id'
                                );


                        /*
                        |--------------------------------------------------------------
                        | Add any subject existing in marks but not in exam config.
                        |--------------------------------------------------------------
                        */

                        foreach (
                            $marks as $mark
                        ) {

                            $subjectId =
                                (int)
                                $mark->canonical_subject_id;


                            if (
                                !$resultSubjects->has(
                                    $subjectId
                                )
                            ) {

                                $resultSubjects->put(
                                    $subjectId,
                                    null
                                );
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | CALCULATE EACH SUBJECT
                        |--------------------------------------------------------------------------
                        */

                        foreach (
                            $resultSubjects as $subjectId => $config
                        ) {

                            $subjectId =
                                (int) $subjectId;


                            /*
                            |--------------------------------------------------------------
                            | Find student's mark for this subject.
                            |--------------------------------------------------------------
                            */

                            $mark =
                                $marks->first(
                                    fn ($item) =>
                                        (int)
                                        $item->canonical_subject_id
                                        ===
                                        $subjectId
                                );


                            /*
                            |--------------------------------------------------------------
                            | MAX MARKS
                            |--------------------------------------------------------------
                            */

                            if ($mark) {

                                $maxMarks =
                                    (float)
                                    (
                                        $mark->theory_max_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark->oral_max_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark->practical_max_marks
                                        ?? 0
                                    );

                            } else {

                                $maxMarks =
                                    0;
                            }


                            /*
                            |--------------------------------------------------------------
                            | FALLBACK MAX FROM EXAM CONFIG
                            |--------------------------------------------------------------
                            */

                            if (
                                $maxMarks <= 0 &&
                                $config
                            ) {

                                $maxMarks =
                                    (float)
                                    (
                                        $config->max_marks
                                        ?? 0
                                    );
                            }


                            /*
                            |--------------------------------------------------------------
                            | PASSING MARKS
                            |--------------------------------------------------------------
                            */

                            if ($mark) {

                                $passingMarks =
                                    (float)
                                    (
                                        $mark->theory_passing_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark->oral_passing_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark->practical_passing_marks
                                        ?? 0
                                    );

                            } else {

                                $passingMarks =
                                    0;
                            }


                            /*
                            |--------------------------------------------------------------
                            | FALLBACK PASSING MARKS
                            |--------------------------------------------------------------
                            */

                            if (
                                $passingMarks <= 0 &&
                                $config
                            ) {

                                $passingMarks =
                                    (float)
                                    (
                                        $config->passing_marks
                                        ?? 0
                                    );
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | OBTAINED MARKS
                            |--------------------------------------------------------------------------
                            */

                            $isAbsent =
                                false;


                            if ($mark) {

                                $isAbsent =
                                    (
                                        (int)
                                        (
                                            $mark->is_absent
                                            ?? 0
                                        )
                                    ) === 1;
                            }


                            if ($isAbsent) {

                                $obtained =
                                    0;

                            } elseif ($mark) {

                                $obtained =
                                    (float)
                                    (
                                        $mark->theory_obtained_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark->oral_obtained_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark->practical_obtained_marks
                                        ?? 0
                                    );

                            } else {

                                /*
                                |----------------------------------------------------------
                                | No marks entered = 0
                                |----------------------------------------------------------
                                */

                                $obtained =
                                    0;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | TOTAL MAX
                            |--------------------------------------------------------------------------
                            */

                            $totalMax +=
                                $maxMarks;


                            /*
                            |--------------------------------------------------------------------------
                            | TOTAL OBTAINED
                            |--------------------------------------------------------------------------
                            */

                            $totalObtained +=
                                $obtained;


                            /*
                            |--------------------------------------------------------------------------
                            | SUBJECT RESULT
                            |--------------------------------------------------------------------------
                            */

                            if ($isAbsent) {

                                $subjectResult =
                                    'ABSENT';

                                $subjectGrade =
                                    'AB';

                                $failedSubjects++;

                            } else {

                                /*
                                |----------------------------------------------------------
                                | No mark is treated as not passed.
                                |----------------------------------------------------------
                                */

                                if (
                                    $obtained >=
                                    $passingMarks
                                    &&
                                    $passingMarks > 0
                                ) {

                                    $subjectResult =
                                        'PASS';

                                } else {

                                    $subjectResult =
                                        'FAIL';

                                    $failedSubjects++;
                                }


                                $subjectGrade =
                                    $this->calculateGrade(
                                        $obtained,
                                        $maxMarks,
                                        $subjectResult
                                    );
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | STORE TEMPORARY RESULT DATA
                            |--------------------------------------------------------------------------
                            */

                            $resultSubjects
                                ->put(
                                    $subjectId,
                                    [
                                        'subject_id' =>
                                            $subjectId,

                                        'max_marks' =>
                                            $maxMarks,

                                        'obtained_marks' =>
                                            $obtained,

                                        'passing_marks' =>
                                            $passingMarks,

                                        'subject_result' =>
                                            $subjectResult,

                                        'grade' =>
                                            $subjectGrade,
                                    ]
                                );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | PERCENTAGE
                        |--------------------------------------------------------------------------
                        */

                        $percentage =
                            $totalMax > 0
                                ? round(
                                    (
                                        $totalObtained
                                        /
                                        $totalMax
                                    ) * 100,
                                    2
                                )
                                : 0;


                        /*
                        |--------------------------------------------------------------------------
                        | OVERALL RESULT
                        |--------------------------------------------------------------------------
                        */

                        $result =
                            $failedSubjects > 0
                                ? 'FAIL'
                                : 'PASS';


                        /*
                        |--------------------------------------------------------------------------
                        | OVERALL GRADE
                        |--------------------------------------------------------------------------
                        */

                        $grade =
                            $this->calculateOverallGrade(
                                $percentage
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | INSERT STUDENT RESULT
                        |--------------------------------------------------------------------------
                        */

                        $resultId =
                            DB::table(
                                'student_results'
                            )
                            ->insertGetId([

                                'academic_year_id' =>
                                    $academicYearId,

                                'exam_master_id' =>
                                    $examMasterId,

                                'standard_id' =>
                                    $standardId,

                                'division_id' =>
                                    $divisionId,

                                'student_id' =>
                                    $studentId,

                                'total_max_marks' =>
                                    $totalMax,

                                'total_obtained_marks' =>
                                    $totalObtained,

                                'percentage' =>
                                    $percentage,

                                'grade' =>
                                    $grade,

                                'result' =>
                                    $result,

                                'rank' =>
                                    null,

                                'generated_at' =>
                                    now(),

                                'created_at' =>
                                    now(),

                                'updated_at' =>
                                    now(),
                            ]);


                        /*
                        |--------------------------------------------------------------------------
                        | INSERT RESULT DETAILS
                        |--------------------------------------------------------------------------
                        */

                        foreach (
                            $resultSubjects as $subjectData
                        ) {

                            /*
                            |--------------------------------------------------------------
                            | Ignore old null entries.
                            |--------------------------------------------------------------
                            */

                            if (
                                !is_array(
                                    $subjectData
                                )
                            ) {
                                continue;
                            }


                            DB::table(
                                'student_result_details'
                            )
                            ->insert([

                                'student_result_id' =>
                                    $resultId,

                                'subject_id' =>
                                    $subjectData[
                                        'subject_id'
                                    ],

                                'max_marks' =>
                                    $subjectData[
                                        'max_marks'
                                    ],

                                'obtained_marks' =>
                                    $subjectData[
                                        'obtained_marks'
                                    ],

                                'grade' =>
                                    $subjectData[
                                        'grade'
                                    ],

                                'passing_marks' =>
                                    $subjectData[
                                        'passing_marks'
                                    ],

                                'subject_result' =>
                                    $subjectData[
                                        'subject_result'
                                    ],

                                'created_at' =>
                                    now(),

                                'updated_at' =>
                                    now(),
                            ]);
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 14
                    |--------------------------------------------------------------------------
                    | CALCULATE RANK
                    |--------------------------------------------------------------------------
                    */

                    $passStudents =
                        DB::table(
                            'student_results'
                        )
                        ->where(
                            'academic_year_id',
                            $academicYearId
                        )
                        ->where(
                            'exam_master_id',
                            $examMasterId
                        )
                        ->where(
                            'standard_id',
                            $standardId
                        )
                        ->where(
                            'division_id',
                            $divisionId
                        )
                        ->where(
                            'result',
                            'PASS'
                        )
                        ->orderByDesc(
                            'percentage'
                        )
                        ->orderByDesc(
                            'total_obtained_marks'
                        )
                        ->get();


                    $rank =
                        0;

                    $position =
                        0;

                    $previousPercentage =
                        null;

                    $previousObtained =
                        null;


                    foreach (
                        $passStudents as $studentResult
                    ) {

                        $position++;


                        if (
                            $previousPercentage !==
                                $studentResult->percentage
                            ||
                            $previousObtained !==
                                $studentResult->total_obtained_marks
                        ) {

                            $rank =
                                $position;
                        }


                        DB::table(
                            'student_results'
                        )
                        ->where(
                            'id',
                            $studentResult->id
                        )
                        ->update([
                            'rank' =>
                                $rank,
                        ]);


                        $previousPercentage =
                            $studentResult->percentage;

                        $previousObtained =
                            $studentResult->total_obtained_marks;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 15
                    |--------------------------------------------------------------------------
                    | FAILED STUDENTS DO NOT GET RANK
                    |--------------------------------------------------------------------------
                    */

                    DB::table(
                        'student_results'
                    )
                    ->where(
                        'academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'exam_master_id',
                        $examMasterId
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'division_id',
                        $divisionId
                    )
                    ->where(
                        'result',
                        'FAIL'
                    )
                    ->update([
                        'rank' =>
                            null,
                    ]);
                }
            );


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            return back()->with(
                'success',
                'Results Generated Successfully.'
            );

        } catch (\Throwable $e) {

            report($e);


            return back()
                ->withInput()
                ->with(
                    'error',
                    'Result generation failed: '
                    .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SUBJECT GRADE
    |--------------------------------------------------------------------------
    */

    private function calculateGrade(
        $obtainedMarks,
        $maxMarks,
        $subjectResult = 'PASS'
    ) {

        if ($subjectResult === 'ABSENT') {
            return 'AB';
        }


        if ($subjectResult === 'LEFT') {
            return 'LEFT';
        }


        if ($subjectResult === 'FAIL') {
            return 'F';
        }


        if ($maxMarks <= 0) {
            return '';
        }


        $percentage =
            (
                $obtainedMarks
                /
                $maxMarks
            ) * 100;


        if ($percentage >= 91) {
            return 'A1';
        }

        if ($percentage >= 81) {
            return 'A2';
        }

        if ($percentage >= 71) {
            return 'B1';
        }

        if ($percentage >= 61) {
            return 'B2';
        }

        if ($percentage >= 51) {
            return 'C1';
        }

        if ($percentage >= 41) {
            return 'C2';
        }

        return 'D';
    }


    /*
    |--------------------------------------------------------------------------
    | OVERALL GRADE
    |--------------------------------------------------------------------------
    */

    private function calculateOverallGrade(
        $percentage
    ) {

        if ($percentage >= 91) {
            return 'A1';
        }

        if ($percentage >= 81) {
            return 'A2';
        }

        if ($percentage >= 71) {
            return 'B1';
        }

        if ($percentage >= 61) {
            return 'B2';
        }

        if ($percentage >= 51) {
            return 'C1';
        }

        if ($percentage >= 41) {
            return 'C2';
        }

        if ($percentage >= 33) {
            return 'D';
        }

        return 'FAIL';
    }
}