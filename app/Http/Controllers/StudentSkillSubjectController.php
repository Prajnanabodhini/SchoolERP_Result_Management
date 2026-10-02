<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;
use App\Models\Standard;
use App\Models\Division;
use App\Models\Subject;
use App\Models\StudentSkillSubject;
use App\Helpers\StudentHelper;

class StudentSkillSubjectController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX — load the filter form + students (if any filters applied)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $standards     = Standard::where('is_active', 1)->orderBy('display_order')->get();
        $divisions     = Division::where('is_active', 1)->orderBy('division_name')->get();

        $students = collect();

        // Skill (2) + Co-Scholastic (3) subjects
        $skillSubjects = Subject::whereIn('subject_type_id', [2, 3])
            ->where('is_active', 1)
            ->orderBy('subject_name')
            ->get();

        if (
            $request->filled('academic_year_id') &&
            $request->filled('standard_ids') &&
            $request->filled('division_ids')
        ) {
            $academicYear = AcademicYear::find($request->academic_year_id);

            if ($academicYear) {

                $yearId = $academicYear->old_year_id
                    ?? substr($academicYear->year_name, 0, 4);

                $standardMap = $standards->pluck('standard_name', 'id');
                $divisionMap = $divisions->pluck('division_name', 'id');

                foreach ($request->standard_ids as $stdId) {
                    foreach ($request->division_ids as $divId) {

                        $classStudents = StudentHelper::getStudentsDirectERP(
                            $yearId,
                            $stdId,
                            $divId
                        );

                        foreach ($classStudents as $s) {
                            $s->class_standard_id = $stdId;
                            $s->class_division_id = $divId;
                            $s->class_label       = ($standardMap[$stdId] ?? '')
                                                  . ' - '
                                                  . ($divisionMap[$divId] ?? '');
                        }

                        $students = $students->merge($classStudents);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Attach ALL allocated subjects per student (array), not just one.
                |--------------------------------------------------------------------------
                */

                if ($students->isNotEmpty()) {

                    $studentIds = $students->pluck('Studentid')->toArray();

                    $allocations = StudentSkillSubject::where('academic_year_id', $academicYear->id)
                        ->whereIn('student_id', $studentIds)
                        ->get()
                        ->groupBy('student_id')
                        ->map(fn ($rows) => $rows->pluck('subject_id')->map(fn ($v) => (int) $v)->toArray());

                    foreach ($students as $student) {
                        $student->selected_subjects =
                            $allocations[$student->Studentid] ?? [];
                    }
                }
            }
        }

        return view('student-skill-subjects.index', compact(
            'academicYears',
            'standards',
            'divisions',
            'students',
            'skillSubjects'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE — individual allocation.
    | Receives: skill_subjects[student_id][] = [subject_id, subject_id, ...]
    | Adds missing subjects, removes unchecked ones.
    |--------------------------------------------------------------------------
    */

    public function save(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
        ]);

        $academicYearId = $request->academic_year_id;

        $matrix = $request->input('skill_subjects', []);

        if (empty($matrix) || !is_array($matrix)) {
            return redirect()->back()->with('success', 'No changes to save.');
        }

        DB::transaction(function () use ($matrix, $academicYearId) {

            foreach ($matrix as $studentId => $subjectIds) {

                // Normalise: single value or array, filter empty
                $subjectIds = array_filter(
                    array_map('intval', (array) $subjectIds),
                    fn ($v) => $v > 0
                );

                $subjectIds = array_unique($subjectIds);

                // Existing subject ids for this student
                $existing = StudentSkillSubject::where('academic_year_id', $academicYearId)
                    ->where('student_id', $studentId)
                    ->pluck('subject_id')
                    ->map(fn ($v) => (int) $v)
                    ->toArray();

                // Compute adds / removes
                $toAdd    = array_diff($subjectIds, $existing);
                $toRemove = array_diff($existing, $subjectIds);

                // Add new rows
                foreach ($toAdd as $subjectId) {
                    StudentSkillSubject::create([
                        'academic_year_id' => $academicYearId,
                        'student_id'       => $studentId,
                        'subject_id'       => $subjectId,
                        'updated_by'       => Auth::id(),
                    ]);
                }

                // Remove unchecked ones
                if (!empty($toRemove)) {
                    StudentSkillSubject::where('academic_year_id', $academicYearId)
                        ->where('student_id', $studentId)
                        ->whereIn('subject_id', $toRemove)
                        ->delete();
                }
            }
        });

        return redirect()->back()->with('success', 'Skill Subject Allocations Saved Successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | BULK ALLOCATE — add one subject to all loaded students.
    | Does NOT remove their existing subjects.
    |--------------------------------------------------------------------------
    */

    public function bulkAllocate(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'subject_id'       => 'required|exists:subjects,id',
            'student_ids'      => 'required|array|min:1',
            'student_ids.*'    => 'required',
        ]);

        $academicYearId = $request->academic_year_id;
        $subjectId      = (int) $request->subject_id;
        $added          = 0;
        $skipped        = 0;

        DB::transaction(function () use ($request, $academicYearId, $subjectId, &$added, &$skipped) {

            foreach ($request->student_ids as $studentId) {

                $row = StudentSkillSubject::firstOrCreate(
                    [
                        'academic_year_id' => $academicYearId,
                        'student_id'       => $studentId,
                        'subject_id'       => $subjectId,
                    ],
                    [
                        'updated_by' => Auth::id(),
                    ]
                );

                if ($row->wasRecentlyCreated) {
                    $added++;
                } else {
                    $skipped++;
                }
            }
        });

        $message = "{$added} new allocation(s) added. Existing subjects kept.";

        if ($skipped > 0) {
            $message .= " {$skipped} student(s) already had this subject.";
        }

        return redirect()->back()->with('success', $message);
    }

    /*
    |--------------------------------------------------------------------------
    | BULK REMOVE — remove one subject from all loaded students.
    | Leaves their other subjects intact.
    |--------------------------------------------------------------------------
    */

    public function bulkRemove(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'subject_id'       => 'required|exists:subjects,id',
            'student_ids'      => 'required|array|min:1',
        ]);

        $deleted = StudentSkillSubject::where('academic_year_id', $request->academic_year_id)
            ->where('subject_id', $request->subject_id)
            ->whereIn('student_id', $request->student_ids)
            ->delete();

        return redirect()->back()->with(
            'success',
            "{$deleted} allocation(s) removed."
        );
    }
}