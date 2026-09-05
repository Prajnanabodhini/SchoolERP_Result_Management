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
    */

    public function index()
    {
        $exams =
            ExamMaster::query()
                ->orderBy('display_order')
                ->orderBy('exam_name')
                ->get();

        $divisions =
            Division::query()
                ->orderBy('display_order')
                ->get();

        $academicYears =
            AcademicYear::query()
                ->orderByDesc('id')
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
    | 2. standard_wise_subjects.id
    |
    | Always return the actual subjects.id.
    |--------------------------------------------------------------------------
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

        $storedSubjectId =
            (int) $storedSubjectId;

        $standardId =
            (int) $standardId;

        if (
            $storedSubjectId <= 0 ||
            $standardId <= 0
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | CASE 1
        | Stored value is subjects.id
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

        if (
            $directSubjectExists
        ) {

            $validMapping =
                DB::table(
                    'standard_wise_subjects'
                )
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

            if (
                $validMapping
            ) {

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
            DB::table(
                'standard_wise_subjects'
            )
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

            return (int)
                $mapping->subject_id;
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | GET PASSING PERCENTAGE
    |--------------------------------------------------------------------------
    |
    | 35%:
    |
    | Nursery
    | JrKg
    | SrKg
    | 9th
    | 10th
    | 11th
    | 12th
    |
    | 40%:
    |
    | All other standards.
    |--------------------------------------------------------------------------
    */

    private function getPassingPercentage(
        $standardId
    ): float {

        $standardId =
            (int) $standardId;

        /*
        |--------------------------------------------------------------------------
        | 9TH / 10TH / 11TH / 12TH
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $standardId,
                [
                    9,
                    10,
                    11,
                    12,
                ],
                true
            )
        ) {

            return 35.0;
        }

        /*
        |--------------------------------------------------------------------------
        | NURSERY / JRKG / SRKG BY NAME
        |--------------------------------------------------------------------------
        */

        $standardName =
            DB::table(
                'standards'
            )
            ->where(
                'id',
                $standardId
            )
            ->value(
                'standard_name'
            );

        $normalizedName =
            preg_replace(
                '/[^A-Z0-9]+/',
                '',
                strtoupper(
                    trim(
                        (string) $standardName
                    )
                )
            );

        if (
            in_array(
                $normalizedName,
                [
                    'NURSERY',
                    'NUR',
                    'JRKG',
                    'JUNIORKG',
                    'JUNIORKINDERGARTEN',
                    'SRKG',
                    'SENIORKG',
                    'SENIORKINDERGARTEN',
                ],
                true
            )
        ) {

            return 35.0;
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT
        |--------------------------------------------------------------------------
        */

        return 40.0;
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE FALLBACK PASSING MARKS
    |--------------------------------------------------------------------------
    */

    private function calculatePassingMarks(
        $maxMarks,
        $standardId
    ): float {

        $maxMarks =
            (float) $maxMarks;

        if (
            $maxMarks <= 0
        ) {

            return 0;
        }

        $percentage =
            $this->getPassingPercentage(
                $standardId
            );

        return (float) ceil(
            $maxMarks *
            (
                $percentage / 100
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE RESULTS
    |--------------------------------------------------------------------------
    */

    public function generate(
        Request $request
    ) {

        /*
        |--------------------------------------------------------------------------
        | REQUEST VALUES
        |--------------------------------------------------------------------------
        */

        $academicYearId =
            (int) $request->academic_year_id;

        $examMasterId =
            (int) $request->exam_master_id;

        $divisionId =
            (int) $request->division_id;


        /*
        |--------------------------------------------------------------------------
        | BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            !$academicYearId
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Please select Academic Year.'
                );
        }

        if (
            !$examMasterId ||
            !$divisionId
        ) {

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
            ExamMaster::query()
                ->where(
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
        | VERIFY EXAM ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $exam->academic_year_id
            )
            &&
            (int) $exam->academic_year_id
                !== $academicYearId
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam does not belong to the selected Academic Year.'
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

        if (
            $standardId <= 0
        ) {

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
                    | GET LOCKED MARKS
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
                        ->orderBy('id')
                        ->get();


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 2
                    |--------------------------------------------------------------------------
                    | IF NO LOCKED MARKS, USE EXISTING MARKS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedMarks->isEmpty()
                    ) {

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
                            ->orderBy('id')
                            ->get();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 3
                    |--------------------------------------------------------------------------
                    | LOAD EXAM SUBJECT CONFIGURATION
                    |--------------------------------------------------------------------------
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
                        ->orderBy(
                            'display_order'
                        )
                        ->orderBy(
                            'id'
                        )
                        ->get();


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 4
                    |--------------------------------------------------------------------------
                    | NORMALIZE EXAM SUBJECT IDS
                    |--------------------------------------------------------------------------
                    */

                    $normalizedExamSubjects =
                        collect();

                    foreach (
                        $examSubjectConfigs
                        as $config
                    ) {

                        $canonicalSubjectId =
                            $this->resolveCanonicalSubjectId(
                                $config->subject_id
                                    ?? null,
                                $standardId
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | DIRECT SUBJECT FALLBACK
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !$canonicalSubjectId
                        ) {

                            $directExists =
                                !empty(
                                    $config->subject_id
                                )
                                &&
                                DB::table(
                                    'subjects'
                                )
                                ->where(
                                    'id',
                                    (int)
                                    $config->subject_id
                                )
                                ->where(
                                    'is_active',
                                    1
                                )
                                ->exists();


                            if (
                                $directExists
                            ) {

                                $canonicalSubjectId =
                                    (int)
                                    $config->subject_id;
                            }
                        }


                        if (
                            $canonicalSubjectId
                        ) {

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


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 6
                    |--------------------------------------------------------------------------
                    | LOAD TSA
                    |--------------------------------------------------------------------------
                    */

                    $tsaMap =
                        collect();

                    if (
                        $tsaIds->isNotEmpty()
                    ) {

                        $tsaMap =
                            DB::table(
                                'teacher_subject_allocations'
                            )
                            ->whereIn(
                                'id',
                                $tsaIds->toArray()
                            )
                            ->get()
                            ->keyBy(
                                'id'
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 7
                    |--------------------------------------------------------------------------
                    | NORMALIZE MARK SUBJECT IDS
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $lockedMarks as $mark
                    ) {

                        $canonicalSubjectId =
                            null;


                        /*
                        |--------------------------------------------------------------------------
                        | TSA SUBJECT FIRST
                        |--------------------------------------------------------------------------
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

                            if (
                                $tsa
                            ) {

                                $canonicalSubjectId =
                                    $this->resolveCanonicalSubjectId(
                                        $tsa->subject_id
                                            ?? null,
                                        $standardId
                                    );
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | STUDENT MARK SUBJECT FALLBACK
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !$canonicalSubjectId
                        ) {

                            $canonicalSubjectId =
                                $this->resolveCanonicalSubjectId(
                                    $mark->subject_id
                                        ?? null,
                                    $standardId
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | DIRECT SUBJECT FALLBACK
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !$canonicalSubjectId
                            ) {

                                $directExists =
                                    !empty(
                                        $mark->subject_id
                                    )
                                    &&
                                    DB::table(
                                        'subjects'
                                    )
                                    ->where(
                                        'id',
                                        (int)
                                        $mark->subject_id
                                    )
                                    ->where(
                                        'is_active',
                                        1
                                    )
                                    ->exists();


                                if (
                                    $directExists
                                ) {

                                    $canonicalSubjectId =
                                        (int)
                                        $mark->subject_id;
                                }
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | SAVE CANONICAL ID IN MEMORY
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $canonicalSubjectId
                        ) {

                            $mark->canonical_subject_id =
                                (int)
                                $canonicalSubjectId;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | NORMALIZE OPTIONAL VALUE
                        |--------------------------------------------------------------------------
                        */

                        $mark->is_optional =
                            (
                                isset(
                                    $mark->is_optional
                                )
                                &&
                                (int)
                                $mark->is_optional === 1
                            )
                                ? 1
                                : 0;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 8
                    |--------------------------------------------------------------------------
                    | REMOVE UNRESOLVED SUBJECTS
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
                    | UNIQUE STUDENT + SUBJECT MARKS
                    |--------------------------------------------------------------------------
                    |
                    | Latest record wins.
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
                            . '_'
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


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 11
                    |--------------------------------------------------------------------------
                    | FALLBACK TO LOCAL STUDENTS TABLE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $studentIds->isEmpty()
                    ) {

                        $studentQuery =
                            DB::table(
                                'students'
                            );


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


                        $studentIds =
                            $studentQuery
                                ->pluck('id')
                                ->map(
                                    fn ($id) =>
                                        (int) $id
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
                    | GENERATE EACH STUDENT
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $studentIds as $studentId
                    ) {

                        /*
                        |--------------------------------------------------------------------------
                        | STUDENT MARKS
                        |--------------------------------------------------------------------------
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
                        | TOTAL VARIABLES
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
                        | STEP 13A
                        |--------------------------------------------------------------------------
                        | LOAD STANDARD SUBJECT OPTIONAL FLAGS
                        |--------------------------------------------------------------------------
                        */

                        $standardSubjectRows =
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
                            ->select([
                                'subject_id',
                                'is_optional',
                            ])
                            ->get()
                            ->keyBy(
                                fn ($row) =>
                                    (int)
                                    $row->subject_id
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | STEP 13B
                        |--------------------------------------------------------------------------
                        | BUILD REQUIRED SUBJECT LIST
                        |--------------------------------------------------------------------------
                        |
                        | Required subjects are always included.
                        |
                        | Optional subjects are NOT included here.
                        |--------------------------------------------------------------------------
                        */

                        $resultSubjects =
                            collect();


                        foreach (
                            $normalizedExamSubjects
                            as $config
                        ) {

                            $subjectId =
                                (int)
                                $config->canonical_subject_id;


                            if (
                                $subjectId <= 0
                            ) {

                                continue;
                            }


                            $standardSubject =
                                $standardSubjectRows->get(
                                    $subjectId
                                );


                            $isPoolOptional =
                                $standardSubject
                                &&
                                (
                                    (int)
                                    (
                                        $standardSubject->is_optional
                                        ?? 0
                                    )
                                ) === 1;


                            /*
                            |--------------------------------------------------------------------------
                            | REQUIRED SUBJECT
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !$isPoolOptional
                            ) {

                                $resultSubjects->put(
                                    $subjectId,
                                    $config
                                );
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | STEP 13C
                        |--------------------------------------------------------------------------
                        | FIND STUDENT'S SELECTED OPTIONAL SUBJECT
                        |--------------------------------------------------------------------------
                        |
                        | A student may have Biology OR Geography OR Mathematics,
                        | etc. Only the subject marked with is_optional=1 is selected.
                        |--------------------------------------------------------------------------
                        */

                        $selectedOptionalMarks =
                            $marks
                                ->filter(
                                    function ($mark) {

                                        return
                                            (
                                                (int)
                                                (
                                                    $mark->is_optional
                                                    ?? 0
                                                )
                                            ) === 1;
                                    }
                                );


                        /*
                        |--------------------------------------------------------------------------
                        | Normally there should be only one selected optional
                        | subject. Use the latest one if old duplicate data exists.
                        |--------------------------------------------------------------------------
                        */

                        $selectedOptionalMark =
                            $selectedOptionalMarks
                                ->sortByDesc(
                                    'id'
                                )
                                ->first();


                        /*
                        |--------------------------------------------------------------------------
                        | STEP 13D
                        |--------------------------------------------------------------------------
                        | VALIDATE SELECTED OPTIONAL SUBJECT
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $selectedOptionalMark
                        ) {

                            $optionalSubjectId =
                                (int)
                                $selectedOptionalMark
                                    ->canonical_subject_id;


                            $optionalPoolSubject =
                                $standardSubjectRows->get(
                                    $optionalSubjectId
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | Add only when it is actually configured as an
                            | optional subject for this Standard.
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $optionalSubjectId > 0
                                &&
                                $optionalPoolSubject
                                &&
                                (
                                    (int)
                                    (
                                        $optionalPoolSubject
                                            ->is_optional
                                        ?? 0
                                    )
                                ) === 1
                            ) {

                                $optionalConfig =
                                    $normalizedExamSubjects
                                        ->first(
                                            function (
                                                $config
                                            ) use (
                                                $optionalSubjectId
                                            ) {

                                                return
                                                    (int)
                                                    $config
                                                        ->canonical_subject_id
                                                    ===
                                                    $optionalSubjectId;
                                            }
                                        );


                                /*
                                |--------------------------------------------------------------------------
                                | Add selected optional subject
                                |--------------------------------------------------------------------------
                                */

                                $resultSubjects->put(
                                    $optionalSubjectId,
                                    $optionalConfig
                                );
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | STEP 13E
                        |--------------------------------------------------------------------------
                        | CALCULATE EACH RESULT SUBJECT
                        |--------------------------------------------------------------------------
                        */

                        foreach (
                            $resultSubjects
                            as $subjectId => $config
                        ) {

                            $subjectId =
                                (int)
                                $subjectId;


                            /*
                            |--------------------------------------------------------------------------
                            | STUDENT MARK
                            |--------------------------------------------------------------------------
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
                            |--------------------------------------------------------------------------
                            | IS OPTIONAL
                            |--------------------------------------------------------------------------
                            |
                            | A subject is OPT only when the student's mark
                            | explicitly says is_optional=1 AND the Standard
                            | says that subject is optional.
                            |--------------------------------------------------------------------------
                            */

                            $standardSubject =
                                $standardSubjectRows->get(
                                    $subjectId
                                );


                            $isOptional =
                                $mark
                                &&
                                $standardSubject
                                &&
                                (
                                    (int)
                                    (
                                        $mark->is_optional
                                        ?? 0
                                    )
                                ) === 1
                                &&
                                (
                                    (int)
                                    (
                                        $standardSubject
                                            ->is_optional
                                        ?? 0
                                    )
                                ) === 1;


                            /*
                            |--------------------------------------------------------------------------
                            | OPTIONAL SUBJECT
                            |--------------------------------------------------------------------------
                            |
                            | It is displayed as OPT but has ZERO contribution
                            | to result calculation.
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $isOptional
                            ) {

                                $resultSubjects->put(
                                    $subjectId,
                                    [

                                        'subject_id' =>
                                            $subjectId,

                                        'max_marks' =>
                                            0,

                                        'obtained_marks' =>
                                            0,

                                        'passing_marks' =>
                                            0,

                                        'subject_result' =>
                                            'OPT',

                                        'grade' =>
                                            'OPT',

                                        'is_optional' =>
                                            1,

                                    ]
                                );


                                continue;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | MAX MARKS
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $mark
                            ) {

                                $maxMarks =
                                    (float)
                                    (
                                        $mark
                                            ->theory_max_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark
                                            ->oral_max_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark
                                            ->practical_max_marks
                                        ?? 0
                                    );

                            } else {

                                $maxMarks =
                                    0;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | FALLBACK MAX MARKS
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $maxMarks <= 0
                                &&
                                $config
                            ) {

                                $maxMarks =
                                    (float)
                                    (
                                        $config
                                            ->max_marks
                                        ?? 0
                                    );
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | PASSING MARKS
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $mark
                            ) {

                                $passingMarks =
                                    (float)
                                    (
                                        $mark
                                            ->theory_passing_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark
                                            ->oral_passing_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark
                                            ->practical_passing_marks
                                        ?? 0
                                    );

                            } else {

                                $passingMarks =
                                    0;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | FALLBACK PASSING MARKS
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $passingMarks <= 0
                            ) {

                                if (
                                    $config
                                ) {

                                    $configuredMax =
                                        (float)
                                        (
                                            $config
                                                ->max_marks
                                            ?? 0
                                        );


                                    $passingMarks =
                                        $this->calculatePassingMarks(
                                            $configuredMax > 0
                                                ? $configuredMax
                                                : $maxMarks,
                                            $standardId
                                        );

                                } elseif (
                                    $maxMarks > 0
                                ) {

                                    $passingMarks =
                                        $this->calculatePassingMarks(
                                            $maxMarks,
                                            $standardId
                                        );
                                }
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | ABSENT
                            |--------------------------------------------------------------------------
                            */

                            $isAbsent =
                                false;


                            if (
                                $mark
                            ) {

                                $isAbsent =
                                    (
                                        (int)
                                        (
                                            $mark
                                                ->is_absent
                                            ?? 0
                                        )
                                    ) === 1;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | OBTAINED
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $isAbsent
                            ) {

                                $obtained =
                                    0;

                            } elseif (
                                $mark
                            ) {

                                $obtained =
                                    (float)
                                    (
                                        $mark
                                            ->theory_obtained_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark
                                            ->oral_obtained_marks
                                        ?? 0
                                    )
                                    +
                                    (float)
                                    (
                                        $mark
                                            ->practical_obtained_marks
                                        ?? 0
                                    );

                            } else {

                                $obtained =
                                    0;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | TOTALS
                            |--------------------------------------------------------------------------
                            */

                            $totalMax +=
                                $maxMarks;


                            $totalObtained +=
                                $obtained;


                            /*
                            |--------------------------------------------------------------------------
                            | SUBJECT RESULT
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $isAbsent
                            ) {

                                $subjectResult =
                                    'ABSENT';


                                $subjectGrade =
                                    'AB';


                                $failedSubjects++;

                            } else {

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
                            | SAVE NORMAL SUBJECT RESULT
                            |--------------------------------------------------------------------------
                            */

                            $resultSubjects->put(
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

                                    'is_optional' =>
                                        0,

                                ]
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | STEP 13F
                        |--------------------------------------------------------------------------
                        | PERCENTAGE
                        |--------------------------------------------------------------------------
                        |
                        | Optional subjects were never added to totalMax
                        | or totalObtained.
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
                            $resultSubjects
                            as $subjectData
                        ) {

                            if (
                                !is_array(
                                    $subjectData
                                )
                            ) {

                                continue;
                            }


                            $resultDetailData = [

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
                            ];


                            /*
                            |--------------------------------------------------------------------------
                            | SAVE OPTIONAL FLAG
                            |--------------------------------------------------------------------------
                            */

                            if (
                                Schema::hasColumn(
                                    'student_result_details',
                                    'is_optional'
                                )
                            ) {

                                $resultDetailData[
                                    'is_optional'
                                ] =
                                    (int)
                                    (
                                        $subjectData[
                                            'is_optional'
                                        ]
                                        ?? 0
                                    );
                            }


                            DB::table(
                                'student_result_details'
                            )
                            ->insert(
                                $resultDetailData
                            );
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
                        $passStudents as
                        $studentResult
                    ) {

                        $position++;


                        if (
                            $previousPercentage !==
                                $studentResult
                                    ->percentage
                            ||
                            $previousObtained !==
                                $studentResult
                                    ->total_obtained_marks
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
                            $studentResult
                                ->total_obtained_marks;
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

            return back()
                ->with(
                    'success',
                    'Results Generated Successfully.'
                );

        } catch (
            \Throwable $e
        ) {

            report(
                $e
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Result generation failed: '
                    . $e->getMessage()
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

        if (
            $subjectResult === 'OPT'
        ) {

            return 'OPT';
        }


        if (
            $subjectResult === 'ABSENT'
        ) {

            return 'AB';
        }


        if (
            $subjectResult === 'LEFT'
        ) {

            return 'LEFT';
        }


        if (
            $subjectResult === 'FAIL'
        ) {

            return 'F';
        }


        if (
            $maxMarks <= 0
        ) {

            return '';
        }


        $percentage =
            (
                $obtainedMarks
                /
                $maxMarks
            ) * 100;


        if (
            $percentage >= 91
        ) {

            return 'A1';
        }


        if (
            $percentage >= 81
        ) {

            return 'A2';
        }


        if (
            $percentage >= 71
        ) {

            return 'B1';
        }


        if (
            $percentage >= 61
        ) {

            return 'B2';
        }


        if (
            $percentage >= 51
        ) {

            return 'C1';
        }


        if (
            $percentage >= 41
        ) {

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

        if (
            $percentage >= 91
        ) {

            return 'A1';
        }


        if (
            $percentage >= 81
        ) {

            return 'A2';
        }


        if (
            $percentage >= 71
        ) {

            return 'B1';
        }


        if (
            $percentage >= 61
        ) {

            return 'B2';
        }


        if (
            $percentage >= 51
        ) {

            return 'C1';
        }


        if (
            $percentage >= 41
        ) {

            return 'C2';
        }


        if (
            $percentage >= 33
        ) {

            return 'D';
        }


        return 'FAIL';
    }
}