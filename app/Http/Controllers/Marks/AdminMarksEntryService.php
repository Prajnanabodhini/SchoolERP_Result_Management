<?php

namespace App\Http\Controllers\Marks;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Subject;
use App\Models\ExamMaster;
use App\Models\StudentMark;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\TeacherClassAllocation;
use App\Models\ExamMasterSubject;
use App\Models\MarkAuditLog;

use App\Helpers\StudentHelper;

class AdminMarksEntryService
{
    /*
    |--------------------------------------------------------------------------
    | DEFAULT DATA
    |--------------------------------------------------------------------------
    */

    private function emptySelectedData(): array
    {
        return [

            'students' =>
                collect(),

            'existingMarks' =>
                collect(),

            'exam' =>
                null,

            'teacherSubjectAllocation' =>
                null,

            'selectedClassAllocation' =>
                null,

            'subjectConfig' =>
                null,

            'showTheory' =>
                false,

            'showOral' =>
                false,

            'showPractical' =>
                false,

            'theoryMaxMarks' =>
                0,

            'theoryPassingMarks' =>
                0,

            'oralMaxMarks' =>
                0,

            'oralPassingMarks' =>
                0,

            'practicalMaxMarks' =>
                0,

            'practicalPassingMarks' =>
                0,

            'marksLocked' =>
                false,

            'message' =>
                '',

            'error' =>
                '',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD SELECTED DATA
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | This method does nothing expensive until a teaching assignment is
    | actually selected.
    |
    |--------------------------------------------------------------------------
    */

    public function loadSelectedData(
        Request $request,
        $exams,
        $subjectService
    ) {

        /*
        |--------------------------------------------------------------------------
        | INITIAL DATA
        |--------------------------------------------------------------------------
        */

        $data =
            $this->emptySelectedData();


        /*
        |--------------------------------------------------------------------------
        | SELECTION
        |--------------------------------------------------------------------------
        */

        $selectionValue =
            $request->input(
                'teacher_subject_allocation_id'
            );


        if (
            !$selectionValue
        ) {

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | PARSE:
        |
        | TSA|SUBJECT
        |--------------------------------------------------------------------------
        */

        [
            $tsaId,
            $selectedSubjectId
        ] =
            $this->parseSelection(
                $request
            );


        if (
            !$tsaId
        ) {

            $data['error'] =
                'Invalid teaching assignment.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | REQUEST EXAM
        |--------------------------------------------------------------------------
        */

        $requestedExamId =
            $request->input(
                'exam_master_id'
            );


        /*
        |--------------------------------------------------------------------------
        | LOAD TMS STATUS
        |--------------------------------------------------------------------------
        */

        $status =
            TeacherMarksStatus::query()
                ->where(
                    'teacher_subject_allocation_id',
                    $tsaId
                )
                ->when(
                    $requestedExamId !== null
                    &&
                    $requestedExamId !== '',
                    function ($query) use (
                        $requestedExamId
                    ) {

                        $query->where(
                            'exam_master_id',
                            (int)
                            $requestedExamId
                        );
                    }
                )
                ->orderByDesc(
                    'id'
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | LOAD REAL TSA
        |--------------------------------------------------------------------------
        |
        | Only one TSA is loaded.
        |--------------------------------------------------------------------------
        */

        $tsa =
            TeacherSubjectAllocation::query()
                ->with([
                    'allocation.teacher',
                    'allocation.academicYear',
                    'allocation.section',
                    'allocation.standard',
                    'allocation.division',
                ])
                ->find(
                    $tsaId
                );


        if (
            !$tsa
            ||
            !$tsa->allocation
        ) {

            $data['error'] =
                'Teacher class allocation not found.';

            return $data;
        }


        $allocation =
            $tsa->allocation;


        /*
        |--------------------------------------------------------------------------
        | EXAM ID
        |--------------------------------------------------------------------------
        */

        $examId =
            (int)
            (
                $requestedExamId
                ??
                $tsa->exam_master_id
            );


        if (
            !$examId
        ) {

            $data['error'] =
                'Exam linked to the selected teaching assignment was not found.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        $exam =
            $exams->firstWhere(
                'id',
                $examId
            );


        if (
            !$exam
        ) {

            $exam =
                ExamMaster::find(
                    $examId
                );
        }


        if (
            !$exam
        ) {

            $data['error'] =
                'Exam linked to the selected teaching assignment was not found.';

            return $data;
        }


        $data['exam'] =
            $exam;


        /*
        |--------------------------------------------------------------------------
        | STANDARD / DIVISION / ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        $standardId =
            (int)
            (
                $status?->standard_id
                ??
                $allocation->standard_id
            );


        $divisionId =
            (int)
            (
                $status?->division_id
                ??
                $allocation->division_id
            );


        $academicYearId =
            (int)
            (
                $status?->academic_year_id
                ??
                $allocation->academic_year_id
                ??
                $exam->academic_year_id
                ??
                0
            );


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR VALIDATION
        |--------------------------------------------------------------------------
        */

        $requestedAcademicYearId =
            $request->input(
                'academic_year_id'
            );


        if (
            $requestedAcademicYearId !== null
            &&
            $requestedAcademicYearId !== ''
            &&
            $academicYearId > 0
            &&
            (int)
            $requestedAcademicYearId !==
            $academicYearId
        ) {

            $data['error'] =
                'Selected teaching assignment does not belong to the selected academic year.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT RESOLUTION
        |--------------------------------------------------------------------------
        |
        | Priority:
        |
        | 1. Explicit subject from selection key
        | 2. Student marks historical subject
        | 3. TMS subject
        | 4. TSA subject
        |
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            $this->resolveSelectedSubject(
                $selectedSubjectId,
                $tsa,
                $status,
                $examId,
                $standardId,
                $divisionId,
                $subjectService
            );


        if (
            !$actualSubjectId
        ) {

            $data['error'] =
                'Unable to resolve the actual Subject Master ID.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT MASTER
        |--------------------------------------------------------------------------
        */

        $subject =
            Subject::query()
                ->where(
                    'id',
                    $actualSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first([
                    'id',
                    'subject_name',
                    'subject_code',
                    'short_name',
                ]);


        if (
            !$subject
        ) {

            $data['error'] =
                'The selected subject was not found.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD SUBJECT VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            !$subjectService->isMappedToStandard(
                $actualSubjectId,
                $standardId
            )
        ) {

            $data['error'] =
                'The selected subject is not mapped to the selected Standard.';

            return $data;
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD DISPLAY ASSIGNMENT
        |--------------------------------------------------------------------------
        */

        $displayAssignment =
            $this->buildDisplayAssignment(
                $tsa,
                $exam,
                $subject,
                $allocation,
                $status,
                $selectedSubjectId
            );


        $data[
            'teacherSubjectAllocation'
        ] =
            $displayAssignment;


        $data[
            'selectedClassAllocation'
        ] =
            $allocation;


        /*
        |--------------------------------------------------------------------------
        | EXAM SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $subjectConfig =
            $subjectService->getSubjectConfig(
                $examId,
                $standardId,
                $actualSubjectId
            );


        if (
            !$subjectConfig
        ) {

            $data['error'] =
                'Marks configuration was not found for '
                . $subject->subject_name
                . ' in '
                . $exam->exam_name
                . '.';

            return $data;
        }


        $data[
            'subjectConfig'
        ] =
            $subjectConfig;


        /*
        |--------------------------------------------------------------------------
        | COMPONENT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $component =
            $this->getComponentConfig(
                $exam,
                $subjectConfig
            );


        $data =
            array_merge(
                $data,
                $component
            );


        /*
        |--------------------------------------------------------------------------
        | LOAD STUDENTS
        |--------------------------------------------------------------------------
        |
        | This expensive ERP query runs ONLY after the assignment is selected.
        |--------------------------------------------------------------------------
        */

        try {

            if (
                $academicYearId > 0
                &&
                $standardId > 0
                &&
                $divisionId > 0
            ) {

                $data['students'] =
                    $this->loadStudents(
                        $academicYearId,
                        $standardId,
                        $divisionId
                    );

            }

        } catch (
            \Throwable $e
        ) {

            report($e);

            $data['students'] =
                collect();

            $data['error'] =
                'Old ERP Error: '
                . $e->getMessage();
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD EXISTING MARKS
        |--------------------------------------------------------------------------
        |
        | CRITICAL FIX:
        |
        | Do not use the current TSA as the identity.
        |
        | Historical data can have a different TSA.
        |
        | Match by:
        |
        |     exam
        |     student
        |     actual subject
        |     standard
        |     division
        |
        |--------------------------------------------------------------------------
        */

        $data[
            'existingMarks'
        ] =
            $this->loadExistingMarks(
                $examId,
                $actualSubjectId,
                $standardId,
                $divisionId,
                $subjectService
            );


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $statusName =
            strtoupper(
                trim(
                    (string)
                    (
                        $status?->status
                        ??
                        'PENDING'
                    )
                )
            );


        $data[
            'marksLocked'
        ] =
            $statusName === 'COMPLETED';


        /*
        |--------------------------------------------------------------------------
        | MESSAGE
        |--------------------------------------------------------------------------
        */

        if (
            $data['marksLocked']
        ) {

            $data['message'] =
                'Status: COMPLETED. Administrator can modify these marks.';

        } elseif (
            $displayAssignment->is_historical
            ?? false
        ) {

            $data['message'] =
                'Historical marks recovered from Student Marks. Administrator can modify these marks.';

        } else {

            $data['message'] =
                'Status: '
                . $statusName
                . '. Administrator can modify these marks.';
        }


        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | PARSE SELECTION
    |--------------------------------------------------------------------------
    */

    private function parseSelection(
        Request $request
    ): array {

        $value =
            $request->input(
                'teacher_subject_allocation_id'
            );


        if (
            $value === null
            ||
            $value === ''
        ) {

            return [
                null,
                null,
            ];
        }


        $tsaId =
            null;

        $subjectId =
            null;


        if (
            str_contains(
                (string) $value,
                '|'
            )
        ) {

            $parts =
                explode(
                    '|',
                    (string) $value
                );


            $tsaId =
                isset(
                    $parts[0]
                )
                    ? (int)
                        $parts[0]
                    : null;


            $subjectId =
                isset(
                    $parts[1]
                )
                    &&
                    $parts[1] !== ''
                    ? (int)
                        $parts[1]
                    : null;

        } else {

            $tsaId =
                (int) $value;


            if (
                $request->filled(
                    'subject_id'
                )
            ) {

                $subjectId =
                    (int)
                    $request->input(
                        'subject_id'
                    );
            }
        }


        return [
            $tsaId,
            $subjectId,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE SELECTED SUBJECT
    |--------------------------------------------------------------------------
    */

    private function resolveSelectedSubject(
        $selectedSubjectId,
        $tsa,
        $status,
        $examId,
        $standardId,
        $divisionId,
        $subjectService
    ) {

        /*
        |--------------------------------------------------------------------------
        | 1. EXPLICIT SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            $selectedSubjectId
        ) {

            $actual =
                $subjectService->resolveActualSubjectId(
                    $selectedSubjectId,
                    $standardId
                );


            if (
                $actual
            ) {

                /*
                | Check whether this subject actually has marks for the
                | selected assignment. This is especially important when
                | the TSA contains historical subject data.
                */

                $historicalExists =
                    StudentMark::query()
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
                            'subject_id',
                            $actual
                        )
                        ->where(
                            'teacher_subject_allocation_id',
                            $tsa->id
                        )
                        ->exists();


                if (
                    $historicalExists
                ) {

                    return $actual;
                }


                /*
                | Even if there are no marks, the selected subject from
                | the dropdown is authoritative when it belongs to the
                | Standard.
                */

                if (
                    $subjectService->isMappedToStandard(
                        $actual,
                        $standardId
                    )
                ) {

                    return $actual;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 2. HISTORICAL SUBJECT FROM STUDENT MARKS
        |--------------------------------------------------------------------------
        |
        | Search only one TSA + exam.
        |--------------------------------------------------------------------------
        */

        $historicalSubjectIds =
            StudentMark::query()
                ->where(
                    'teacher_subject_allocation_id',
                    $tsa->id
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
                    'subject_id'
                )
                ->orderByDesc(
                    'id'
                )
                ->pluck(
                    'subject_id'
                )
                ->unique()
                ->values();


        foreach (
            $historicalSubjectIds as $storedSubjectId
        ) {

            $actual =
                $subjectService->resolveActualSubjectId(
                    $storedSubjectId,
                    $standardId
                );


            if (
                $actual
            ) {

                return $actual;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 3. TMS SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            $status
            &&
            $status->subject_id
        ) {

            $actual =
                $subjectService->resolveActualSubjectId(
                    $status->subject_id,
                    $standardId
                );


            if (
                $actual
            ) {

                return $actual;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 4. TSA SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            $tsa
            &&
            $tsa->subject_id
        ) {

            $actual =
                $subjectService->resolveActualSubjectId(
                    $tsa->subject_id,
                    $standardId
                );


            if (
                $actual
            ) {

                return $actual;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD DISPLAY ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    private function buildDisplayAssignment(
        $tsa,
        $exam,
        $subject,
        $allocation,
        $status,
        $selectedSubjectId
    ) {

        $assignment =
            new TeacherSubjectAllocation();


        $assignment->id =
            (int) $tsa->id;


        $assignment->teacher_class_allocation_id =
            (int)
            $tsa->teacher_class_allocation_id;


        $assignment->exam_master_id =
            (int)
            $exam->id;


        $assignment->subject_id =
            (int)
            $subject->id;


        /*
        |--------------------------------------------------------------------------
        | RELATIONS
        |--------------------------------------------------------------------------
        */

        $assignment->setRelation(
            'allocation',
            $allocation
        );


        $assignment->setRelation(
            'subject',
            $subject
        );


        $assignment->setRelation(
            'exam',
            $exam
        );


        /*
        |--------------------------------------------------------------------------
        | RESOLVED VALUES
        |--------------------------------------------------------------------------
        */

        $assignment->resolved_subject_id =
            (int)
            $subject->id;


        $assignment->resolved_academic_year_id =
            (int)
            $allocation->academic_year_id;


        $assignment->resolved_class_allocation_id =
            (int)
            $allocation->id;


        $assignment->resolved_exam_master_id =
            (int)
            $exam->id;


        $assignment->resolved_standard_id =
            (int)
            $allocation->standard_id;


        $assignment->resolved_division_id =
            (int)
            $allocation->division_id;


        $assignment->resolved_teacher_id =
            $allocation->user_id
                ? (int)
                    $allocation->user_id
                : null;


        $assignment->resolved_tms_subject_id =
            $status?->subject_id;


        $assignment->resolved_status =
            strtoupper(
                trim(
                    (string)
                    (
                        $status?->status
                        ??
                        'PENDING'
                    )
                )
            );


        $assignment->resolved_status_id =
            $status?->id;


        /*
        |--------------------------------------------------------------------------
        | HISTORICAL
        |--------------------------------------------------------------------------
        */

        $assignment->is_historical =
            (
                $selectedSubjectId
                &&
                $status
                &&
                (int)
                $selectedSubjectId
                !==
                (int)
                (
                    $status->subject_id
                    ?? 0
                )
            );


        /*
        |--------------------------------------------------------------------------
        | UNIQUE SELECTION KEY
        |--------------------------------------------------------------------------
        */

        $assignment->resolved_selection_key =
            $tsa->id
            . '|'
            . $subject->id;


        return $assignment;
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD STUDENTS
    |--------------------------------------------------------------------------
    */

    private function loadStudents(
        $academicYearId,
        $standardId,
        $divisionId
    ) {

        $students =
            StudentHelper::getStudentsDirectERP(
                $academicYearId,
                $standardId,
                $divisionId
            );


        $students =
            collect(
                $students
            );


        /*
        |--------------------------------------------------------------------------
        | ROLL NUMBER SORT
        |--------------------------------------------------------------------------
        */

        return $students
            ->sortBy(
                function ($student) {

                    $roll =
                        $student->roll_no
                        ??
                        $student->roll_number
                        ??
                        $student->roll
                        ??
                        $student->student_roll_no
                        ??
                        null;


                    if (
                        $roll === null
                        ||
                        $roll === ''
                    ) {

                        return PHP_INT_MAX;
                    }


                    return (int) $roll;
                }
            )
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | LOAD EXISTING MARKS
    |--------------------------------------------------------------------------
    |
    | CRITICAL PERFORMANCE + DATA FIX
    |--------------------------------------------------------------------------
    |
    | DO NOT use:
    |
    |     where teacher_subject_allocation_id = current TSA
    |
    | Instead:
    |
    |     exam
    |     subject
    |     standard
    |     division
    |
    | and then one mark per student.
    |
    |--------------------------------------------------------------------------
    */

    private function loadExistingMarks(
        $examId,
        $actualSubjectId,
        $standardId,
        $divisionId,
        $subjectService
    ) {

        /*
        |--------------------------------------------------------------------------
        | POSSIBLE SUBJECT IDs
        |--------------------------------------------------------------------------
        |
        | Current:
        |     subjects.id
        |
        | Legacy:
        |     standard_wise_subjects.id
        |--------------------------------------------------------------------------
        */

        $possibleSubjectIds =
            $subjectService
                ->getPossibleSubjectIds(
                    $actualSubjectId,
                    $standardId
                );


        if (
            $possibleSubjectIds->isEmpty()
        ) {

            return collect();
        }


        /*
        |--------------------------------------------------------------------------
        | SINGLE TARGETED QUERY
        |--------------------------------------------------------------------------
        */

        $marks =
            StudentMark::query()
                ->where(
                    'exam_master_id',
                    $examId
                )
                ->whereIn(
                    'subject_id',
                    $possibleSubjectIds
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'division_id',
                    $divisionId
                )
                ->orderByDesc(
                    'id'
                )
                ->get();


        if (
            $marks->isEmpty()
        ) {

            return collect();
        }


        /*
        |--------------------------------------------------------------------------
        | NORMALIZE SUBJECT ID IN MEMORY
        |--------------------------------------------------------------------------
        |
        | This is useful if an old mark still contains an SWS ID.
        |--------------------------------------------------------------------------
        */

        foreach (
            $marks as $mark
        ) {

            $resolved =
                $subjectService
                    ->resolveActualSubjectId(
                        $mark->subject_id,
                        $standardId
                    );


            if (
                $resolved
            ) {

                $mark->resolved_subject_id =
                    (int) $resolved;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ONE MARK PER STUDENT
        |--------------------------------------------------------------------------
        |
        | Latest record wins because query is orderByDesc(id).
        |--------------------------------------------------------------------------
        */

        return $marks
            ->unique(
                'student_id'
            )
            ->keyBy(
                'student_id'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPONENT CONFIGURATION
    |--------------------------------------------------------------------------
    */

    private function getComponentConfig(
        $exam,
        $subjectConfig
    ): array {

        /*
        |--------------------------------------------------------------------------
        | THEORY
        |--------------------------------------------------------------------------
        */

        $showTheory =
            true;


        /*
        |--------------------------------------------------------------------------
        | ORAL
        |--------------------------------------------------------------------------
        */

        $showOral =
            (bool)
            (
                $exam->has_oral
                ??
                false
            );


        /*
        |--------------------------------------------------------------------------
        | PRACTICAL
        |--------------------------------------------------------------------------
        */

        $showPractical =
            (bool)
            (
                $exam->has_practical
                ??
                false
            );


        /*
        |--------------------------------------------------------------------------
        | UNIT TEST 1
        |--------------------------------------------------------------------------
        */

        $examName =
            strtoupper(
                trim(
                    (string)
                    $exam->exam_name
                )
            );


        if (
            str_contains(
                $examName,
                'UNIT TEST 1'
            )
        ) {

            $showOral =
                false;

            $showPractical =
                false;
        }


        return [

            'showTheory' =>
                $showTheory,

            'showOral' =>
                $showOral,

            'showPractical' =>
                $showPractical,

            'theoryMaxMarks' =>
                (float)
                (
                    $subjectConfig->max_marks
                    ??
                    0
                ),

            'theoryPassingMarks' =>
                (float)
                (
                    $subjectConfig->passing_marks
                    ??
                    0
                ),

            'oralMaxMarks' =>
                $showOral
                    ? (float)
                        (
                            $exam->oral_max_marks
                            ??
                            0
                        )
                    : 0,

            'oralPassingMarks' =>
                $showOral
                    ? (float)
                        (
                            $exam->oral_passing_marks
                            ??
                            0
                        )
                    : 0,

            'practicalMaxMarks' =>
                $showPractical
                    ? (float)
                        (
                            $exam->practical_max_marks
                            ??
                            0
                        )
                    : 0,

            'practicalPassingMarks' =>
                $showPractical
                    ? (float)
                        (
                            $exam->practical_passing_marks
                            ??
                            0
                        )
                    : 0,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE MARK
    |--------------------------------------------------------------------------
    */

    private function validateMark(
        $value,
        $max,
        $required,
        $label,
        $studentId
    ) {

        if (
            $value !== null
            &&
            $value !== ''
        ) {

            $value =
                (float) $value;


            if (
                $value < 0
                ||
                $value > $max
            ) {

                throw new \RuntimeException(
                    'Invalid '
                    . $label
                    . ' marks for student ID '
                    . $studentId
                    . '. Maximum allowed marks: '
                    . $max
                );
            }


            return $value;
        }


        if (
            $required
            &&
            $max > 0
        ) {

            throw new \RuntimeException(
                $label
                . ' marks are required for student ID '
                . $studentId
            );
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE MARKS
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $subjectService
    ) {

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'teacher_subject_allocation_id' =>
                'required',

            'exam_master_id' =>
                'required|integer|exists:exam_masters,id',

            'student_ids' =>
                'required|array|min:1',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PARSE SELECTION
        |--------------------------------------------------------------------------
        */

        [
            $tsaId,
            $selectedSubjectId
        ] =
            $this->parseSelection(
                $request
            );


        $examId =
            (int)
            $request->exam_master_id;


        if (
            !$tsaId
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'teacher_subject_allocation_id' =>
                        'Invalid teaching assignment.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD TSA
        |--------------------------------------------------------------------------
        */

        $tsa =
            TeacherSubjectAllocation::find(
                $tsaId
            );


        if (
            !$tsa
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'teacher_subject_allocation_id' =>
                        'Teaching assignment was not found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD STATUS
        |--------------------------------------------------------------------------
        */

        $status =
            TeacherMarksStatus::query()
                ->where(
                    'teacher_subject_allocation_id',
                    $tsaId
                )
                ->where(
                    'exam_master_id',
                    $examId
                )
                ->orderByDesc(
                    'id'
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | LOAD CLASS ALLOCATION
        |--------------------------------------------------------------------------
        */

        $classAllocation =
            TeacherClassAllocation::find(
                $tsa->teacher_class_allocation_id
            );


        if (
            !$classAllocation
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'teacher_subject_allocation_id' =>
                        'Teacher class allocation was not found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD / DIVISION / YEAR
        |--------------------------------------------------------------------------
        */

        $standardId =
            (int)
            (
                $status?->standard_id
                ??
                $classAllocation->standard_id
            );


        $divisionId =
            (int)
            (
                $status?->division_id
                ??
                $classAllocation->division_id
            );


        $academicYearId =
            (int)
            (
                $status?->academic_year_id
                ??
                $classAllocation->academic_year_id
                ??
                0
            );


        /*
        |--------------------------------------------------------------------------
        | FALLBACK YEAR FROM EXAM
        |--------------------------------------------------------------------------
        */

        if (
            $academicYearId <= 0
        ) {

            $exam =
                ExamMaster::find(
                    $examId
                );


            $academicYearId =
                (int)
                (
                    $exam->academic_year_id
                    ??
                    0
                );
        }


        if (
            $standardId <= 0
            ||
            $divisionId <= 0
            ||
            $academicYearId <= 0
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'teacher_subject_allocation_id' =>
                        'Unable to determine Standard, Division or Academic Year for this teaching assignment.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            $this->resolveSubjectForUpdate(
                $selectedSubjectId,
                $status,
                $tsa,
                $examId,
                $standardId,
                $divisionId,
                $subjectService
            );


        if (
            !$actualSubjectId
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'Unable to resolve the actual Subject Master ID.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT MASTER
        |--------------------------------------------------------------------------
        */

        $subject =
            Subject::query()
                ->where(
                    'id',
                    $actualSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();


        if (
            !$subject
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'The selected subject was not found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD MAPPING
        |--------------------------------------------------------------------------
        */

        if (
            !$subjectService->isMappedToStandard(
                $actualSubjectId,
                $standardId
            )
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'The selected subject is not mapped to the selected Standard.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM
        |--------------------------------------------------------------------------
        */

        $exam =
            ExamMaster::query()
                ->where(
                    'id',
                    $examId
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
                ->withErrors([
                    'exam_master_id' =>
                        'The selected exam was not found.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | EXAM CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $subjectConfig =
            $subjectService->getSubjectConfig(
                $examId,
                $standardId,
                $actualSubjectId
            );


        if (
            !$subjectConfig
        ) {

            return back()
                ->withInput()
                ->withErrors([
                    'subject_id' =>
                        'Marks configuration was not found for '
                        . $subject->subject_name
                        . ' in '
                        . $exam->exam_name
                        . '.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | COMPONENTS
        |--------------------------------------------------------------------------
        */

        $component =
            $this->getComponentConfig(
                $exam,
                $subjectConfig
            );


        $theoryMax =
            $component[
                'theoryMaxMarks'
            ];


        $showOral =
            $component[
                'showOral'
            ];


        $showPractical =
            $component[
                'showPractical'
            ];


        $oralMax =
            $component[
                'oralMaxMarks'
            ];


        $practicalMax =
            $component[
                'practicalMaxMarks'
            ];


        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request,
                $tsaId,
                $examId,
                $standardId,
                $divisionId,
                $academicYearId,
                $actualSubjectId,
                $theoryMax,
                $oralMax,
                $practicalMax,
                $showOral,
                $showPractical
            ) {

                foreach (
                    $request->student_ids
                    as $studentId
                ) {

                    $studentId =
                        (string)
                        $studentId;


                    /*
                    |--------------------------------------------------------------------------
                    | FIND EXISTING MARK
                    |--------------------------------------------------------------------------
                    |
                    | IMPORTANT:
                    |
                    | Do not use TSA as the identity.
                    |
                    |--------------------------------------------------------------------------
                    */

                    $mark =
                        StudentMark::query()
                            ->where(
                                'exam_master_id',
                                $examId
                            )
                            ->where(
                                'student_id',
                                $studentId
                            )
                            ->where(
                                'subject_id',
                                $actualSubjectId
                            )
                            ->where(
                                'standard_id',
                                $standardId
                            )
                            ->where(
                                'division_id',
                                $divisionId
                            )
                            ->orderByDesc(
                                'id'
                            )
                            ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | OLD VALUES
                    |--------------------------------------------------------------------------
                    */

                    $oldTheory =
                        $mark?->theory_obtained_marks;


                    $oldOral =
                        $mark?->oral_obtained_marks;


                    $oldPractical =
                        $mark?->practical_obtained_marks;


                    /*
                    |--------------------------------------------------------------------------
                    | ABSENT
                    |--------------------------------------------------------------------------
                    */

                    $isAbsent =
                        (
                            (int)
                            (
                                $request
                                    ->is_absent[
                                        $studentId
                                    ]
                                    ??
                                    0
                            )
                        ) === 1
                            ? 1
                            : 0;


                    /*
                    |--------------------------------------------------------------------------
                    | REQUEST MARKS
                    |--------------------------------------------------------------------------
                    */

                    $theory =
                        $request
                            ->theory_marks[
                                $studentId
                            ]
                            ??
                            null;


                    $oral =
                        $request
                            ->oral_marks[
                                $studentId
                            ]
                            ??
                            null;


                    $practical =
                        $request
                            ->practical_marks[
                                $studentId
                            ]
                            ??
                            null;


                    /*
                    |--------------------------------------------------------------------------
                    | ABSENT = ZERO
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $isAbsent
                    ) {

                        $theory =
                            0;

                        $oral =
                            0;

                        $practical =
                            0;

                    } else {

                        if (
                            !$showOral
                        ) {

                            $oral =
                                null;
                        }


                        if (
                            !$showPractical
                        ) {

                            $practical =
                                null;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | THEORY
                    |--------------------------------------------------------------------------
                    */

                    $theory =
                        $this->validateMark(
                            $theory,
                            $theoryMax,
                            !$isAbsent,
                            'Theory',
                            $studentId
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | ORAL
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $showOral
                    ) {

                        $oral =
                            $this->validateMark(
                                $oral,
                                $oralMax,
                                !$isAbsent,
                                'Oral',
                                $studentId
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PRACTICAL
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $showPractical
                    ) {

                        $practical =
                            $this->validateMark(
                                $practical,
                                $practicalMax,
                                !$isAbsent,
                                'Practical',
                                $studentId
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE EXISTING
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $mark
                    ) {

                        $mark->update([

                            /*
                            |--------------------------------------------------------------------------
                            | IMPORTANT:
                            |
                            | Keep current selected TSA.
                            |
                            | The historical subject itself remains represented
                            | by the actual subject ID.
                            |--------------------------------------------------------------------------
                            */

                            'teacher_subject_allocation_id' =>
                                $tsaId,

                            'subject_id' =>
                                $actualSubjectId,

                            'standard_id' =>
                                $standardId,

                            'division_id' =>
                                $divisionId,

                            'theory_obtained_marks' =>
                                $theory,

                            'oral_obtained_marks' =>
                                $oral,

                            'practical_obtained_marks' =>
                                $practical,

                            'is_absent' =>
                                $isAbsent,

                            'updated_by' =>
                                Auth::id(),
                        ]);


                        $wasCreated =
                            false;

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | CREATE NEW MARK
                        |--------------------------------------------------------------------------
                        */

                        $mark =
                            StudentMark::create([

                                'student_id' =>
                                    $studentId,

                                'exam_master_id' =>
                                    $examId,

                                'teacher_subject_allocation_id' =>
                                    $tsaId,

                                'subject_id' =>
                                    $actualSubjectId,

                                'standard_id' =>
                                    $standardId,

                                'division_id' =>
                                    $divisionId,

                                'theory_obtained_marks' =>
                                    $theory,

                                'oral_obtained_marks' =>
                                    $oral,

                                'practical_obtained_marks' =>
                                    $practical,

                                'is_absent' =>
                                    $isAbsent,

                                'is_locked' =>
                                    0,

                                'updated_by' =>
                                    Auth::id(),
                            ]);


                        $wasCreated =
                            true;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | AUDIT LOG
                    |--------------------------------------------------------------------------
                    */

                    MarkAuditLog::create([

                        'student_mark_id' =>
                            $mark->id,

                        'student_id' =>
                            $mark->student_id,

                        'exam_master_id' =>
                            $mark->exam_master_id,

                        'subject_id' =>
                            $actualSubjectId,

                        'teacher_id' =>
                            Auth::id(),

                        'action' =>
                            'ADMIN_UPDATE',

                        'old_theory_marks' =>
                            $oldTheory,

                        'new_theory_marks' =>
                            $mark
                                ->theory_obtained_marks,

                        'old_oral_marks' =>
                            $oldOral,

                        'new_oral_marks' =>
                            $mark
                                ->oral_obtained_marks,

                        'old_practical_marks' =>
                            $oldPractical,

                        'new_practical_marks' =>
                            $mark
                                ->practical_obtained_marks,

                        'remarks' =>
                            $wasCreated
                                ? 'Admin Marks Entry'
                                : (
                                    $isAbsent
                                        ? 'Admin Marks Correction - ABSENT'
                                        : 'Admin Marks Correction - PRESENT'
                                ),

                        'ip_address' =>
                            $request->ip(),

                        'user_agent' =>
                            $request->userAgent(),
                    ]);
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'result-generation.admin-marks.edit',
            [

                'academic_year_id' =>
                    $academicYearId,

                'exam_master_id' =>
                    $examId,

                'teacher_subject_allocation_id' =>
                    $tsaId
                    . '|'
                    . $actualSubjectId,

                'subject_id' =>
                    $actualSubjectId,

                'marks_updated' =>
                    1,
            ]
        )
        ->with(
            'success',
            'Marks Updated Successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE SUBJECT FOR UPDATE
    |--------------------------------------------------------------------------
    */

    private function resolveSubjectForUpdate(
        $selectedSubjectId,
        $status,
        $tsa,
        $examId,
        $standardId,
        $divisionId,
        $subjectService
    ) {

        /*
        |--------------------------------------------------------------------------
        | 1. SELECTED SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            $selectedSubjectId
        ) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $selectedSubjectId,
                        $standardId
                    );

            if (
                $actual
            ) {

                return $actual;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 2. EXISTING STUDENT MARK
        |--------------------------------------------------------------------------
        */

        $historicalSubject =
            StudentMark::query()
                ->where(
                    'teacher_subject_allocation_id',
                    $tsa->id
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
                    'subject_id'
                )
                ->orderByDesc(
                    'id'
                )
                ->value(
                    'subject_id'
                );


        if (
            $historicalSubject
        ) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $historicalSubject,
                        $standardId
                    );

            if (
                $actual
            ) {

                return $actual;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 3. TMS
        |--------------------------------------------------------------------------
        */

        if (
            $status
            &&
            $status->subject_id
        ) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $status->subject_id,
                        $standardId
                    );

            if (
                $actual
            ) {

                return $actual;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 4. TSA
        |--------------------------------------------------------------------------
        */

        if (
            $tsa
            &&
            $tsa->subject_id
        ) {

            $actual =
                $subjectService
                    ->resolveActualSubjectId(
                        $tsa->subject_id,
                        $standardId
                    );

            if (
                $actual
            ) {

                return $actual;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | REOPEN
    |--------------------------------------------------------------------------
    */

    public function reopen(
        Request $request,
        $subjectService
    ) {

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'exam_master_id' =>
                'required',

            'subject_id' =>
                'required',

            'standard_id' =>
                'required',

            'division_id' =>
                'required',
        ]);


        $examId =
            (int)
            $request->exam_master_id;


        $standardId =
            (int)
            $request->standard_id;


        $divisionId =
            (int)
            $request->division_id;


        /*
        |--------------------------------------------------------------------------
        | ACTUAL SUBJECT
        |--------------------------------------------------------------------------
        */

        $actualSubjectId =
            $subjectService
                ->resolveActualSubjectId(
                    $request->subject_id,
                    $standardId
                );


        if (
            !$actualSubjectId
        ) {

            return back()
                ->with(
                    'error',
                    'Unable to resolve Subject Master ID.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | MARKS
        |--------------------------------------------------------------------------
        |
        | TSA is deliberately NOT required.
        |
        | This supports historical marks.
        |--------------------------------------------------------------------------
        */

        $possibleSubjectIds =
            $subjectService
                ->getPossibleSubjectIds(
                    $actualSubjectId,
                    $standardId
                );


        $marks =
            StudentMark::query()
                ->where(
                    'exam_master_id',
                    $examId
                )
                ->whereIn(
                    'subject_id',
                    $possibleSubjectIds
                )
                ->where(
                    'standard_id',
                    $standardId
                )
                ->where(
                    'division_id',
                    $divisionId
                )
                ->get();


        if (
            $marks->isEmpty()
        ) {

            return back()
                ->with(
                    'error',
                    'No marks found for the selected Subject.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $request,
                $marks,
                $examId
            ) {

                foreach (
                    $marks as $mark
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | UNLOCK
                    |--------------------------------------------------------------------------
                    */

                    $mark->update([

                        'is_locked' =>
                            0,

                        'updated_by' =>
                            Auth::id(),
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | AUDIT
                    |--------------------------------------------------------------------------
                    */

                    MarkAuditLog::create([

                        'student_mark_id' =>
                            $mark->id,

                        'student_id' =>
                            $mark->student_id,

                        'exam_master_id' =>
                            $mark->exam_master_id,

                        'subject_id' =>
                            $mark->subject_id,

                        'teacher_id' =>
                            Auth::id(),

                        'action' =>
                            'REOPEN',

                        'remarks' =>
                            $request->remarks
                            ??
                            'Marks reopened by admin',

                        'ip_address' =>
                            $request->ip(),

                        'user_agent' =>
                            $request->userAgent(),
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | RESET STATUS FOR TSA REFERENCES
                |--------------------------------------------------------------------------
                */

                $tsaIds =
                    $marks
                        ->pluck(
                            'teacher_subject_allocation_id'
                        )
                        ->filter()
                        ->unique()
                        ->values();


                if (
                    $tsaIds->isNotEmpty()
                ) {

                    TeacherMarksStatus::query()
                        ->where(
                            'exam_master_id',
                            $examId
                        )
                        ->whereIn(
                            'teacher_subject_allocation_id',
                            $tsaIds
                        )
                        ->update([

                            'status' =>
                                'PENDING',

                            'updated_at' =>
                                now(),
                        ]);
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'result-generation.admin-marks.edit',
            [

                'exam_master_id' =>
                    $examId,

                'teacher_subject_allocation_id' =>
                    $request->input(
                        'teacher_subject_allocation_id'
                    ),

                'subject_id' =>
                    $actualSubjectId,

                'standard_id' =>
                    $standardId,

                'division_id' =>
                    $divisionId,

                'marks_reopened' =>
                    1,
            ]
        )
        ->with(
            'success',
            'Marks reopened successfully.'
        );
    }
}