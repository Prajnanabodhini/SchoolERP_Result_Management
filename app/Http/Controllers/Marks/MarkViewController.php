<?php

namespace App\Http\Controllers\Marks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
use App\Models\ExamMasterSubject;
use App\Models\TeacherSubjectAllocation;
use App\Models\TeacherMarksStatus;
use App\Models\StudentMark;

use App\Helpers\StudentHelper;

class MarkViewController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATOR DETECTION
    |--------------------------------------------------------------------------
    */

    private function isAdministrator(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {

            if (
                $user->hasRole('Administrator') ||
                $user->hasRole('admin')
            ) {
                return true;
            }
        }

        $role = strtolower(
            trim(
                (string) ($user->role ?? '')
            )
        );

        return in_array(
            $role,
            [
                'administrator',
                'admin',
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE ACTUAL SUBJECT ID
    |--------------------------------------------------------------------------
    */

    private function resolveActualSubjectId(
        $storedSubjectId,
        $standardId
    ): ?int {

        if (
            $storedSubjectId === null ||
            $storedSubjectId === '' ||
            !$standardId
        ) {
            return null;
        }

        $storedSubjectId = (int) $storedSubjectId;
        $standardId = (int) $standardId;

        if (
            $storedSubjectId <= 0 ||
            $standardId <= 0
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | CURRENT FORMAT
        | subject_id = subjects.id
        |--------------------------------------------------------------------------
        */

        $currentSubject = DB::table('subjects')
            ->where('id', $storedSubjectId)
            ->where('is_active', 1)
            ->first();

        if ($currentSubject) {

            $mappingExists = DB::table(
                'standard_wise_subjects'
            )
                ->where('standard_id', $standardId)
                ->where('subject_id', $storedSubjectId)
                ->where('is_active', 1)
                ->exists();

            if ($mappingExists) {
                return $storedSubjectId;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LEGACY FORMAT
        | subject_id = standard_wise_subjects.id
        |--------------------------------------------------------------------------
        */

        $legacyMapping = DB::table(
            'standard_wise_subjects'
        )
            ->where('id', $storedSubjectId)
            ->where('standard_id', $standardId)
            ->where('is_active', 1)
            ->first();

        if (
            $legacyMapping &&
            !empty($legacyMapping->subject_id)
        ) {

            $actualSubject = DB::table('subjects')
                ->where(
                    'id',
                    (int) $legacyMapping->subject_id
                )
                ->where('is_active', 1)
                ->first();

            if ($actualSubject) {
                return (int) $actualSubject->id;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE DISPLAY SUBJECT
    |--------------------------------------------------------------------------
    |
    | TSA subject is PRIMARY.
    | TMS subject is only a legacy fallback.
    |--------------------------------------------------------------------------
    */

    private function resolveDisplaySubject(
        $storedSubjectId,
        $standardId,
        $tmsSubjectId = null
    ) {

        $standardId = (int) $standardId;

        if ($standardId <= 0) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | PRIMARY - TSA SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            $storedSubjectId !== null &&
            $storedSubjectId !== '' &&
            (int) $storedSubjectId > 0
        ) {

            $actualSubjectId =
                $this->resolveActualSubjectId(
                    $storedSubjectId,
                    $standardId
                );

            if ($actualSubjectId) {

                $subject = DB::table('subjects')
                    ->where(
                        'id',
                        $actualSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();

                if ($subject) {
                    return $subject;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FALLBACK - TMS SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            $tmsSubjectId !== null &&
            $tmsSubjectId !== '' &&
            (int) $tmsSubjectId > 0
        ) {

            $tmsSubjectId = (int) $tmsSubjectId;


            /*
            |--------------------------------------------------------------------------
            | TMS.subject_id = subjects.id
            |--------------------------------------------------------------------------
            */

            $subject = DB::table('subjects')
                ->where(
                    'id',
                    $tmsSubjectId
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();

            if ($subject) {

                $mappingExists = DB::table(
                    'standard_wise_subjects'
                )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'subject_id',
                        $tmsSubjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->exists();

                if ($mappingExists) {
                    return $subject;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | TMS.subject_id = standard_wise_subjects.id
            |--------------------------------------------------------------------------
            */

            $legacyMapping = DB::table(
                'standard_wise_subjects as sws'
            )
                ->leftJoin(
                    'subjects as s',
                    's.id',
                    '=',
                    'sws.subject_id'
                )
                ->where(
                    'sws.id',
                    $tmsSubjectId
                )
                ->where(
                    'sws.standard_id',
                    $standardId
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
                    's.id',
                    's.subject_name',
                    's.subject_code',
                    's.short_name',
                ])
                ->first();

            if ($legacyMapping) {
                return $legacyMapping;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | VIEW MARKS
    |--------------------------------------------------------------------------
    */

    public function viewMarks(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | INITIAL VALUES
        |--------------------------------------------------------------------------
        */

        $records = collect();

        $subjects = collect();

        $exam = null;

        $selectedTsa = null;

        $selectedSubject = null;

        $showTheory = false;

        $showOral = false;

        $showPractical = false;

        $error = null;


        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $userId = (int) $user->id;

        $isAdministrator = $this->isAdministrator();


        /*
        |--------------------------------------------------------------------------
        | REQUEST
        |--------------------------------------------------------------------------
        */

        $examId =
            $request->input('exam_master_id');

        $tsaId =
            $request->input(
                'teacher_subject_allocation_id'
            );


        /*
        |--------------------------------------------------------------------------
        | EXAMS
        |--------------------------------------------------------------------------
        */

        $exams = ExamMaster::query()
            ->where('is_active', 1)
            ->orderBy('display_order')
            ->orderBy('exam_name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | STANDARDS
        |--------------------------------------------------------------------------
        */

        $standards = Standard::query()
            ->where('is_active', 1)
            ->orderBy('display_order')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | DIVISIONS
        |--------------------------------------------------------------------------
        */

        $divisions = Division::query()
            ->where('is_active', 1)
            ->orderBy('division_name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | SELECTED EXAM
        |--------------------------------------------------------------------------
        */

        if ($examId) {

            $exam = $exams->firstWhere(
                'id',
                (int) $examId
            );

            if (!$exam) {

                $error =
                    'Selected exam was not found.';

            } else {

                $showTheory =
                    (bool) $exam->has_theory;

                $showOral =
                    (bool) $exam->has_oral;

                $showPractical =
                    (bool) $exam->has_practical;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SELECTED TEACHER SUBJECT ALLOCATION
        |--------------------------------------------------------------------------
        */

        if ($tsaId) {

            $tsaQuery =
                TeacherSubjectAllocation::query()
                    ->with([
                        'allocation.teacher',
                        'allocation.academicYear',
                        'allocation.section',
                        'allocation.standard',
                        'allocation.division',
                        'exam',
                    ])
                    ->where(
                        'id',
                        (int) $tsaId
                    );


            /*
            |--------------------------------------------------------------------------
            | EXAM SECURITY
            |--------------------------------------------------------------------------
            */

            if ($examId) {

                $tsaQuery->where(
                    'exam_master_id',
                    (int) $examId
                );
            }


            /*
            |--------------------------------------------------------------------------
            | TEACHER SECURITY
            |--------------------------------------------------------------------------
            */

            if (!$isAdministrator) {

                $tsaQuery->whereHas(
                    'allocation',
                    function ($query) use ($userId) {

                        $query->where(
                            'user_id',
                            $userId
                        );
                    }
                );
            }


            $selectedTsa =
                $tsaQuery->first();


            if (!$selectedTsa) {

                $error =
                    'Selected teaching assignment was not found or is not assigned to you.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CLASS ALLOCATION
        |--------------------------------------------------------------------------
        */

        $allocation =
            $selectedTsa
                ? $selectedTsa->allocation
                : null;


        if (
            $selectedTsa &&
            !$allocation
        ) {

            $selectedTsa = null;

            $error =
                'Teacher class allocation was not found.';
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD / DIVISION / ACADEMIC YEAR
        |--------------------------------------------------------------------------
        */

        $standardId =
            $allocation
                ? (int) $allocation->standard_id
                : 0;

        $divisionId =
            $allocation
                ? (int) $allocation->division_id
                : 0;

        $academicYearId =
            $allocation
                ? (int) $allocation->academic_year_id
                : 0;


        /*
        |--------------------------------------------------------------------------
        | RESOLVE SUBJECT
        |--------------------------------------------------------------------------
        */

        if (
            $selectedTsa &&
            $allocation
        ) {

            /*
            |--------------------------------------------------------------------------
            | GET TMS STATUS
            |--------------------------------------------------------------------------
            */

            $statusQuery =
                TeacherMarksStatus::query()
                    ->where(
                        'teacher_subject_allocation_id',
                        $selectedTsa->id
                    )
                    ->where(
                        'exam_master_id',
                        $selectedTsa->exam_master_id
                    );

            if (!$isAdministrator) {

                $statusQuery->where(
                    'teacher_id',
                    $userId
                );
            }

            $status =
                $statusQuery->first();


            $tmsSubjectId =
                $status
                    ? $status->subject_id
                    : null;


            /*
            |--------------------------------------------------------------------------
            | TSA SUBJECT PRIMARY
            |--------------------------------------------------------------------------
            */

            $selectedSubject =
                $this->resolveDisplaySubject(
                    $selectedTsa->subject_id,
                    $standardId,
                    $tmsSubjectId
                );


            if ($selectedSubject) {

                $selectedTsa->setRelation(
                    'subject',
                    $selectedSubject
                );

                $selectedTsa->resolved_subject_id =
                    (int) $selectedSubject->id;

            } else {

                $error =
                    'Subject could not be resolved from the selected teaching assignment.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SUBJECT ID
        |--------------------------------------------------------------------------
        */

        $subjectId =
            $selectedTsa
                ? (
                    $selectedTsa->resolved_subject_id
                    ?? null
                )
                : null;


        /*
        |--------------------------------------------------------------------------
        | EXAM SUBJECT CONFIGURATION
        |--------------------------------------------------------------------------
        */

        $subjectConfig = null;

        if (
            $exam &&
            $selectedTsa &&
            $subjectId
        ) {

            /*
            |--------------------------------------------------------------------------
            | CURRENT CONFIGURATION
            |--------------------------------------------------------------------------
            */

            $subjectConfig =
                ExamMasterSubject::query()
                    ->where(
                        'exam_master_id',
                        $exam->id
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'subject_id',
                        $subjectId
                    )
                    ->first();


            /*
            |--------------------------------------------------------------------------
            | LEGACY CONFIGURATION
            |--------------------------------------------------------------------------
            */

            if (!$subjectConfig) {

                $mapping =
                    DB::table(
                        'standard_wise_subjects'
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'subject_id',
                        $subjectId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();

                if ($mapping) {

                    $subjectConfig =
                        ExamMasterSubject::query()
                            ->where(
                                'exam_master_id',
                                $exam->id
                            )
                            ->where(
                                'standard_id',
                                $standardId
                            )
                            ->where(
                                'subject_id',
                                $mapping->id
                            )
                            ->first();
                }
            }


            if (!$subjectConfig) {

                $error =
                    'Marks configuration not found for '
                    . (
                        $selectedSubject->subject_name
                        ?? 'Selected Subject'
                    )
                    . '.';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ONLY SELECTED SUBJECT
        |--------------------------------------------------------------------------
        */

        if ($subjectConfig) {

            $subjects =
                ExamMasterSubject::query()
                    ->join(
                        'subjects',
                        'subjects.id',
                        '=',
                        'exam_master_subjects.subject_id'
                    )
                    ->where(
                        'exam_master_subjects.id',
                        $subjectConfig->id
                    )
                    ->where(
                        'subjects.is_active',
                        1
                    )
                    ->select([
                        'subjects.id',
                        'subjects.subject_name',
                        'subjects.subject_code',
                        'subjects.short_name',
                        'exam_master_subjects.max_marks',
                        'exam_master_subjects.passing_marks',
                        'exam_master_subjects.display_order',
                    ])
                    ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD STUDENTS EXACTLY LIKE MARK ENTRY
        |--------------------------------------------------------------------------
        */

        $students = collect();

        if (
            $allocation &&
            $exam &&
            $selectedTsa
        ) {

            try {

                $students =
                    StudentHelper::getStudentsDirectERP(
                        $allocation->academic_year_id,
                        $allocation->standard_id,
                        $allocation->division_id
                    );


                /*
                |--------------------------------------------------------------------------
                | SAME SORT AS MARK ENTRY
                |--------------------------------------------------------------------------
                */

                $students =
                    $students
                        ->sort(
                            function ($a, $b) {

                                $genderA =
                                    strtoupper(
                                        trim(
                                            $a->gender
                                            ?? ''
                                        )
                                    );

                                $genderB =
                                    strtoupper(
                                        trim(
                                            $b->gender
                                            ?? ''
                                        )
                                    );


                                if (
                                    $genderA !== $genderB
                                ) {

                                    if (
                                        in_array(
                                            $genderA,
                                            [
                                                'F',
                                                'FEMALE',
                                            ],
                                            true
                                        )
                                    ) {
                                        return -1;
                                    }

                                    if (
                                        in_array(
                                            $genderB,
                                            [
                                                'F',
                                                'FEMALE',
                                            ],
                                            true
                                        )
                                    ) {
                                        return 1;
                                    }
                                }


                                return strcmp(
                                    strtoupper(
                                        trim(
                                            $a->studname
                                            ?? ''
                                        )
                                    ),
                                    strtoupper(
                                        trim(
                                            $b->studname
                                            ?? ''
                                        )
                                    )
                                );
                            }
                        )
                        ->values();

            } catch (\Throwable $e) {

                report($e);

                $students = collect();

                $error =
                    'Old ERP Error: '
                    . $e->getMessage();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD SAVED MARKS
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | SAME AS MARK ENTRY:
        |
        | exam_master_id
        | +
        | teacher_subject_allocation_id
        |
        | We intentionally DO NOT filter here by:
        |
        | subject_id
        | standard_id
        | division_id
        |
        | because MarkEntryController also loads existing marks using
        | only Exam + TSA.
        |
        |--------------------------------------------------------------------------
        */

        $existingMarks =
            collect();

        if (
            $selectedTsa &&
            $exam
        ) {

            $existingMarks =
                StudentMark::query()
                    ->where(
                        'exam_master_id',
                        $exam->id
                    )
                    ->where(
                        'teacher_subject_allocation_id',
                        $selectedTsa->id
                    )
                    ->get()
                    ->keyBy(
                        'student_id'
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | BUILD VIEW RECORDS
        |--------------------------------------------------------------------------
        */

        foreach (
            $students as $student
        ) {

            $studentId =
                (int)(
                    $student->Studentid
                    ?? $student->studentid
                    ?? $student->student_id
                    ?? $student->id
                    ?? 0
                );


            if (
                $studentId <= 0
            ) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ONLY STUDENTS WITH SAVED MARKS
            |--------------------------------------------------------------------------
            */

            $savedMark =
                $existingMarks->get(
                    $studentId
                );


            if (!$savedMark) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | NAME
            |--------------------------------------------------------------------------
            */

            $studName =
                trim(
                    (string)(
                        $student->studname
                        ?? ''
                    )
                );


            $fatherName =
                trim(
                    (string)(
                        $student->fathername
                        ?? ''
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | RECORD
            |--------------------------------------------------------------------------
            */

            $records->push(
                (object)[

                    'id' =>
                        $savedMark->id,

                    'student_id' =>
                        $studentId,

                    'studname' =>
                        $studName,

                    'fathername' =>
                        $fatherName,

                    'regno' =>
                        $student->regno
                        ?? $student->registration_no
                        ?? $student->gr_no
                        ?? '',

                    'rollno' =>
                        $student->rollno
                        ?? $student->roll_no
                        ?? '',

                    'gender' =>
                        $student->gender
                        ?? '',

                    'is_absent' =>
                        (int)(
                            $savedMark->is_absent
                            ?? 0
                        ),

                    'status' =>
                        $savedMark->status
                        ?? null,

                    'theory_max_marks' =>
                        $savedMark->theory_max_marks
                        ?? (
                            $subjectConfig->max_marks
                            ?? 0
                        ),

                    'theory_passing_marks' =>
                        $savedMark->theory_passing_marks
                        ?? (
                            $subjectConfig->passing_marks
                            ?? 0
                        ),

                    'theory_obtained_marks' =>
                        $savedMark->theory_obtained_marks
                        ?? null,

                    'oral_max_marks' =>
                        $savedMark->oral_max_marks
                        ?? (
                            $exam->oral_max_marks
                            ?? 0
                        ),

                    'oral_passing_marks' =>
                        $savedMark->oral_passing_marks
                        ?? (
                            $exam->oral_passing_marks
                            ?? 0
                        ),

                    'oral_obtained_marks' =>
                        $savedMark->oral_obtained_marks
                        ?? null,

                    'practical_max_marks' =>
                        $savedMark->practical_max_marks
                        ?? (
                            $exam->practical_max_marks
                            ?? 0
                        ),

                    'practical_passing_marks' =>
                        $savedMark->practical_passing_marks
                        ?? (
                            $exam->practical_passing_marks
                            ?? 0
                        ),

                    'practical_obtained_marks' =>
                        $savedMark->practical_obtained_marks
                        ?? null,

                    'subject_name' =>
                        $selectedSubject
                            ->subject_name
                        ?? '',

                    'subject_code' =>
                        $selectedSubject
                            ->subject_code
                        ?? '',

                    'exam_name' =>
                        $exam->exam_name
                        ?? '',
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SORT RECORDS LIKE MARK ENTRY
        |--------------------------------------------------------------------------
        */

        $records =
            $records
                ->sortBy(
                    function ($student) {

                        $rollNo =
                            trim(
                                (string)(
                                    $student->rollno
                                    ?? ''
                                )
                            );


                        if (
                            $rollNo !== '' &&
                            is_numeric($rollNo)
                        ) {

                            return [
                                0,
                                (int)$rollNo,
                            ];
                        }


                        return [
                            1,
                            strtoupper($rollNo),
                        ];
                    }
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'marks-entry.view',
            compact(
                'records',
                'standards',
                'divisions',
                'subjects',
                'exams',
                'exam',
                'showTheory',
                'showOral',
                'showPractical',
                'selectedTsa',
                'selectedSubject',
                'error'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH MARKS
    |--------------------------------------------------------------------------
    */

    public function searchMarks(
        Request $request
    ) {
        return $this->viewMarks($request);
    }
}