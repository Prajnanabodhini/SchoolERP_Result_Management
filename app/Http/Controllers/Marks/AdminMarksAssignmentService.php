<?php

namespace App\Http\Controllers\Marks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\TeacherClassAllocation;
use App\Models\ExamMaster;

class AdminMarksAssignmentService
{
    /*
    |--------------------------------------------------------------------------
    | PREPARE EXAMS
    |--------------------------------------------------------------------------
    |
    | Adds:
    |
    | resolved_standard_id
    | resolved_standard_name
    | display_exam_name
    |
    | Also supports older Exam Master records where the standard was not
    | properly attached and has to be identified from the exam name.
    |--------------------------------------------------------------------------
    */

    public function prepareExams(
        $exams,
        $standards
    ) {
        foreach ($exams as $exam) {

            /*
            |--------------------------------------------------------------------------
            | DIRECT STANDARD
            |--------------------------------------------------------------------------
            */

            $standard = null;

            if (
                !empty(
                    $exam->standard_id
                )
            ) {

                $standard =
                    $standards->firstWhere(
                        'id',
                        (int) $exam->standard_id
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | EXAM NAME FALLBACK
            |--------------------------------------------------------------------------
            */

            if (!$standard) {

                $normalizedExamName =
                    $this->normalizeText(
                        $exam->exam_name
                    );

                /*
                |--------------------------------------------------------------------------
                | Longest standard name first.
                |
                | This prevents:
                |
                | FIRST
                | FIRST SCIENCE
                |
                | from matching incorrectly.
                |--------------------------------------------------------------------------
                */

                $sortedStandards =
                    $standards->sortByDesc(
                        function ($item) {

                            return strlen(
                                $this->normalizeText(
                                    $item->standard_name
                                )
                            );
                        }
                    );

                foreach (
                    $sortedStandards as $candidate
                ) {

                    $normalizedStandardName =
                        $this->normalizeText(
                            $candidate->standard_name
                        );

                    if (
                        $normalizedStandardName === ''
                    ) {
                        continue;
                    }

                    if (
                        str_ends_with(
                            $normalizedExamName,
                            $normalizedStandardName
                        )
                        ||
                        str_contains(
                            $normalizedExamName,
                            $normalizedStandardName
                        )
                    ) {

                        $standard =
                            $candidate;

                        break;
                    }
                }
            }


            /*
            |--------------------------------------------------------------------------
            | RESOLVED VALUES
            |--------------------------------------------------------------------------
            */

            $exam->resolved_standard_id =
                $standard
                    ? (int) $standard->id
                    : null;

            $exam->resolved_standard_name =
                $standard
                    ? $standard->standard_name
                    : null;


            /*
            |--------------------------------------------------------------------------
            | DISPLAY NAME
            |--------------------------------------------------------------------------
            */

            $examName =
                trim(
                    (string)
                    $exam->exam_name
                );

            $standardName =
                trim(
                    (string)
                    (
                        $exam->resolved_standard_name
                        ?? ''
                    )
                );


            if (
                $standardName !== ''
                &&
                !str_ends_with(
                    $this->normalizeText(
                        $examName
                    ),
                    $this->normalizeText(
                        $standardName
                    )
                )
            ) {

                $exam->display_exam_name =
                    $examName
                    . ' - '
                    . $standardName;

            } else {

                $exam->display_exam_name =
                    $examName;
            }
        }

        return $exams;
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE TEXT
    |--------------------------------------------------------------------------
    */

    private function normalizeText(
        $value
    ): string {

        return preg_replace(
            '/[^A-Z0-9]/',
            '',
            strtoupper(
                trim(
                    (string)
                    $value
                )
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET ASSIGNMENTS
    |--------------------------------------------------------------------------
    |
    | PERFORMANCE IMPORTANT
    |--------------------------------------------------------------------------
    |
    | The old controller did a broad historical StudentMark scan.
    |
    | That is removed from the initial Admin Marks page.
    |
    | Current assignment list comes from:
    |
    | teacher_marks_status
    |        ↓
    | teacher_subject_allocations
    |        ↓
    | teacher_class_allocations
    |
    | student_marks are NOT queried here.
    |
    |--------------------------------------------------------------------------
    */

    public function getAssignments(
        $academicYearId = null,
        $examId = null
    ) {

        /*
        |--------------------------------------------------------------------------
        | DO NOT LOAD EVERYTHING WHEN EXAM IS NOT SELECTED
        |--------------------------------------------------------------------------
        */

        if (
            $examId === null
            ||
            $examId === ''
        ) {

            return collect();
        }


        $examId =
            (int) $examId;


        /*
        |--------------------------------------------------------------------------
        | LOAD CURRENT MARK STATUS
        |--------------------------------------------------------------------------
        */

        $statusQuery =
            TeacherMarksStatus::query()
                ->where(
                    'exam_master_id',
                    $examId
                );


        if (
            $academicYearId !== null
            &&
            $academicYearId !== ''
        ) {

            $statusQuery->where(
                'academic_year_id',
                (int) $academicYearId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SELECT ONLY REQUIRED COLUMNS
        |--------------------------------------------------------------------------
        */

        $statuses =
            $statusQuery
                ->orderByDesc(
                    'id'
                )
                ->get([
                    'id',
                    'academic_year_id',
                    'exam_master_id',
                    'teacher_subject_allocation_id',
                    'teacher_id',
                    'standard_id',
                    'division_id',
                    'subject_id',
                    'status',
                ]);


        if (
            $statuses->isEmpty()
        ) {

            return collect();
        }


        /*
        |--------------------------------------------------------------------------
        | BATCH LOAD TSA
        |--------------------------------------------------------------------------
        |
        | Prevents N+1 queries.
        |--------------------------------------------------------------------------
        */

        $tsaIds =
            $statuses
                ->pluck(
                    'teacher_subject_allocation_id'
                )
                ->filter()
                ->unique()
                ->values();


        if (
            $tsaIds->isEmpty()
        ) {

            return collect();
        }


        $tsas =
            TeacherSubjectAllocation::query()
                ->with([
                    'allocation.teacher',
                    'allocation.academicYear',
                    'allocation.section',
                    'allocation.standard',
                    'allocation.division',
                ])
                ->whereIn(
                    'id',
                    $tsaIds
                )
                ->get()
                ->keyBy(
                    'id'
                );


        if (
            $tsas->isEmpty()
        ) {

            return collect();
        }


        /*
        |--------------------------------------------------------------------------
        | BATCH LOAD STANDARD-WISE SUBJECT MAPPINGS
        |--------------------------------------------------------------------------
        */

        $standardIds =
            $statuses
                ->pluck(
                    'standard_id'
                )
                ->filter()
                ->unique()
                ->map(
                    fn ($id) => (int) $id
                )
                ->values();


        $subjectMappings =
            collect();


        if (
            $standardIds->isNotEmpty()
        ) {

            $subjectMappings =
                DB::table(
                    'standard_wise_subjects as sws'
                )
                ->join(
                    'subjects as s',
                    's.id',
                    '=',
                    'sws.subject_id'
                )
                ->whereIn(
                    'sws.standard_id',
                    $standardIds
                )
                ->where(
                    'sws.is_active',
                    1
                )
                ->where(
                    's.is_active',
                    1
                )
                ->select([
                    'sws.id as mapping_id',
                    'sws.standard_id',
                    'sws.subject_id',
                    's.id as actual_subject_id',
                    's.subject_name',
                    's.subject_code',
                    's.short_name',
                ])
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD LOOKUP MAPS
        |--------------------------------------------------------------------------
        |
        | We use separate maps for:
        |
        | CURRENT:
        |     subjects.id
        |
        | LEGACY:
        |     standard_wise_subjects.id
        |
        | This is important because two IDs may have the same numeric value.
        |--------------------------------------------------------------------------
        */

        $currentSubjectMap =
            [];

        $legacySubjectMap =
            [];


        foreach (
            $subjectMappings as $mapping
        ) {

            $currentSubjectMap[
                (int) $mapping->standard_id
                . ':'
                . (int) $mapping->subject_id
            ] =
                $mapping;


            $legacySubjectMap[
                (int) $mapping->standard_id
                . ':'
                . (int) $mapping->mapping_id
            ] =
                $mapping;
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        $assignments =
            collect();


        foreach (
            $statuses as $status
        ) {

            $tsa =
                $tsas->get(
                    (int)
                    $status->teacher_subject_allocation_id
                );


            if (
                !$tsa
                ||
                !$tsa->allocation
            ) {
                continue;
            }


            $standardId =
                (int)
                (
                    $status->standard_id
                    ??
                    $tsa->allocation->standard_id
                );


            /*
            |--------------------------------------------------------------------------
            | SUBJECT RESOLUTION
            |--------------------------------------------------------------------------
            */

            $mapping =
                $currentSubjectMap[
                    $standardId
                    . ':'
                    . (int) $status->subject_id
                ]
                ??
                $legacySubjectMap[
                    $standardId
                    . ':'
                    . (int) $status->subject_id
                ]
                ??
                $currentSubjectMap[
                    $standardId
                    . ':'
                    . (int) $tsa->subject_id
                ]
                ??
                $legacySubjectMap[
                    $standardId
                    . ':'
                    . (int) $tsa->subject_id
                ]
                ??
                null;


            /*
            |--------------------------------------------------------------------------
            | FALLBACK SUBJECT MASTER
            |--------------------------------------------------------------------------
            |
            | Normally mapping should exist.
            |
            | We intentionally do a single fallback query only when required.
            |--------------------------------------------------------------------------
            */

            if (!$mapping) {

                $actualSubjectId =
                    $this->resolveActualSubjectIdFallback(
                        $status->subject_id,
                        $tsa->subject_id,
                        $standardId
                    );


                if (
                    !$actualSubjectId
                ) {
                    continue;
                }


                $subject =
                    DB::table(
                        'subjects'
                    )
                    ->where(
                        'id',
                        $actualSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->select([
                        'id',
                        'subject_name',
                        'subject_code',
                        'short_name',
                    ])
                    ->first();


                if (!$subject) {
                    continue;
                }


                $subjectObject =
                    (object) [
                        'id' =>
                            (int) $subject->id,

                        'subject_name' =>
                            $subject->subject_name,

                        'subject_code' =>
                            $subject->subject_code
                            ?? '',

                        'short_name' =>
                            $subject->short_name
                            ?? '',
                    ];

            } else {

                $subjectObject =
                    (object) [
                        'id' =>
                            (int)
                            $mapping->actual_subject_id,

                        'subject_name' =>
                            $mapping->subject_name
                            ?: '-',

                        'subject_code' =>
                            $mapping->subject_code
                            ?? '',

                        'short_name' =>
                            $mapping->short_name
                            ?? '',
                    ];
            }


            /*
            |--------------------------------------------------------------------------
            | BUILD ASSIGNMENT OBJECT
            |--------------------------------------------------------------------------
            */

            $assignment =
                new TeacherSubjectAllocation();


            $assignment->id =
                (int) $tsa->id;


            $assignment->teacher_class_allocation_id =
                (int)
                $tsa->teacher_class_allocation_id;


            $assignment->exam_master_id =
                (int)
                $tsa->exam_master_id;


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT:
            |
            | Always expose actual Subject Master ID to Blade.
            |--------------------------------------------------------------------------
            */

            $assignment->subject_id =
                (int)
                $subjectObject->id;


            /*
            |--------------------------------------------------------------------------
            | RELATIONSHIPS
            |--------------------------------------------------------------------------
            */

            $assignment->setRelation(
                'allocation',
                $tsa->allocation
            );


            $assignment->setRelation(
                'subject',
                $subjectObject
            );


            /*
            |--------------------------------------------------------------------------
            | EXAM RELATIONSHIP
            |--------------------------------------------------------------------------
            */

            $assignment->setRelation(
                'exam',
                $this->getExam(
                    $examId
                )
            );


            /*
            |--------------------------------------------------------------------------
            | RESOLVED VALUES
            |--------------------------------------------------------------------------
            */

            $assignment->resolved_subject_id =
                (int)
                $subjectObject->id;


            $assignment->resolved_academic_year_id =
                (int)
                $tsa->allocation
                    ->academic_year_id;


            $assignment->resolved_class_allocation_id =
                (int)
                $tsa->teacher_class_allocation_id;


            $assignment->resolved_exam_master_id =
                (int)
                $tsa->exam_master_id;


            $assignment->resolved_standard_id =
                (int)
                $tsa->allocation
                    ->standard_id;


            $assignment->resolved_division_id =
                (int)
                $tsa->allocation
                    ->division_id;


            $assignment->resolved_teacher_id =
                $tsa->allocation->user_id
                    ? (int)
                    $tsa->allocation->user_id
                    : null;


            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $assignment->resolved_status =
                strtoupper(
                    trim(
                        (string)
                        (
                            $status->status
                            ??
                            'PENDING'
                        )
                    )
                );


            $assignment->resolved_status_id =
                $status->id;


            $assignment->resolved_tms_subject_id =
                $status->subject_id;


            $assignment->is_historical =
                false;


            /*
            |--------------------------------------------------------------------------
            | UNIQUE SELECTION KEY
            |--------------------------------------------------------------------------
            */

            $assignment->resolved_selection_key =
                $tsa->id
                . '|'
                . $subjectObject->id;


            $assignments->push(
                $assignment
            );
        }


        /*
        |--------------------------------------------------------------------------
        | REMOVE DUPLICATES
        |--------------------------------------------------------------------------
        */

        return $assignments
            ->unique(
                'resolved_selection_key'
            )
            ->sortBy([
                [
                    'resolved_standard_id',
                    'asc',
                ],
                [
                    'resolved_division_id',
                    'asc',
                ],
                [
                    'subject.subject_name',
                    'asc',
                ],
            ])
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | GET EXAM
    |--------------------------------------------------------------------------
    |
    | Small helper.
    |--------------------------------------------------------------------------
    */

    private function getExam(
        $examId
    ) {

        static $examCache = [];


        $examId =
            (int) $examId;


        if (
            isset(
                $examCache[$examId]
            )
        ) {

            return $examCache[$examId];
        }


        $exam =
            ExamMaster::find(
                $examId
            );


        $examCache[$examId] =
            $exam;


        return $exam;
    }


    /*
    |--------------------------------------------------------------------------
    | FALLBACK SUBJECT RESOLUTION
    |--------------------------------------------------------------------------
    |--------------------------------------------------------------------------
    */

    private function resolveActualSubjectIdFallback(
        $statusSubjectId,
        $tsaSubjectId,
        $standardId
    ) {

        $candidateIds =
            collect([
                $statusSubjectId,
                $tsaSubjectId,
            ])
            ->filter(
                fn ($id) =>
                    (int) $id > 0
            )
            ->map(
                fn ($id) =>
                    (int) $id
            )
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | CURRENT SUBJECT FORMAT FIRST
        |--------------------------------------------------------------------------
        */

        foreach (
            $candidateIds as $candidateId
        ) {

            $current =
                DB::table(
                    'standard_wise_subjects'
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'subject_id',
                    $candidateId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();


            if ($current) {

                return (int)
                    $current->subject_id;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LEGACY SWS FORMAT
        |--------------------------------------------------------------------------
        */

        foreach (
            $candidateIds as $candidateId
        ) {

            $legacy =
                DB::table(
                    'standard_wise_subjects'
                )
                ->where(
                    'id',
                    $candidateId
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


            if ($legacy) {

                return (int)
                    $legacy->subject_id;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | GET SUBJECTS
    |--------------------------------------------------------------------------
    |
    | This method is used by AJAX after Administrator selects:
    |
    | Teacher Class Allocation
    | +
    | Exam
    |
    | Unlike getAssignments(), this method may inspect historical marks,
    | because it is already a targeted request.
    |--------------------------------------------------------------------------
    */

    public function getSubjects(
        Request $request,
        $subjectService
    ) {

        $request->validate([
            'allocation_id' =>
                'required|exists:teacher_class_allocations,id',

            'exam_master_id' =>
                'required|exists:exam_masters,id',
        ]);


        $allocationId =
            (int)
            $request->allocation_id;


        $examId =
            (int)
            $request->exam_master_id;


        /*
        |--------------------------------------------------------------------------
        | LOAD CLASS ALLOCATION
        |--------------------------------------------------------------------------
        */

        $allocation =
            TeacherClassAllocation::findOrFail(
                $allocationId
            );


        $standardId =
            (int)
            $allocation->standard_id;


        $divisionId =
            (int)
            $allocation->division_id;


        $academicYearId =
            (int)
            $allocation->academic_year_id;


        $teacherId =
            (int)
            $allocation->user_id;


        $subjects =
            collect();


        /*
        |--------------------------------------------------------------------------
        | CURRENT STATUS SUBJECTS
        |--------------------------------------------------------------------------
        */

        $statuses =
            TeacherMarksStatus::query()
                ->where(
                    'exam_master_id',
                    $examId
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
                    'academic_year_id',
                    $academicYearId
                )
                ->where(
                    'teacher_id',
                    $teacherId
                )
                ->orderByDesc(
                    'id'
                )
                ->get([
                    'id',
                    'teacher_subject_allocation_id',
                    'subject_id',
                    'status',
                    'exam_master_id',
                    'standard_id',
                    'division_id',
                    'academic_year_id',
                    'teacher_id',
                ]);


        /*
        |--------------------------------------------------------------------------
        | BATCH LOAD TSA FOR CURRENT SUBJECTS
        |--------------------------------------------------------------------------
        */

        $tsaIds =
            $statuses
                ->pluck(
                    'teacher_subject_allocation_id'
                )
                ->filter()
                ->unique()
                ->values();


        $tsaMap =
            collect();


        if (
            $tsaIds->isNotEmpty()
        ) {

            $tsaMap =
                TeacherSubjectAllocation::query()
                    ->whereIn(
                        'id',
                        $tsaIds
                    )
                    ->get([
                        'id',
                        'subject_id',
                        'teacher_class_allocation_id',
                        'exam_master_id',
                    ])
                    ->keyBy(
                        'id'
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | CURRENT SUBJECTS
        |--------------------------------------------------------------------------
        */

        foreach (
            $statuses as $status
        ) {

            $tsa =
                $tsaMap->get(
                    $status->teacher_subject_allocation_id
                );


            $subject =
                $subjectService->getActualSubject(
                    $status->subject_id,
                    $standardId
                );


            if (
                !$subject
                &&
                $tsa
            ) {

                $subject =
                    $subjectService->getActualSubject(
                        $tsa->subject_id,
                        $standardId
                    );
            }


            if (!$subject) {
                continue;
            }


            $this->pushSubjectOption(
                $subjects,
                $status->teacher_subject_allocation_id,
                $subject,
                false
            );
        }


        /*
        |--------------------------------------------------------------------------
        | HISTORICAL SUBJECTS
        |--------------------------------------------------------------------------
        |
        | This query is now restricted by:
        |
        | exam
        | standard
        | division
        |
        | It does NOT scan all StudentMarks.
        |--------------------------------------------------------------------------
        */

        $historicalMarks =
            DB::table(
                'student_marks'
            )
            ->where(
                'exam_master_id',
                $examId
            )
            ->where(
                'standard_id',
                $standardId
            )
            ->where(
                'division_id',
                $divisionId
            )
            ->whereNotNull(
                'teacher_subject_allocation_id'
            )
            ->whereNotNull(
                'subject_id'
            )
            ->select([
                'teacher_subject_allocation_id',
                'subject_id',
            ])
            ->distinct()
            ->get();


        foreach (
            $historicalMarks as $markInfo
        ) {

            $subject =
                $subjectService->getActualSubject(
                    $markInfo->subject_id,
                    $standardId
                );


            if (!$subject) {
                continue;
            }


            $this->pushSubjectOption(
                $subjects,
                $markInfo->teacher_subject_allocation_id,
                $subject,
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SORT
        |--------------------------------------------------------------------------
        */

        $subjects =
            $subjects
                ->sortBy(
                    function ($item) {

                        return strtoupper(
                            (string)
                            $item->subject_name
                        );
                    }
                )
                ->values();


        return response()->json(
            $subjects
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PUSH SUBJECT OPTION
    |--------------------------------------------------------------------------
    */

    private function pushSubjectOption(
        &$subjects,
        $tsaId,
        $subject,
        $historical
    ) {

        if (!$subject) {
            return;
        }


        $selectionKey =
            (int) $tsaId
            . '|'
            . (int) $subject->id;


        $exists =
            $subjects->contains(
                function ($item) use (
                    $selectionKey
                ) {

                    return
                        $item->selection_key
                        ===
                        $selectionKey;
                }
            );


        if ($exists) {
            return;
        }


        $subjects->push(
            (object) [

                'teacher_subject_allocation_id' =>
                    (int) $tsaId,

                'subject_id' =>
                    (int) $subject->id,

                'subject_name' =>
                    $subject->subject_name
                    ??
                    '-',

                'subject_code' =>
                    $subject->subject_code
                    ??
                    '',

                'short_name' =>
                    $subject->short_name
                    ??
                    '',

                'selection_key' =>
                    $selectionKey,

                'is_historical' =>
                    (bool) $historical,
            ]
        );
    }
}