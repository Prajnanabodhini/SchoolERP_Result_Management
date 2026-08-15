<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Standard;
use App\Models\Subject;
use App\Models\StandardSubject;

class StandardSubjectController extends Controller
{
    public function index(Request $request)
    {
        $standards = Standard::orderBy('display_order')->get();

        $selectedStandard = $request->standard_id;

        $allocatedSubjects = [];

        $academicSubjects = collect();

        $skillSubjects = collect();

        $coScholasticSubjects = collect();

        if ($selectedStandard) {

            $standard = Standard::find($selectedStandard);

            if ($standard) {

                // Academic Subjects
                $academicSubjects = Subject::where('subject_type_id', 1)
                    ->where('is_active', 1)
                    ->where(function ($q) use ($standard) {

                        $q->where('section_id', $standard->section_id)

                          // COMPUTER / ROBOTICS / PE
                          ->orWhereIn('id', [57, 58, 63]);

                    })
                    ->orderBy('subject_name')
                    ->get();

                // Skill Subjects
                $skillSubjects = Subject::selectRaw('MIN(id) as id, subject_name')
    ->where('subject_type_id', 2)
    ->where('is_active', 1)
    ->groupBy('subject_name')
    ->orderBy('subject_name')
    ->get();

                // Co-Scholastic Subjects
                $coScholasticSubjects = Subject::selectRaw('MIN(id) as id, subject_name')
    ->where('subject_type_id', 3)
    ->where('is_active', 1)
    ->groupBy('subject_name')
    ->orderBy('subject_name')
    ->get();
                $allocatedSubjects = StandardSubject::where(
                        'standard_id',
                        $selectedStandard
                    )
                    ->pluck('subject_id')
                    ->toArray();
            }
        }

        return view(
            'standard-subjects.index',
            compact(
                'standards',
                'selectedStandard',
                'allocatedSubjects',
                'academicSubjects',
                'skillSubjects',
                'coScholasticSubjects'
            )
        );
    }

    public function save(Request $request)
    {
        StandardSubject::where(
            'standard_id',
            $request->standard_id
        )->delete();

        if ($request->subjects) {

            foreach ($request->subjects as $subjectId) {

                StandardSubject::create([
                    'standard_id' => $request->standard_id,
                    'subject_id'  => $subjectId,
                ]);
            }
        }

        return redirect()
            ->route(
                'standard-subject-allocation.index',
                [
                    'standard_id' => $request->standard_id
                ]
            )
            ->with(
                'success',
                'Subject Allocation Saved Successfully.'
            );
    }
}