<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    | Standard is NOT selected separately.
    | Standard is automatically determined from the selected Exam.
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
    | Stored value is already subjects.id
    |--------------------------------------------------------------------------
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
    | Stored value is standard_wise_subjects.id
    |--------------------------------------------------------------------------
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
        !empty(
            $mapping->subject_id
        )
    ) {

        return (int)
            $mapping->subject_id;
    }


    return null;
}
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
                | GET FINALLY LOCKED MARKS
                |--------------------------------------------------------------------------
                */

                $lockedMarks =
                    DB::table(
                        'student_marks'
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
                        'is_locked',
                        1
                    )
                    ->orderBy(
                        'id'
                    )
                    ->get();


                if (
                    $lockedMarks->isEmpty()
                ) {

                    throw new \Exception(
                        'No finally submitted marks found for the selected Exam, Standard and Division.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 2
                | GET TSA IDS
                |--------------------------------------------------------------------------
                */

                $tsaIds =
                    $lockedMarks
                        ->pluck(
                            'teacher_subject_allocation_id'
                        )
                        ->filter()
                        ->map(
                            fn ($id) =>
                                (int) $id
                        )
                        ->unique()
                        ->values();


                if (
                    $tsaIds->isEmpty()
                ) {

                    throw new \Exception(
                        'Locked marks do not contain valid Teacher Subject Allocation IDs.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 3
                | LOAD TSA
                |--------------------------------------------------------------------------
                |
                | TSA is authoritative for subject identity.
                |
                */

                $tsaMap =
                    DB::table(
                        'teacher_subject_allocations'
                    )
                    ->whereIn(
                        'id',
                        $tsaIds->toArray()
                    )
                    ->where(
                        'exam_master_id',
                        $examMasterId
                    )
                    ->get()
                    ->keyBy(
                        'id'
                    );


                /*
                |--------------------------------------------------------------------------
                | STEP 4
                | NORMALIZE SUBJECT ID
                |--------------------------------------------------------------------------
                |
                | student_marks.subject_id may be an old/stale ID.
                |
                | teacher_subject_allocations.subject_id is used as
                | the authoritative assignment.
                |
                */

                foreach (
                    $lockedMarks as $mark
                ) {

                    $tsa =
                        $tsaMap->get(
                            (int)
                            $mark->teacher_subject_allocation_id
                        );


                    if (!$tsa) {

                        throw new \Exception(
                            'Teacher Subject Allocation '
                            .
                            $mark->teacher_subject_allocation_id
                            .
                            ' was not found for locked mark ID '
                            .
                            $mark->id
                            .
                            '.'
                        );
                    }


                    /*
                    |----------------------------------------------------------------------
                    | RESOLVE CANONICAL SUBJECT
                    |---------------------------------------------------------------------- 
                    */

                    $canonicalSubjectId =
                        $this->resolveCanonicalSubjectId(
                            $tsa->subject_id,
                            $standardId
                        );


                    if (!$canonicalSubjectId) {

                        throw new \Exception(
                            'Unable to resolve the subject for Teacher Subject Allocation '
                            .
                            $tsa->id
                            .
                            ' for the selected Standard.'
                        );
                    }


                    /*
                    |----------------------------------------------------------------------
                    | STORE TEMPORARILY ON COLLECTION OBJECT
                    |----------------------------------------------------------------------
                    */

                    $mark->canonical_subject_id =
                        (int)
                        $canonicalSubjectId;
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 5
                | GET CANONICAL SUBJECT IDS
                |--------------------------------------------------------------------------
                */

                $subjectIds =
                    $lockedMarks
                        ->pluck(
                            'canonical_subject_id'
                        )
                        ->filter()
                        ->map(
                            fn ($id) =>
                                (int) $id
                        )
                        ->unique()
                        ->values();


                if (
                    $subjectIds->isEmpty()
                ) {

                    throw new \Exception(
                        'No valid subject IDs found in locked marks.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 6
                | VERIFY SUBJECT MASTER
                |--------------------------------------------------------------------------
                */

                $existingSubjectIds =
                    DB::table(
                        'subjects'
                    )
                    ->whereIn(
                        'id',
                        $subjectIds->toArray()
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->pluck(
                        'id'
                    )
                    ->map(
                        fn ($id) =>
                            (int) $id
                    )
                    ->unique()
                    ->values()
                    ->toArray();


                $invalidSubjectIds =
                    $subjectIds
                        ->filter(
                            fn ($id) =>
                                !in_array(
                                    (int) $id,
                                    $existingSubjectIds,
                                    true
                                )
                        )
                        ->values();


                if (
                    $invalidSubjectIds->isNotEmpty()
                ) {

                    throw new \Exception(
                        'Invalid subject IDs found in locked marks: '
                        .
                        $invalidSubjectIds->implode(', ')
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 7
                | VERIFY SUBJECTS BELONG TO STANDARD
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                |
                | Use standard_wise_subjects.subject_id
                | NOT standard_wise_subjects.id.
                |
                */

                $validStandardSubjectIds =
                    DB::table(
                        'standard_wise_subjects'
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->whereIn(
                        'subject_id',
                        $subjectIds->toArray()
                    )
                    ->pluck(
                        'subject_id'
                    )
                    ->map(
                        fn ($id) =>
                            (int) $id
                    )
                    ->unique()
                    ->values()
                    ->toArray();


                $invalidStandardSubjects =
                    $subjectIds
                        ->filter(
                            fn ($id) =>
                                !in_array(
                                    (int) $id,
                                    $validStandardSubjectIds,
                                    true
                                )
                        )
                        ->values();


                if (
                    $invalidStandardSubjects->isNotEmpty()
                ) {

                    /*
                    |----------------------------------------------------------------------
                    | GET SUBJECT NAMES FOR A BETTER ERROR
                    |---------------------------------------------------------------------- 
                    */

                    $invalidSubjectNames =
                        DB::table(
                            'subjects'
                        )
                        ->whereIn(
                            'id',
                            $invalidStandardSubjects->toArray()
                        )
                        ->pluck(
                            'subject_name',
                            'id'
                        );


                    $formatted =
                        $invalidStandardSubjects
                            ->map(
                                function ($id) use (
                                    $invalidSubjectNames
                                ) {

                                    $name =
                                        $invalidSubjectNames
                                            ->get(
                                                $id
                                            )
                                            ?? 'UNKNOWN';

                                    return
                                        $id
                                        . ' ('
                                        . $name
                                        . ')';
                                }
                            )
                            ->implode(
                                ', '
                            );


                    throw new \Exception(
                        'The following subject IDs are not mapped to the selected Standard: '
                        .
                        $formatted
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 8
                | LOAD EXAM SUBJECT CONFIGURATION
                |--------------------------------------------------------------------------
                |
                | Use canonical subjects.id.
                |
                */

                $examSubjectConfigs =
                    DB::table(
                        'exam_master_subjects'
                    )
                    ->where(
                        'exam_master_id',
                        $examMasterId
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->whereIn(
                        'subject_id',
                        $subjectIds->toArray()
                    )
                    ->get()
                    ->keyBy(
                        'subject_id'
                    );


                /*
                |--------------------------------------------------------------------------
                | VERIFY EXAM CONFIGURATION
                |--------------------------------------------------------------------------
                */

                foreach (
                    $subjectIds as $subjectId
                ) {

                    if (
                        !$examSubjectConfigs
                            ->has(
                                $subjectId
                            )
                    ) {

                        $subjectName =
                            DB::table(
                                'subjects'
                            )
                            ->where(
                                'id',
                                $subjectId
                            )
                            ->value(
                                'subject_name'
                            )
                            ?? 'UNKNOWN';


                        throw new \Exception(
                            'Exam Subject configuration is missing for '
                            .
                            $subjectName
                            .
                            ' (Subject ID '
                            .
                            $subjectId
                            .
                            ').'
                        );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 9
                | NORMALIZE SUBJECT CONFIGURATION ON MARKS
                |--------------------------------------------------------------------------
                |
                | Use exam_master_subjects where available.
                |
                */

                foreach (
                    $lockedMarks as $mark
                ) {

                    $subjectId =
                        (int)
                        $mark->canonical_subject_id;


                    $config =
                        $examSubjectConfigs->get(
                            $subjectId
                        );


                    /*
                    |------------------------------------------------------------------
                    | Theory configuration
                    |------------------------------------------------------------------
                    */

                    if (
                        $config
                    ) {

                        /*
                        | NOTE:
                        | Existing student_marks columns are retained if present.
                        | Exam configuration is used as fallback.
                        */

                        if (
                            !isset(
                                $mark->theory_max_marks
                            )
                            ||
                            $mark->theory_max_marks === null
                        ) {

                            $mark->theory_max_marks =
                                $config->max_marks
                                ?? 0;
                        }


                        if (
                            !isset(
                                $mark->theory_passing_marks
                            )
                            ||
                            $mark->theory_passing_marks === null
                        ) {

                            $mark->theory_passing_marks =
                                $config->passing_marks
                                ?? 0;
                        }
                    }


                    /*
                    |------------------------------------------------------------------
                    | Oral / Practical fallback
                    |------------------------------------------------------------------
                    */

                    if (
                        !isset(
                            $mark->oral_max_marks
                        )
                        ||
                        $mark->oral_max_marks === null
                    ) {

                        $mark->oral_max_marks =
                            0;
                    }


                    if (
                        !isset(
                            $mark->oral_passing_marks
                        )
                        ||
                        $mark->oral_passing_marks === null
                    ) {

                        $mark->oral_passing_marks =
                            0;
                    }


                    if (
                        !isset(
                            $mark->practical_max_marks
                        )
                        ||
                        $mark->practical_max_marks === null
                    ) {

                        $mark->practical_max_marks =
                            0;
                    }


                    if (
                        !isset(
                            $mark->practical_passing_marks
                        )
                        ||
                        $mark->practical_passing_marks === null
                    ) {

                        $mark->practical_passing_marks =
                            0;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 10
                | KEEP LATEST MARK PER STUDENT + CANONICAL SUBJECT
                |--------------------------------------------------------------------------
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


                    /*
                    |------------------------------------------------------------------
                    | Ordered ASC above, so replace existing entry.
                    |------------------------------------------------------------------
                    */

                    $uniqueMarks->put(
                        $key,
                        $mark
                    );
                }


                $uniqueMarks =
                    $uniqueMarks
                        ->values();


                /*
                |--------------------------------------------------------------------------
                | STEP 11
                | DELETE OLD GENERATED RESULTS
                |--------------------------------------------------------------------------
                |
                | This is inside the transaction.
                |
                | If generation fails, the transaction rolls back.
                |
                */

                $existingResultIds =
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
                    ->pluck(
                        'id'
                    );


                if (
                    $existingResultIds
                        ->isNotEmpty()
                ) {

                    DB::table(
                        'student_result_details'
                    )
                    ->whereIn(
                        'student_result_id',
                        $existingResultIds
                            ->toArray()
                    )
                    ->delete();


                    DB::table(
                        'student_results'
                    )
                    ->whereIn(
                        'id',
                        $existingResultIds
                            ->toArray()
                    )
                    ->delete();
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 12
                | STUDENTS
                |--------------------------------------------------------------------------
                */

                $studentIds =
                    $uniqueMarks
                        ->pluck(
                            'student_id'
                        )
                        ->filter()
                        ->map(
                            fn ($id) =>
                                (int) $id
                        )
                        ->unique()
                        ->values();


                if (
                    $studentIds->isEmpty()
                ) {

                    throw new \Exception(
                        'No students found in locked marks.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 13
                | GENERATE RESULT STUDENT BY STUDENT
                |--------------------------------------------------------------------------
                */

                foreach (
                    $studentIds as $studentId
                ) {

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


                    if (
                        $marks->isEmpty()
                    ) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL MAX
                    |--------------------------------------------------------------------------
                    */

                    $totalMax =
                        0;


                    foreach (
                        $marks as $mark
                    ) {

                        $subjectMax =
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


                        /*
                        |--------------------------------------------------------------
                        | FALLBACK IF MAX IS ZERO
                        |--------------------------------------------------------------
                        */

                        if (
                            $subjectMax <= 0
                        ) {

                            $config =
                                $examSubjectConfigs
                                    ->get(
                                        (int)
                                        $mark->canonical_subject_id
                                    );


                            if (
                                $config
                            ) {

                                $subjectMax =
                                    (float)
                                    (
                                        $config->max_marks
                                        ?? 0
                                    );
                            }
                        }


                        $totalMax +=
                            $subjectMax;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL OBTAINED
                    |--------------------------------------------------------------------------
                    */

                    $totalObtained =
                        0;


                    foreach (
                        $marks as $mark
                    ) {

                        if (
                            (int)
                            $mark->is_absent === 1
                        ) {

                            continue;
                        }


                        $totalObtained +=
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
                    | FAILED SUBJECT COUNT
                    |--------------------------------------------------------------------------
                    */

                    $failedSubjects =
                        0;


                    foreach (
                        $marks as $mark
                    ) {

                        if (
                            (int)
                            $mark->is_absent === 1
                        ) {

                            $failedSubjects++;

                            continue;
                        }


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


                        $passing =
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


                        /*
                        |------------------------------------------------------------------
                        | FALLBACK PASSING MARKS
                        |------------------------------------------------------------------
                        */

                        if (
                            $passing <= 0
                        ) {

                            $config =
                                $examSubjectConfigs
                                    ->get(
                                        (int)
                                        $mark->canonical_subject_id
                                    );


                            if (
                                $config
                            ) {

                                $passing =
                                    (float)
                                    (
                                        $config->passing_marks
                                        ?? 0
                                    );
                            }
                        }


                        if (
                            $obtained <
                            $passing
                        ) {

                            $failedSubjects++;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | RESULT
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
                        $marks as $mark
                    ) {

                        $subjectId =
                            (int)
                            $mark->canonical_subject_id;


                        /*
                        |------------------------------------------------------------------
                        | MAX MARKS
                        |------------------------------------------------------------------
                        */

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


                        /*
                        |------------------------------------------------------------------
                        | FALLBACK MAX
                        |------------------------------------------------------------------
                        */

                        if (
                            $maxMarks <= 0
                        ) {

                            $config =
                                $examSubjectConfigs
                                    ->get(
                                        $subjectId
                                    );


                            if (
                                $config
                            ) {

                                $maxMarks =
                                    (float)
                                    (
                                        $config->max_marks
                                        ?? 0
                                    );
                            }
                        }


                        /*
                        |------------------------------------------------------------------
                        | PASSING
                        |------------------------------------------------------------------
                        */

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


                        if (
                            $passingMarks <= 0
                        ) {

                            $config =
                                $examSubjectConfigs
                                    ->get(
                                        $subjectId
                                    );


                            if (
                                $config
                            ) {

                                $passingMarks =
                                    (float)
                                    (
                                        $config->passing_marks
                                        ?? 0
                                    );
                            }
                        }


                        /*
                        |------------------------------------------------------------------
                        | ABSENT
                        |------------------------------------------------------------------
                        */

                        if (
                            (int)
                            $mark->is_absent === 1
                        ) {

                            $obtained =
                                0;

                            $subjectResult =
                                'ABSENT';

                            $subjectGrade =
                                'AB';

                        } else {

                            /*
                            |----------------------------------------------------------------
                            | OBTAINED
                            |----------------------------------------------------------------
                            */

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


                            /*
                            |----------------------------------------------------------------
                            | RESULT
                            |----------------------------------------------------------------
                            */

                            $subjectResult =
                                $obtained >=
                                $passingMarks
                                    ? 'PASS'
                                    : 'FAIL';


                            /*
                            |----------------------------------------------------------------
                            | GRADE
                            |----------------------------------------------------------------
                            */

                            $subjectGrade =
                                $this->calculateGrade(
                                    $obtained,
                                    $maxMarks,
                                    $subjectResult
                                );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | INSERT
                        |--------------------------------------------------------------------------
                        |
                        | IMPORTANT:
                        |
                        | Use canonical subjects.id.
                        |
                        */

                        DB::table(
                            'student_result_details'
                        )
                        ->insert([

                            'student_result_id' =>
                                $resultId,

                            'subject_id' =>
                                $subjectId,

                            'max_marks' =>
                                $maxMarks,

                            'obtained_marks' =>
                                $obtained,

                            'grade' =>
                                $subjectGrade,

                            'passing_marks' =>
                                $passingMarks,

                            'subject_result' =>
                                $subjectResult,

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