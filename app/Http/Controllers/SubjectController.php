<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Subject;
use App\Models\Standard;
use App\Models\StandardWiseSubject;

class SubjectController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = StandardWiseSubject::with([
            'standard',
            'subject',
        ]);

        if ($request->filled('standard_id')) {
            $query->where(
                'standard_id',
                $request->standard_id
            );
        }

        $subjects = $query
            ->orderBy('standard_id')
            ->orderBy('sort_order')
            ->get();

        $standards = Standard::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        return view(
            'subjects.index',
            compact(
                'subjects',
                'standards'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

 public function create()
{
    $standards = Standard::where('is_active', 1)
        ->orderBy('display_order')
        ->get();

    $masterSubjects = Subject::where('is_active', 1)
        ->orderBy('subject_name')
        ->get();

    $nextSortOrder =
        (StandardWiseSubject::max('sort_order') ?? 0) + 1;

    return view(
        'subjects.create',
        compact(
            'standards',
            'masterSubjects',
            'nextSortOrder'
        )
    );
}

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

public function store(Request $request)
{
    $request->validate([
        'standard_id' => [
            'required',
            'exists:standards,id',
        ],

        'subject_id' => [
            'required',
            'exists:subjects,id',
        ],

        'sort_order' => [
            'nullable',
            'integer',
            'min:0',
        ],
    ]);

    DB::transaction(function () use ($request) {

        $standard = Standard::findOrFail(
            $request->standard_id
        );

        $subject = Subject::findOrFail(
            $request->subject_id
        );

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Mapping
        |--------------------------------------------------------------------------
        */

        $exists = StandardWiseSubject::where(
            'standard_id',
            $standard->id
        )
        ->where(
            'subject_id',
            $subject->id
        )
        ->exists();

        if ($exists) {

            throw new \Exception(
                'This subject is already mapped to the selected standard.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create Standard Wise Subject Mapping
        |--------------------------------------------------------------------------
        */

        StandardWiseSubject::create([

    'standard_id'   => $standard->id,

    'subject_id'    => $subject->id,

    'standard_name' => $standard->standard_name,

    'subject_name'  => $subject->subject_name,

    'sort_order'    => $request->sort_order ?? 0,

    'is_optional'   => $request->has('is_optional'),

    'is_active'     => $request->has('is_active'),
]);
    });

    return redirect()
        ->route(
            'subjects.index',
            [
                'standard_id' =>
                    $request->standard_id,
            ]
        )
        ->with(
            'success',
            'Subject Added Successfully'
        );
}

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(StandardWiseSubject $subject)
{
    $subject->load([
        'standard',
        'subject',
    ]);

    $standards = Standard::where('is_active', 1)
        ->orderBy('display_order')
        ->get();

    $masterSubjects = Subject::where('is_active', 1)
        ->orderBy('subject_name')
        ->get();

    return view(
        'subjects.edit',
        compact(
            'subject',
            'standards',
            'masterSubjects'
        )
    );
}


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
    Request $request,
    StandardWiseSubject $subject
) {
    $request->validate([
        'standard_id' => 'required|exists:standards,id',
        'subject_id'  => 'required|exists:subjects,id',
        'sort_order'  => 'nullable|integer|min:0',
    ]);

    DB::transaction(function () use (
        $request,
        $subject
    ) {

        $standard = Standard::findOrFail(
            $request->standard_id
        );

        $masterSubject = Subject::findOrFail(
            $request->subject_id
        );

        /*
        |--------------------------------------------------------------------------
        | Update Standard Wise Subject Only
        |--------------------------------------------------------------------------
        */

        $subject->update([

            'standard_id'   => $request->standard_id,

            'subject_id'    => $masterSubject->id,

            'standard_name' => $standard->standard_name,

            'subject_name'  => $masterSubject->subject_name,

            'sort_order'    => $request->sort_order ?? 0,

            'is_optional'   => $request->has('is_optional'),

            'is_active'     => $request->has('is_active'),

        ]);
    });

    return redirect()
        ->route(
            'subjects.index',
            [
                'standard_id' => $request->standard_id,
            ]
        )
        ->with(
            'success',
            'Subject Updated Successfully'
        );
}

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(
        StandardWiseSubject $subject
    ) {

        $standardId =
            $subject->standard_id;


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Delete ONLY the standard mapping here.
        |
        | Do NOT delete the master Subject.
        |
        | Because the same Subject may be used by:
        |
        | - another standard
        | - exam_subjects
        | - exam_master_subjects
        | - student marks
        | - result details
        | - reports
        |
        |--------------------------------------------------------------------------
        */

        $subject->delete();


        return redirect()
            ->route(
                'subjects.index',
                [
                    'standard_id' =>
                        $standardId,
                ]
            )
            ->with(
                'success',
                'Subject Mapping Deleted Successfully'
            );
    }
}