<?php

namespace App\Http\Controllers;

use App\Models\UserDesignation;
use App\Models\Designation;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\Standard;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserDesignationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $assignments =
            UserDesignation::query()
                ->with([
                    'user',
                    'designation',
                    'academicYear',
                    'standard',
                    'division',
                ])
                ->orderByDesc('id')
                ->get();

        return view(
            'user-designations.index',
            compact(
                'assignments'
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
        $users =
            User::query()
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'email',
                ]);


        $designations =
            Designation::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderBy(
                    'designation_name'
                )
                ->get();


        $academicYears =
            AcademicYear::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderByDesc('id')
                ->get();


        $standards =
            Standard::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderBy(
                    'display_order'
                )
                ->get();


        $divisions =
            Division::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderBy(
                    'division_name'
                )
                ->get();


        return view(
            'user-designations.create',
            compact(
                'users',
                'designations',
                'academicYears',
                'standards',
                'divisions'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {

        $validated =
            $request->validate([

                'user_id' => [
                    'required',
                    'integer',
                    'exists:users,id',
                ],

                'designation_id' => [
                    'required',
                    'integer',
                    'exists:designations,id',
                ],

                'academic_year_id' => [
                    'nullable',
                    'integer',
                    'exists:academic_years,id',
                ],

                'standard_id' => [
                    'nullable',
                    'integer',
                    'exists:standards,id',
                ],

                'division_id' => [
                    'nullable',
                    'integer',
                    'exists:divisions,id',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | VALIDATE CLASS TEACHER
        |--------------------------------------------------------------------------
        |
        | A Class Teacher assignment requires:
        |
        | Academic Year
        | Standard
        | Division
        |
        */

        $designation =
            Designation::find(
                $validated['designation_id']
            );


        $designationCode =
            strtoupper(
                trim(
                    (string)(
                        $designation->designation_code
                        ?? ''
                    )
                )
            );


        if (
            $designationCode === 'CLASS_TEACHER'
        ) {

            if (
                empty(
                    $validated['academic_year_id']
                ) ||
                empty(
                    $validated['standard_id']
                ) ||
                empty(
                    $validated['division_id']
                )
            ) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Academic Year, Standard and Division are required for Class Teacher assignment.'
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PRINCIPAL
        |--------------------------------------------------------------------------
        |
        | Principal is school-level, so standard/division are not allowed.
        |
        */

        if (
            $designationCode === 'PRINCIPAL'
        ) {

            $validated['standard_id'] =
                null;

            $validated['division_id'] =
                null;
        }


        /*
        |--------------------------------------------------------------------------
        | DUPLICATE CHECK
        |--------------------------------------------------------------------------
        */

        $duplicateExists =
            UserDesignation::query()
                ->where(
                    'user_id',
                    $validated['user_id']
                )
                ->where(
                    'designation_id',
                    $validated['designation_id']
                )
                ->where(
                    'academic_year_id',
                    $validated['academic_year_id']
                    ?? null
                )
                ->where(
                    'standard_id',
                    $validated['standard_id']
                    ?? null
                )
                ->where(
                    'division_id',
                    $validated['division_id']
                    ?? null
                )
                ->exists();


        if ($duplicateExists) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'This designation assignment already exists.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | STORE
        |--------------------------------------------------------------------------
        */

        UserDesignation::create([

            'user_id' =>
                $validated['user_id'],

            'designation_id' =>
                $validated['designation_id'],

            'academic_year_id' =>
                $validated['academic_year_id']
                ?? null,

            'standard_id' =>
                $validated['standard_id']
                ?? null,

            'division_id' =>
                $validated['division_id']
                ?? null,
        ]);


        return redirect()
            ->route(
                'user-designations.index'
            )
            ->with(
                'success',
                'User designation assigned successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        UserDesignation $userDesignation
    ) {

        $users =
            User::query()
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'email',
                ]);


        $designations =
            Designation::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderBy(
                    'designation_name'
                )
                ->get();


        $academicYears =
            AcademicYear::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderByDesc('id')
                ->get();


        $standards =
            Standard::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderBy(
                    'display_order'
                )
                ->get();


        $divisions =
            Division::query()
                ->where(
                    'is_active',
                    1
                )
                ->orderBy(
                    'division_name'
                )
                ->get();


        return view(
            'user-designations.edit',
            compact(
                'userDesignation',
                'users',
                'designations',
                'academicYears',
                'standards',
                'divisions'
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
        UserDesignation $userDesignation
    ) {

        $validated =
            $request->validate([

                'user_id' => [
                    'required',
                    'integer',
                    'exists:users,id',
                ],

                'designation_id' => [
                    'required',
                    'integer',
                    'exists:designations,id',
                ],

                'academic_year_id' => [
                    'nullable',
                    'integer',
                    'exists:academic_years,id',
                ],

                'standard_id' => [
                    'nullable',
                    'integer',
                    'exists:standards,id',
                ],

                'division_id' => [
                    'nullable',
                    'integer',
                    'exists:divisions,id',
                ],
            ]);


        $designation =
            Designation::find(
                $validated['designation_id']
            );


        $designationCode =
            strtoupper(
                trim(
                    (string)(
                        $designation->designation_code
                        ?? ''
                    )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | CLASS TEACHER VALIDATION
        |--------------------------------------------------------------------------
        */

        if (
            $designationCode === 'CLASS_TEACHER'
        ) {

            if (
                empty(
                    $validated['academic_year_id']
                ) ||
                empty(
                    $validated['standard_id']
                ) ||
                empty(
                    $validated['division_id']
                )
            ) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Academic Year, Standard and Division are required for Class Teacher assignment.'
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PRINCIPAL
        |--------------------------------------------------------------------------
        */

        if (
            $designationCode === 'PRINCIPAL'
        ) {

            $validated['standard_id'] =
                null;

            $validated['division_id'] =
                null;
        }


        /*
        |--------------------------------------------------------------------------
        | DUPLICATE CHECK
        |--------------------------------------------------------------------------
        */

        $duplicateExists =
            UserDesignation::query()
                ->where(
                    'user_id',
                    $validated['user_id']
                )
                ->where(
                    'designation_id',
                    $validated['designation_id']
                )
                ->where(
                    'academic_year_id',
                    $validated['academic_year_id']
                    ?? null
                )
                ->where(
                    'standard_id',
                    $validated['standard_id']
                    ?? null
                )
                ->where(
                    'division_id',
                    $validated['division_id']
                    ?? null
                )
                ->where(
                    'id',
                    '!=',
                    $userDesignation->id
                )
                ->exists();


        if ($duplicateExists) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'This designation assignment already exists.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        $userDesignation->update([

            'user_id' =>
                $validated['user_id'],

            'designation_id' =>
                $validated['designation_id'],

            'academic_year_id' =>
                $validated['academic_year_id']
                ?? null,

            'standard_id' =>
                $validated['standard_id']
                ?? null,

            'division_id' =>
                $validated['division_id']
                ?? null,
        ]);


        return redirect()
            ->route(
                'user-designations.index'
            )
            ->with(
                'success',
                'User designation assignment updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        UserDesignation $userDesignation
    ) {

        $userDesignation->delete();


        return redirect()
            ->route(
                'user-designations.index'
            )
            ->with(
                'success',
                'User designation assignment deleted successfully.'
            );
    }
}