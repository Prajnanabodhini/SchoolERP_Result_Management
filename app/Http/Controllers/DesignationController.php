<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DesignationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $designations = Designation::query()
            ->with('section')
            ->orderBy('designation_name')
            ->get();

        return view(
            'designations.index',
            compact('designations')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $sections = Section::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        return view(
            'designations.create',
            compact('sections')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([

            'designation_name' => [
                'required',
                'string',
                'max:100',
            ],

            'designation_code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                'unique:designations,designation_code',
            ],

            'section_id' => [
                'required',
                'exists:sections,id',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);


        Designation::create([

            'designation_name' =>
                trim(
                    $validated['designation_name']
                ),

            'designation_code' =>
                strtoupper(
                    trim(
                        $validated['designation_code']
                    )
                ),

            'section_id' =>
                (int) $validated['section_id'],

            'description' =>
                $validated['description']
                ?? null,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),
        ]);


        return redirect()
            ->route('designations.index')
            ->with(
                'success',
                'Designation created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        Designation $designation
    ) {
        $sections = Section::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        return view(
            'designations.edit',
            compact(
                'designation',
                'sections'
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
        Designation $designation
    ) {

        $validated = $request->validate([

            'designation_name' => [
                'required',
                'string',
                'max:100',
            ],

            'designation_code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',

                Rule::unique(
                    'designations',
                    'designation_code'
                )->ignore(
                    $designation->id
                ),
            ],

            'section_id' => [
                'required',
                'exists:sections,id',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);


        $designation->update([

            'designation_name' =>
                trim(
                    $validated['designation_name']
                ),

            'designation_code' =>
                strtoupper(
                    trim(
                        $validated['designation_code']
                    )
                ),

            'section_id' =>
                (int) $validated['section_id'],

            'description' =>
                $validated['description']
                ?? null,

            'is_active' =>
                $request->boolean(
                    'is_active'
                ),
        ]);


        return redirect()
            ->route('designations.index')
            ->with(
                'success',
                'Designation updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Designation $designation
    ) {

        if (
            $designation
                ->userDesignations()
                ->exists()
        ) {

            return redirect()
                ->route('designations.index')
                ->with(
                    'error',
                    'This designation is already assigned to a user and cannot be deleted.'
                );
        }


        $designation->delete();


        return redirect()
            ->route('designations.index')
            ->with(
                'success',
                'Designation deleted successfully.'
            );
    }
}