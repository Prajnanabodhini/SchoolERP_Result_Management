<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AcademicYear;
use App\Models\Standard;
use App\Models\Division;
use App\Models\Subject;
use App\Models\StudentSkillSubject;
use App\Helpers\StudentHelper;

class StudentSkillSubjectController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $standards     = Standard::where('is_active', 1)->orderBy('display_order')->get();
        $divisions     = Division::where('is_active', 1)->orderBy('division_name')->get();

        $students = collect();

        // Global fetch: Skill (2) + Co-Scholastic (3) Subjects
        $skillSubjects = Subject::whereIn('subject_type_id', [2, 3])
            ->where('is_active', 1)
            ->orderBy('subject_name')
            ->get();

        // Fetch students for MULTIPLE standards + divisions
        if (
            $request->filled('academic_year_id') &&
            $request->filled('standard_ids') &&
            $request->filled('division_ids')
        ) {
            $academicYear = AcademicYear::find($request->academic_year_id);

            if ($academicYear) {
                $yearId = $academicYear->old_year_id ?? substr($academicYear->year_name, 0, 4);

                // Build lookup maps for names
                $standardMap = $standards->pluck('standard_name', 'id');
                $divisionMap = $divisions->pluck('division_name', 'id');

                // Loop through every Standard × Division combination
                foreach ($request->standard_ids as $stdId) {
                    foreach ($request->division_ids as $divId) {
                        $classStudents = StudentHelper::getStudentsDirectERP(
                            $yearId,
                            $stdId,
                            $divId
                        );

                        // Attach class info + roll number logic is already inside helper
                        foreach ($classStudents as $s) {
                            $s->class_standard_id = $stdId;
                            $s->class_division_id = $divId;
                            $s->class_label = ($standardMap[$stdId] ?? '') . ' - ' . ($divisionMap[$divId] ?? '');
                        }

                        $students = $students->merge($classStudents);
                    }
                }

                // Fetch existing allocations in one query
                if ($students->isNotEmpty()) {
                    $studentIds = $students->pluck('Studentid')->toArray();

                    $allocations = StudentSkillSubject::where('academic_year_id', $academicYear->id)
                        ->whereIn('student_id', $studentIds)
                        ->pluck('subject_id', 'student_id');

                    foreach ($students as $student) {
                        $student->selected_subject = $allocations[$student->Studentid] ?? null;
                    }
                }
            }
        }

        return view('student-skill-subjects.index', compact(
            'academicYears', 'standards', 'divisions', 'students', 'skillSubjects'
        ));
    }

    public function save(Request $request)
    {
        $request->validate(['academic_year_id' => 'required']);
        $academicYearId = $request->academic_year_id;

        foreach ($request->skill_subject ?? [] as $studentId => $subjectId) {
            if (!$subjectId) continue;

            StudentSkillSubject::updateOrCreate(
                ['academic_year_id' => $academicYearId, 'student_id' => $studentId],
                ['subject_id' => $subjectId, 'updated_by' => Auth::id()]
            );
        }

        return redirect()->back()->with('success', 'Skill Subject Allocation Saved Successfully');
    }

    /**
     * BULK ALLOCATION: Assign one skill subject to ALL loaded students in one click.
     */
    public function bulkAllocate(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'subject_id'       => 'required',
            'student_ids'      => 'required|array',
        ]);

        $academicYearId = $request->academic_year_id;
        $subjectId      = $request->subject_id;
        $count          = 0;

        foreach ($request->student_ids as $studentId) {
            StudentSkillSubject::updateOrCreate(
                ['academic_year_id' => $academicYearId, 'student_id' => $studentId],
                ['subject_id' => $subjectId, 'updated_by' => Auth::id()]
            );
            $count++;
        }

        return redirect()->back()->with('success', "{$count} students allocated successfully.");
    }
}