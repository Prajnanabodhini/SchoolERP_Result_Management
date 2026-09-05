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
                | LONGEST STANDARD NAME FIRST
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
                    (string) (
                        $exam->resolved_standard_name
                        ??
                        ''
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
    | EFFECTIVE STATUS
    |--------------------------------------------------------------------------
    |
    | SAME LOGIC AS EXAM PROGRESS DASHBOARD
    |--------------------------------------------------------------------------
    |
    | If matching student_marks exist for:
    |
    | academic_year
    | section
    | standard
    | division
    | exam
    | subject
    |
    | then effective status is COMPLETED.
    |
    | Otherwise use teacher_marks_status.status.
    |
    | IMPORTANT:
    | teacher_marks_status DOES NOT contain section_id.
    | Section comes from teacher_class_allocations.
    |
    |--------------------------------------------------------------------------
    */

    private function getEffectiveStatus(
        $status,
        $tsa,
        $actualSubjectId
    ): string {

        $storedStatus =
            strtoupper(
                trim(
                    (string) (
                        $status->status
                        ??
                        ''
                    )
                )
            );


        if (
            !$tsa
            ||
            !$tsa->allocation
        ) {

            return $storedStatus !== ''
                ? $storedStatus
                : 'PENDING';
        }


        $allocation =
            $tsa->allocation;


        /*
        |--------------------------------------------------------------------------
        | CLASS / EXAM VALUES
        |--------------------------------------------------------------------------
        */

        $academicYearId =
            (int) (
                $status->academic_year_id
                ??
                $allocation->academic_year_id
                ??
                0
            );


        $sectionId =
            (int) (
                $allocation->section_id
                ??
                0
            );


        $standardId =
            (int) (
                $status->standard_id
                ??
                $allocation->standard_id
                ??
                0
            );


        $divisionId =
            (int) (
                $status->division_id
                ??
                $allocation->division_id
                ??
                0
            );


        $examId =
            (int) (
                $status->exam_master_id
                ??
                $tsa->exam_master_id
                ??
                0
            );


        $actualSubjectId =
            (int) $actualSubjectId;


        /*
        |--------------------------------------------------------------------------
        | VALIDATE CONTEXT
        |--------------------------------------------------------------------------
        */

        if (
            $academicYearId <= 0
            ||
            $sectionId <= 0
            ||
            $standardId <= 0
            ||
            $divisionId <= 0
            ||
            $examId <= 0
            ||
            $actualSubjectId <= 0
        ) {

            return $storedStatus !== ''
                ? $storedStatus
                : 'PENDING';
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT IDS
        |--------------------------------------------------------------------------
        |
        | Current:
        |     subjects.id
        |
        | Legacy:
        |     standard_wise_subjects.id
        |
        |--------------------------------------------------------------------------
        */

        $possibleSubjectIds =
            collect([
                $actualSubjectId,
            ])
            ->merge(
                DB::table(
                    'standard_wise_subjects'
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'subject_id',
                    $actualSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->pluck(
                    'id'
                )
            )
            ->map(
                fn ($id) => (int) $id
            )
            ->filter(
                fn ($id) => $id > 0
            )
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | EFFECTIVE COMPLETION
        |--------------------------------------------------------------------------
        |
        | Same matching rule used by ExamProgressController.
        |
        |--------------------------------------------------------------------------
        */

        $marksExist =
            DB::table(
                'student_marks'
            )
            ->where(
                'academic_year_id',
                $academicYearId
            )
            ->where(
                'section_id',
                $sectionId
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
                'exam_master_id',
                $examId
            )
            ->whereIn(
                'subject_id',
                $possibleSubjectIds
            )
            ->exists();


        if ($marksExist) {

            return 'COMPLETED';
        }


        /*
        |--------------------------------------------------------------------------
        | STORED STATUS FALLBACK
        |--------------------------------------------------------------------------
        */

        return $storedStatus !== ''
            ? $storedStatus
            : 'PENDING';
    }


    /*
    |--------------------------------------------------------------------------
    | GET ASSIGNMENTS
    |--------------------------------------------------------------------------
    */

    public function getAssignments(
        $academicYearId = null,
        $examId = null
    ) {

        /*
        |--------------------------------------------------------------------------
        | NO EXAM = NO ASSIGNMENTS
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
        | LOAD TEACHER MARK STATUS
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
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | NO section_id HERE.
        |
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
        | STANDARD IDS
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


        /*
        |--------------------------------------------------------------------------
        | SUBJECT MAPPINGS
        |--------------------------------------------------------------------------
        */

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
        | LOOKUP MAPS
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
                (int) (
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
            | BUILD ASSIGNMENT
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
            | ACTUAL SUBJECT MASTER ID
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


            $assignment->resolved_section_id =
                (int)
                $tsa->allocation
                    ->section_id;


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
            | EFFECTIVE STATUS
            |--------------------------------------------------------------------------
            */

            $assignment->resolved_status =
                $this->getEffectiveStatus(
                    $status,
                    $tsa,
                    $subjectObject->id
                );


            $assignment->resolved_status_id =
                $status->id;


            $assignment->resolved_tms_subject_id =
                $status->subject_id;


            $assignment->is_historical =
                false;


            /*
            |--------------------------------------------------------------------------
            | UNIQUE KEY
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
        | CURRENT FORMAT
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
        | LEGACY FORMAT
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
        | CLASS ALLOCATION
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
        | TSA MAP
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