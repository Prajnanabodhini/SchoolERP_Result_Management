<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\ExamMaster;
use App\Models\Division;
use App\Models\AcademicYear;
use App\Models\ExamMasterSubject;
use App\Models\ExamSubject;

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
    | teacher_subject_allocations.subject_id and other subject references
    | may contain either:
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
            !$storedSubjectId
            ||
            !$standardId
        ) {
            return null;
        }

        $storedSubjectId =
            (int) $storedSubjectId;

        $standardId =
            (int) $standardId;

        if (
            $storedSubjectId <= 0
            ||
            $standardId <= 0
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | CASE 1: STORED VALUE IS subjects.id
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
        | CASE 2: STORED VALUE IS standard_wise_subjects.id
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
            $mapping
            &&
            !empty($mapping->subject_id)
        ) {
            return (int) $mapping->subject_id;
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
        | NURSERY / JRKG / SRKG
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
            !$examMasterId
            ||
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

        if (
            !$exam
        ) {

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
                    | NO MARKS PROTECTION
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $lockedMarks->isEmpty()
                    ) {

                        throw new \RuntimeException(
                            'No student marks were found for the selected Exam, Standard and Division.'
                        );
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

                    if (
                        $examSubjectConfigs->isEmpty()
                    ) {

                        throw new \RuntimeException(
                            'No subject configuration exists for the selected Exam.'
                        );
                    }

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
                        $examSubjectConfigs as $config
                    ) {

                        $canonicalSubjectId =
                            $this->resolveCanonicalSubjectId(
                                $config->subject_id ?? null,
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
                                    (int) $config->subject_id
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
                                (int)
                                $canonicalSubjectId;

                            $normalizedExamSubjects->push(
                                $config
                            );
                        }
                    }

                    if (
                        $normalizedExamSubjects->isEmpty()
                    ) {

                        throw new \RuntimeException(
                            'No valid subjects could be resolved for the selected Exam.'
                        );
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
                                        $tsa->subject_id ?? null,
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
                                    $mark->subject_id ?? null,
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
                                        (int) $mark->subject_id
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
                        | SAVE CANONICAL SUBJECT ID IN MEMORY
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
                        | NORMALIZE OPTIONAL FLAG
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

                    if (
                        $lockedMarks->isEmpty()
                    ) {

                        throw new \RuntimeException(
                            'No valid subject marks could be resolved for result generation.'
                        );
                    }

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

                    if (
                        $studentIds->isEmpty()
                    ) {

                        throw new \RuntimeException(
                            'No students were found in the selected marks.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | STEP 11
                    |--------------------------------------------------------------------------
                    | LOAD STANDARD SUBJECT OPTIONAL FLAGS ONCE
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
                    | GENERATE EACH STUDENT RESULT
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
                            0.0;

                        $totalObtained =
                            0.0;

                        $failedSubjects =
                            0;

                        /*
                        |--------------------------------------------------------------------------
                        | RESULT SUBJECTS
                        |--------------------------------------------------------------------------
                        */

                        $resultSubjects =
                            collect();

                        /*
                        |--------------------------------------------------------------------------
                        | STEP 13A
                        |--------------------------------------------------------------------------
                        | ADD ALL COMPULSORY SUBJECTS
                        |--------------------------------------------------------------------------
                        */

                        foreach (
                            $normalizedExamSubjects as $config
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
                            | COMPULSORY SUBJECT
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
                        | STEP 13B
                        |--------------------------------------------------------------------------
                        | FIND ALL STUDENT SELECTED OPTIONAL SUBJECTS
                        |--------------------------------------------------------------------------
                        |
                        | Senior standards can have multiple selected optional
                        | subjects. We keep a maximum of THREE optional subjects,
                        | matching the 3 compulsory + 3 optional result-sheet
                        | structure.
                        |--------------------------------------------------------------------------
                        */

                        $selectedOptionalMarks =
                            $marks
                                ->filter(
                                    function ($mark) use (
                                        $standardSubjectRows
                                    ) {

                                        $subjectId =
                                            (int)
                                            (
                                                $mark
                                                    ->canonical_subject_id
                                                ?? 0
                                            );

                                        $standardSubject =
                                            $standardSubjectRows->get(
                                                $subjectId
                                            );

                                        return
                                            (
                                                (int)
                                                (
                                                    $mark
                                                        ->is_optional
                                                    ?? 0
                                                )
                                            ) === 1
                                            &&
                                            $standardSubject
                                            &&
                                            (
                                                (int)
                                                (
                                                    $standardSubject
                                                        ->is_optional
                                                    ?? 0
                                                )
                                            ) === 1;
                                    }
                                )
                                ->sortBy(
                                    function ($mark) {

                                        return [
                                            (int)
                                            (
                                                $mark
                                                    ->id
                                                ?? 0
                                            ),
                                            (int)
                                            (
                                                $mark
                                                    ->canonical_subject_id
                                                ?? 0
                                            ),
                                        ];
                                    }
                                )
                                ->values();

                        /*
                        |--------------------------------------------------------------------------
                        | ONLY THREE OPTIONAL SUBJECTS ARE COUNTED
                        |--------------------------------------------------------------------------
                        */

                        $selectedOptionalMarks =
                            $selectedOptionalMarks
                                ->take(3)
                                ->values();

                        /*
                        |--------------------------------------------------------------------------
                        | STEP 13C
                        |--------------------------------------------------------------------------
                        | ADD SELECTED OPTIONAL SUBJECTS
                        |--------------------------------------------------------------------------
                        */

                        foreach (
                            $selectedOptionalMarks as
                            $selectedOptionalMark
                        ) {

                            $optionalSubjectId =
                                (int)
                                (
                                    $selectedOptionalMark
                                        ->canonical_subject_id
                                    ?? 0
                                );

                            if (
                                $optionalSubjectId <= 0
                            ) {
                                continue;
                            }

                            $optionalPoolSubject =
                                $standardSubjectRows->get(
                                    $optionalSubjectId
                                );

                            if (
                                !$optionalPoolSubject
                                ||
                                (
                                    (int)
                                    (
                                        $optionalPoolSubject
                                            ->is_optional
                                        ?? 0
                                    )
                                ) !== 1
                            ) {
                                continue;
                            }

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
                            | ONLY ADD OPTIONAL SUBJECT IF IT EXISTS
                            | IN THE EXAM CONFIGURATION
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $optionalConfig
                            ) {

                                $resultSubjects->put(
                                    $optionalSubjectId,
                                    $optionalConfig
                                );
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | STEP 13D
                        |--------------------------------------------------------------------------
                        | CALCULATE EACH SUBJECT
                        |--------------------------------------------------------------------------
                        */

                        foreach (
                            $resultSubjects as
                            $subjectId => $config
                        ) {

                            $subjectId =
                                (int) $subjectId;

                            if (
                                $subjectId <= 0
                            ) {
                                continue;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | STUDENT MARK
                            |--------------------------------------------------------------------------
                            */

                            $mark =
                                $marks->first(
                                    fn ($item) =>
                                        (int)
                                        (
                                            $item
                                                ->canonical_subject_id
                                            ?? 0
                                        )
                                        ===
                                        $subjectId
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | STANDARD SUBJECT
                            |--------------------------------------------------------------------------
                            */

                            $standardSubject =
                                $standardSubjectRows->get(
                                    $subjectId
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | IS OPTIONAL
                            |--------------------------------------------------------------------------
                            */

                            $isOptional =
                                $mark
                                &&
                                $standardSubject
                                &&
                                (
                                    (int)
                                    (
                                        $mark
                                            ->is_optional
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
                            | Optional subjects display as OPT and contribute
                            | ZERO to the student's total.
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
                            | MAX MARKS FROM STUDENT MARK RECORD
                            |--------------------------------------------------------------------------
                            */

                            $maxMarks =
                                0.0;

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
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | FALLBACK MAX MARKS FROM EXAM SUBJECT
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $maxMarks <= 0
                            ) {

                                $maxMarks =
                                    (float)
                                    (
                                        $config->max_marks
                                        ?? 0
                                    );
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | PASSING MARKS
                            |--------------------------------------------------------------------------
                            */

                            $passingMarks =
                                0.0;

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
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | FALLBACK PASSING MARKS
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $passingMarks <= 0
                            ) {

                                $configuredMax =
                                    (float)
                                    (
                                        $config->max_marks
                                        ?? 0
                                    );

                                $passingMarks =
                                    $this->calculatePassingMarks(
                                        $configuredMax > 0
                                            ? $configuredMax
                                            : $maxMarks,
                                        $standardId
                                    );
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
                            | OBTAINED MARKS
                            |--------------------------------------------------------------------------
                            */

                            if (
                                $isAbsent
                            ) {

                                $obtained =
                                    0.0;

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
                                    0.0;
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

                            } elseif (
                                $passingMarks > 0
                                &&
                                $obtained >=
                                $passingMarks
                            ) {

                                $subjectResult =
                                    'PASS';

                                $subjectGrade =
                                    $this->calculateGrade(
                                        $obtained,
                                        $maxMarks,
                                        'PASS'
                                    );

                            } else {

                                $subjectResult =
                                    'FAIL';

                                $subjectGrade =
                                    'F';

                                $failedSubjects++;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | SAVE SUBJECT DATA IN MEMORY
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
                        | STEP 13E
                        |--------------------------------------------------------------------------
                        | OVERALL PERCENTAGE
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
                                : 0.0;

                        /*
                        |--------------------------------------------------------------------------
                        | GET PASSING PERCENTAGE
                        |--------------------------------------------------------------------------
                        */

                        $passingPercentage =
                            $this->getPassingPercentage(
                                $standardId
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | OVERALL RESULT
                        |--------------------------------------------------------------------------
                        |
                        | Student must:
                        |
                        | 1. Pass every counted subject.
                        | 2. Meet the standard's overall passing percentage.
                        |--------------------------------------------------------------------------
                        */

                        $result =
                            (
                                $failedSubjects === 0
                                &&
                                $totalMax > 0
                                &&
                                $percentage >=
                                    $passingPercentage
                            )
                                ? 'PASS'
                                : 'FAIL';

                        /*
                        |--------------------------------------------------------------------------
                        | OVERALL GRADE
                        |--------------------------------------------------------------------------
                        */

                        $grade =
                            $this->calculateOverallGrade(
                                $percentage,
                                $result
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
                            $resultSubjects as
                            $subjectData
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
                            | OPTIONAL FLAG
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

                    $rank = 0;

                    $position = 0;

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
                                $studentResult->percentage
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
                (float) $obtainedMarks
                /
                (float) $maxMarks
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

        if (
            $percentage >= 33
        ) {
            return 'D';
        }

        if (
            $percentage >= 21
        ) {
            return 'E1';
        }

        if (
            $percentage >= 1
        ) {
            return 'E2';
        }

        return 'F';
    }


    /*
    |--------------------------------------------------------------------------
    | OVERALL GRADE
    |--------------------------------------------------------------------------
    */

    private function calculateOverallGrade(
        $percentage,
        $result = 'PASS'
    ) {

        if (
            $result === 'FAIL'
        ) {
            return 'F';
        }

        $percentage =
            (float) $percentage;

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

        if (
            $percentage >= 21
        ) {
            return 'E1';
        }

        if (
            $percentage >= 1
        ) {
            return 'E2';
        }

        return 'F';
    }
}
