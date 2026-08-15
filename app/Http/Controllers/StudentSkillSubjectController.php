<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Standard;
use App\Models\Division;
use App\Models\Subject;
use App\Models\StudentSkillSubject;
use App\Models\AcademicYear;
use App\Helpers\StudentHelper;

class StudentSkillSubjectController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();

        $standards = Standard::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        $divisions = Division::where('is_active', 1)
            ->orderBy('division_name')
            ->get();

        $students = collect();

        $skillSubjects = collect();

        /*
        |--------------------------------------------------------------------------
        | Skill Subjects
        |--------------------------------------------------------------------------
        */

        if ($request->standard_id) {

            $skillSubjects = Subject::join(
                'standard_subjects',
                'subjects.id',
                '=',
                'standard_subjects.subject_id'
            )
            ->where(
                'standard_subjects.standard_id',
                $request->standard_id
            )
            ->where(
                'subjects.subject_type_id',
                2
            )
            ->where(
                'subjects.is_active',
                1
            )
            ->select('subjects.*')
            ->orderBy('subjects.subject_name')
            ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Students
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('academic_year_id') &&
            $request->filled('standard_id') &&
            $request->filled('division_id')
        ) {

            $academicYear = AcademicYear::find(
                $request->academic_year_id
            );

            if ($academicYear) {

                /*
                |--------------------------------------------------------------------------
                | ERP Year
                |--------------------------------------------------------------------------
                */

                $yearId =
                    $academicYear->old_year_id
                    ?? substr(
                        $academicYear->year_name,
                        0,
                        4
                    );

                /*
                |--------------------------------------------------------------------------
                | Load Students From Old ERP
                |--------------------------------------------------------------------------
                */

                $students =
                    StudentHelper::getStudentsDirectERP(
                        $yearId,
                        $request->standard_id,
                        $request->division_id
                    );

                /*
                |--------------------------------------------------------------------------
                | Load Saved Skill Subject
                |--------------------------------------------------------------------------
                */

                foreach ($students as $student) {

                    $student->selected_subject =
                        StudentSkillSubject::where(
                            'academic_year_id',
                            $academicYear->id
                        )
                        ->where(
                            'student_id',
                            $student->Studentid
                        )
                        ->value('subject_id');
                }
            }
        }

        return view(
            'student-skill-subjects.index',
            compact(
                'academicYears',
                'standards',
                'divisions',
                'students',
                'skillSubjects'
            )
        );
    }

    public function save(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required'
        ]);

        $academicYearId =
            $request->academic_year_id;

        foreach (
            $request->skill_subject ?? []
            as $studentId => $subjectId
        ) {

            if (!$subjectId) {
                continue;
            }

            StudentSkillSubject::updateOrCreate(
                [
                    'academic_year_id' => $academicYearId,
                    'student_id'       => $studentId
                ],
                [
                    'subject_id' => $subjectId,
                    'updated_by' => Auth::id()
                ]
            );
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'Skill Subject Allocation Saved Successfully'
            );
    }
}