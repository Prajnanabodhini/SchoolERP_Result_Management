<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;
use App\Models\ExamMaster;
use App\Models\StandardMst;
use App\Models\DivisionMst;
use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\TeacherClassAllocation;
use App\Helpers\StudentHelper;
use App\Helpers\ExamHelper;
use App\Models\StudentMark;
use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\Standard;
use App\Models\Division;


class MarksEntryController extends Controller
{

    public function save(Request $request)
    {

        $request->validate([
            'exam_master_id' => 'required',
            'teacher_subject_allocation_id' => 'required',
        ]);

        $teacherSubjectAllocation =
            TeacherSubjectAllocation::with([
                'allocation.standard',
                'allocation.division',
                'subject'
            ])
            ->find(
                $request->teacher_subject_allocation_id
            );

        if (!$teacherSubjectAllocation) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Teaching Assignment not found.'
                );
        }

        $allocation =
            $teacherSubjectAllocation->allocation;

        $exam =
            ExamMaster::find(
                $request->exam_master_id
            );

        $subjects = Subject::query();

        if ($request->exam_master_id) {

            $selectedExam = ExamMaster::find(
                $request->exam_master_id
            );

            if (
                $selectedExam &&
                strtoupper($selectedExam->exam_name) == 'UNIT TEST 1'
            ) {

                $subjects->where(
                    'subject_type_id',
                    1
                );
            }
        }

        $subjects = $subjects
            ->orderBy('subject_name')
            ->get();

        $rules = [];

        foreach ($request->student_ids as $studentId) {

            if ($exam->has_theory) {

                $rules["theory_marks.$studentId"] =
                    'required|numeric|min:0|max:' .
                    $exam->theory_max_marks;
            }

            if ($exam->has_oral) {

                $rules["oral_marks.$studentId"] =
                    'required|numeric|min:0|max:' .
                    $exam->oral_max_marks;
            }

            if ($exam->has_practical) {

                $rules["practical_marks.$studentId"] =
                    'required|numeric|min:0|max:' .
                    $exam->practical_max_marks;
            }
        }

        $request->validate(
            $rules,
            [
                'required' =>
                'Marks required.',
                'numeric' =>
                'Only numeric value allowed.',
                'min' =>
                'Marks cannot be negative.',
                'max' =>
                'Marks exceed maximum allowed.'
            ]
        );

        if ($exam) {
            $showTheory =
                $exam->has_theory ?? 1;

            $showOral =
                $exam->has_oral ?? 0;

            $showPractical =
                $exam->has_practical ?? 0;

            // $marksLocked =
            //     \App\Models\StudentMark::where(
            //         'academic_year_id',
            //         'academic_year_id',
            //         $allocation->academic_year_id
            //     )
            $marksLocked =
    StudentMark::where(
        'academic_year_id',
        $allocation->academic_year_id
    )
                ->where(
                    'section_id',
                    session('sectionid')
                )
                ->where(
                    'exam_master_id',
                    $exam->id
                )
                ->where(
                    'subject_id',
                    $teacherSubjectAllocation->subject_id
                )
                ->exists();

            if ($marksLocked) {
                // $students = collect();

                $error =
                    'Marks already submitted for this Exam and Subject. Contact Admin for modification.';
            }
        }

        $yearId =
            session('yearid');

        $oldStandardId =
            $allocation
            ->standard
            ->old_standard_id;


        foreach (
            $request->student_ids
            as $studentId
        ) {
            foreach ($request->student_ids as $studentId) {
                $alreadyExists = StudentMark::where(
                    'academic_year_id',
                    $allocation->academic_year_id
                )
                    ->where(
                        'section_id',
                        $allocation->section_id
                    )
                    ->where(
                        'exam_master_id',
                        $exam->id
                    )
                    ->where(
                        'subject_id',
                        $teacherSubjectAllocation->subject_id
                    )
                    ->where(
                        'student_id',
                        $studentId
                    )
                    ->exists();

//                 if ($alreadyExists) {

//     TeacherMarksStatus::updateOrCreate(
//     [
//         'exam_master_id' => $request->exam_master_id,
//         'teacher_subject_allocation_id' => $teacherSubjectAllocation->id
//     ],
//     [
//         'academic_year_id' => $allocation->academic_year_id,
//         'standard_id' => $allocation->standard_id,
//         'division_id' => $allocation->division_id,
//         'subject_id' => $teacherSubjectAllocation->subject_id,
//         'teacher_id' => Auth::id(),
//         'status' => 'COMPLETED'
//     ]
// );

 
//                     return redirect()
//                         ->back()
//                         ->withInput()
//                         ->with(
//                             'error',
//                             'Marks have already been entered for this Exam and Subject. Please use View/Edit Marks to modify them.'
//                         );
//                 }
            }
        }

        /*
|--------------------------------------------------------------------------
| Save / Update Marks
|--------------------------------------------------------------------------
*/

foreach ($request->student_ids as $studentId) {

    StudentMark::updateOrCreate(

        [
            'academic_year_id' =>
                $allocation->academic_year_id,

            'section_id' =>
    $allocation->section_id,

            'student_id' =>
                $studentId,

            'exam_master_id' =>
                $exam->id,

            'subject_id' =>
                $teacherSubjectAllocation->subject_id,
        ],

        [
            'standard_id' =>
                $allocation->standard_id,

            'division_id' =>
                $allocation->division_id,

            'teacher_subject_allocation_id' =>
                $teacherSubjectAllocation->id,

            'theory_max_marks' =>
                $exam->theory_max_marks,

            'theory_passing_marks' =>
                $exam->theory_passing_marks,

            'theory_obtained_marks' =>
                $request->theory_marks[$studentId] ?? null,

            'oral_max_marks' =>
                $exam->oral_max_marks,

            'oral_passing_marks' =>
                $exam->oral_passing_marks,

            'oral_obtained_marks' =>
                $request->oral_marks[$studentId] ?? null,

            'practical_max_marks' =>
                $exam->practical_max_marks,

            'practical_passing_marks' =>
                $exam->practical_passing_marks,

            'practical_obtained_marks' =>
                $request->practical_marks[$studentId] ?? null,

            'is_locked' => 1,

            'created_by' => Auth::id(),

            'updated_by' => Auth::id(),
        ]
    );
}


        $tsa =
            TeacherSubjectAllocation::find(
                $request->teacher_subject_allocation_id
            );

        if ($tsa) {
            $classAllocation =
                TeacherClassAllocation::find(
                    $tsa->teacher_class_allocation_id
                );
            $classAllocation =
                TeacherClassAllocation::find(
                    $tsa->teacher_class_allocation_id
                );

            $expectedOldSectionId = null;

            switch ($classAllocation->section_id) {
                case 1:
                    $expectedOldSectionId = 1022; // PRE-PRIMARY
                    break;

                case 2:
                    $expectedOldSectionId = 1023; // PRIMARY
                    break;

                case 3:
                    $expectedOldSectionId = 1024; // SECONDARY
                    break;

                case 4:
                    $expectedOldSectionId = 1025; // HSC SCIENCE
                    break;

                case 5:
                    $expectedOldSectionId = 1026; // HSC COMMERCE
                    break;

                case 6:
                    $expectedOldSectionId = 1027; // HSC ARTS
                    break;
            }

            // if ($expectedOldSectionId != session('sectionid')) {
            //     session()->flash(
            //         'force_section_error',
            //         'You have selected the wrong section. Please login again and select the correct section.'
            //     );

            //     return redirect()->route('marks-entry.index');
            // }
            TeacherMarksStatus::updateOrCreate(

                [
                    'exam_master_id' =>
                    $request->exam_master_id,

                    'teacher_subject_allocation_id' =>
                    $tsa->id
                ],

                [
                    'academic_year_id' =>
                    $classAllocation->academic_year_id,

                    'standard_id' =>
                    $classAllocation->standard_id,

                    'division_id' =>
                    $classAllocation->division_id,

                    'subject_id' =>
                    $tsa->subject_id,

                    'teacher_id' =>
                    $classAllocation->user_id,

                    'status' =>
                    'COMPLETED'
                ]
            );

            ExamHelper::updateCompletionStatus(
                $classAllocation->academic_year_id,
                $request->exam_master_id,
                $classAllocation->standard_id,
                $classAllocation->division_id
            );
        }


        return redirect()
            ->route('marks-entry.index')
            ->with(
                'success',
                'Marks Saved Successfully.'
            );
    }



    public function index(Request $request)
    {
        $students = collect();
        $assignments = collect();

        $exam = null;
        $error = '';
        $message = '';

        $showTheory = false;
        $showOral = false;
        $showPractical = false;

        $theoryMaxMarks = 0;
        $theoryPassingMarks = 0;

        $oralMaxMarks = 0;
        $oralPassingMarks = 0;

        $practicalMaxMarks = 0;
        $practicalPassingMarks = 0;

        $marksLocked = false;


        /*
    |--------------------------------------------------------------------------
    | Restore Session Values
    |--------------------------------------------------------------------------
    */

        if (
            !$request->filled('teacher_subject_allocation_id')
            &&
            session()->has('marks_teacher_subject_allocation_id')
        ) {

            $request->merge([
                'teacher_subject_allocation_id' =>
                session('marks_teacher_subject_allocation_id')
            ]);
        }


        if (
            !$request->filled('exam_master_id')
            &&
            session()->has('marks_exam_master_id')
        ) {

            $request->merge([
                'exam_master_id' =>
                session('marks_exam_master_id')
            ]);
        }



        /*
    |--------------------------------------------------------------------------
    | Exams
    |--------------------------------------------------------------------------
    */

        $exams = ExamMaster::where('is_active', 1)
            ->orderBy('display_order')
            ->get();



        /*
    |--------------------------------------------------------------------------
    | Load Teacher Assignments
    |--------------------------------------------------------------------------
    */

        if ($request->filled('exam_master_id')) {

            $pendingIds = TeacherMarksStatus::where(
                'teacher_id',
                Auth::id()
            )
                ->where(
                    'exam_master_id',
                    $request->exam_master_id
                )
                ->where(
                    'status',
                    'PENDING'
                )
                ->pluck(
                    'teacher_subject_allocation_id'
                );


            $assignments =
                TeacherSubjectAllocation::with([
                    'allocation.standard',
                    'allocation.division',
                    'subject'
                ])
                ->whereIn(
                    'id',
                    $pendingIds
                )
                ->get();


            if ($assignments->count() == 0) {
                $error =
                    "No teaching assignment found.";
            }
        }


        if (
            $request->filled('teacher_subject_allocation_id')
            &&
            $request->filled('exam_master_id')
        ) {

            $teacherSubjectAllocation =
                TeacherSubjectAllocation::with([
                    'allocation.standard',
                    'allocation.division',
                    'subject'
                ])
                ->find(
                    $request->teacher_subject_allocation_id
                );



            if ($teacherSubjectAllocation) {

                /*
|--------------------------------------------------------------------------
| Load Students From ERP Master
|--------------------------------------------------------------------------
*/

                $allocation =
                    $teacherSubjectAllocation->allocation;


                $year =
                    $allocation->academic_year_id;

                $students = StudentHelper::getERPStudents(
                    $allocation->academic_year_id,
                    $allocation->standard_id,
                    $allocation->division_id
                );

                /*
            |--------------------------------------------------------------------------
            | Exam Configuration
            |--------------------------------------------------------------------------
            */

                $exam =
                    ExamMaster::find(
                        $request->exam_master_id
                    );


                if ($exam) {

                    $showTheory =
                        (bool)$exam->has_theory;


                    $showOral =
                        (bool)$exam->has_oral;


                    $showPractical =
                        (bool)$exam->has_practical;



                    $theoryMaxMarks =
                        $exam->theory_max_marks ?? 0;


                    $theoryPassingMarks =
                        $exam->theory_passing_marks ?? 0;



                    $oralMaxMarks =
                        $exam->oral_max_marks ?? 0;


                    $oralPassingMarks =
                        $exam->oral_passing_marks ?? 0;



                    $practicalMaxMarks =
                        $exam->practical_max_marks ?? 0;


                    $practicalPassingMarks =
                        $exam->practical_passing_marks ?? 0;
                }
            }
        }




        /*
    |--------------------------------------------------------------------------
    | Check Marks Status
    |--------------------------------------------------------------------------
    */

        $marksStatus =
            TeacherMarksStatus::where(
                'teacher_subject_allocation_id',
                $request->teacher_subject_allocation_id
            )
            ->where(
                'exam_master_id',
                $request->exam_master_id
            )
            ->where(
                'teacher_id',
                Auth::id()
            )
            ->first();



        if (
            $marksStatus &&
            strtoupper($marksStatus->status) == 'COMPLETED'
        ) {

            $error =
                "Marks entry has already been completed.";
        }




        return view(
            'marks-entry.index',
            compact(
                'request',
                'exams',
                'assignments',
                'students',
                'exam',
                'showTheory',
                'showOral',
                'showPractical',
                'marksLocked',
                'message',
                'error',
                'theoryMaxMarks',
                'theoryPassingMarks',
                'oralMaxMarks',
                'oralPassingMarks',
                'practicalMaxMarks',
                'practicalPassingMarks'
            )
        );
    }
}
