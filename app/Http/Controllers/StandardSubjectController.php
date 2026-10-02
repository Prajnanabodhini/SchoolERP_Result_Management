<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Standard;
use App\Models\Subject;
use App\Models\StandardWiseSubject;
use App\Models\StandardSubject;

class StandardSubjectController extends Controller
{
    public function index(Request $request)
    {
        $standards        = Standard::orderBy('display_order')->get();
        $selectedStandard = $request->standard_id;

        $allocatedSubjects = [];
        $academicSubjects  = collect();
        $skillSubjects     = collect();

        if ($selectedStandard) {

            $standard = Standard::find($selectedStandard);

            if ($standard) {

                /*
                |--------------------------------------------------------------------------
                | 1. Academic Subjects
                |--------------------------------------------------------------------------
                |
                | Pull from the SAME mapping table used by the Subject Master page
                | (standard_wise_subjects). This is the source that shows
                | "which subjects belong to which standard".
                |
                | We then resolve the actual Subject row for each mapping so the
                | view can render subject_name, subject_code, etc.
                |
                */

                $academicSubjects = StandardWiseSubject::with('subject')
                    ->where('standard_id', $selectedStandard)
                    ->whereHas('subject', function ($q) {
                        $q->where('subject_type_id', 1)   // Academic
                          ->where('is_active', 1);
                    })
                    ->get()
                    ->map(function ($mapping) {
                        // Return a lightweight object with the same shape
                        // the view expects (id, subject_name, subject_code)
                        $s = $mapping->subject;
                        return (object) [
                            'id'           => $s->id,
                            'subject_name' => $s->subject_name,
                            'subject_code' => $s->subject_code,
                            'short_name'   => $s->short_name,
                        ];
                    })
                    ->sortBy('subject_name')
                    ->values();

                /*
                |--------------------------------------------------------------------------
                | 2. Skill + Co-Scholastic Subjects (Merged, deduped by name)
                |--------------------------------------------------------------------------
                |
                | Same as before. If you want skill subjects scoped to this
                | standard too, do the same StandardWiseSubject lookup with
                | subject_type_id in [2,3].
                |
                */

                $skillSubjects = Subject::selectRaw('MIN(id) as id, subject_name')
                    ->whereIn('subject_type_id', [2, 3])
                    ->where('is_active', 1)
                    ->groupBy('subject_name')
                    ->orderBy('subject_name')
                    ->get();

                /*
                |--------------------------------------------------------------------------
                | 3. Already Allocated Subjects (for checkbox pre-fill)
                |--------------------------------------------------------------------------
                */

                $allocatedSubjects = StandardSubject::where('standard_id', $selectedStandard)
                    ->pluck('subject_id')
                    ->toArray();
            }
        }

        return view('standard-subjects.index', compact(
            'standards',
            'selectedStandard',
            'allocatedSubjects',
            'academicSubjects',
            'skillSubjects'
        ));
    }

    public function save(Request $request)
    {
        StandardSubject::where('standard_id', $request->standard_id)->delete();

        if ($request->subjects) {
            foreach ($request->subjects as $subjectId) {
                StandardSubject::create([
                    'standard_id' => $request->standard_id,
                    'subject_id'  => $subjectId,
                ]);
            }
        }

        return redirect()
            ->route('standard-subject-allocation.index', [
                'standard_id' => $request->standard_id,
            ])
            ->with('success', 'Subject Allocation Saved Successfully.');
    }
}