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
            'subject.subjectType',
        ]);

        if ($request->filled('standard_id')) {

            $query->where(
                'standard_id',
                (int) $request->standard_id
            );
        }

        $subjects = $query
            ->orderBy('standard_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $standards = Standard::where(
            'is_active',
            1
        )
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
        $standards = Standard::where(
            'is_active',
            1
        )
        ->orderBy('display_order')
        ->get();

        $subjectTypes = DB::table(
            'subject_types'
        )
        ->where(
            'is_active',
            1
        )
        ->orderBy('id')
        ->get();

        $nextSortOrder =
            (
                StandardWiseSubject::max(
                    'sort_order'
                ) ?? 0
            ) + 1;

        return view(
            'subjects.create',
            compact(
                'standards',
                'subjectTypes',
                'nextSortOrder'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FIND EXISTING MASTER SUBJECT
    |--------------------------------------------------------------------------
    |
    | The subjects table is MASTER data.
    |
    | Same subject can be used by multiple standards.
    |
    | We identify the master subject using:
    |
    | subject_code
    | section_id
    | subject_type_id
    |
    | This corresponds to the database unique key:
    |
    | uk_subject_section_type
    |
    |--------------------------------------------------------------------------
    */

    private function findExistingMasterSubject(
        string $subjectCode,
        int $sectionId,
        int $subjectTypeId
    ) {

        return Subject::where(
            'subject_code',
            strtoupper(trim($subjectCode))
        )
        ->where(
            'section_id',
            $sectionId
        )
        ->where(
            'subject_type_id',
            $subjectTypeId
        )
        ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK STANDARD MAPPING
    |--------------------------------------------------------------------------
    */

    private function standardAlreadyHasSubject(
        int $standardId,
        int $subjectId,
        ?int $ignoreMappingId = null
    ): bool {

        $query =
            StandardWiseSubject::where(
                'standard_id',
                $standardId
            )
            ->where(
                'subject_id',
                $subjectId
            );

        if ($ignoreMappingId) {

            $query->where(
                'id',
                '!=',
                $ignoreMappingId
            );
        }

        return $query->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | 1. Find existing master Subject.
    | 2. Create master only if it does not exist.
    | 3. Create a new StandardWiseSubject mapping.
    |
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'standard_id' => [
                'required',
                'integer',
                'exists:standards,id',
            ],

            'subject_name' => [
                'required',
                'string',
                'max:255',
            ],

            'subject_code' => [
                'required',
                'string',
                'max:50',
            ],

            'short_name' => [
                'nullable',
                'string',
                'max:20',
            ],

            'subject_type_id' => [
                'required',
                'integer',
                'exists:subject_types,id',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_optional' => [
                'nullable',
            ],

            'is_active' => [
                'nullable',
            ],
        ]);


        try {

            DB::transaction(function () use ($request) {

                /*
                |--------------------------------------------------------------------------
                | STANDARD
                |--------------------------------------------------------------------------
                */

                $standard =
                    Standard::findOrFail(
                        (int) $request->standard_id
                    );


                /*
                |--------------------------------------------------------------------------
                | NORMALIZE
                |--------------------------------------------------------------------------
                */

                $subjectName =
                    trim(
                        (string) $request->subject_name
                    );


                $subjectCode =
                    strtoupper(
                        trim(
                            (string) $request->subject_code
                        )
                    );


                $shortName =
                    $request->filled('short_name')
                        ? trim(
                            (string) $request->short_name
                        )
                        : null;


                $subjectTypeId =
                    (int) $request->subject_type_id;


                $sortOrder =
                    (int)(
                        $request->sort_order ?? 0
                    );


                $isOptional =
                    $request->has('is_optional')
                        ? 1
                        : 0;


                $isActive =
                    $request->has('is_active')
                        ? 1
                        : 0;


                /*
                |--------------------------------------------------------------------------
                | SECTION
                |--------------------------------------------------------------------------
                */

                $sectionId =
                    (int)$standard->section_id;


                /*
                |--------------------------------------------------------------------------
                | FIND EXISTING MASTER SUBJECT
                |--------------------------------------------------------------------------
                */

                $masterSubject =
                    $this->findExistingMasterSubject(
                        $subjectCode,
                        $sectionId,
                        $subjectTypeId
                    );


                /*
                |--------------------------------------------------------------------------
                | EXISTING MASTER FOUND
                |--------------------------------------------------------------------------
                */

                if ($masterSubject) {

                    /*
                    |----------------------------------------------------------------------
                    | If the same Standard already has this master subject,
                    | do not create another mapping.
                    |----------------------------------------------------------------------

                    */

                    $mappingExists =
                        $this->standardAlreadyHasSubject(
                            $standard->id,
                            $masterSubject->id
                        );


                    if ($mappingExists) {

                        throw new \Exception(
                            'This subject is already mapped to the selected Standard.'
                        );
                    }


                    /*
                    |----------------------------------------------------------------------
                    | DO NOT UPDATE MASTER SUBJECT HERE
                    |--------------------------------------------------------------------------
                    |
                    | The master subject is shared by multiple standards.
                    |
                    | Changing its name/code here could affect every standard.
                    |
                    */

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | CREATE NEW MASTER SUBJECT
                    |--------------------------------------------------------------------------
                    */

                    $masterSubject =
                        Subject::create([

                            'subject_name' =>
                                $subjectName,

                            'subject_code' =>
                                $subjectCode,

                            'short_name' =>
                                $shortName,

                            'section_id' =>
                                $sectionId,

                            'subject_type_id' =>
                                $subjectTypeId,

                            'display_order' =>
                                $sortOrder,

                            'is_active' =>
                                $isActive,
                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | VALID MASTER ID
                |--------------------------------------------------------------------------
                */

                if (
                    !$masterSubject ||
                    !$masterSubject->id
                ) {

                    throw new \Exception(
                        'Subject master could not be created or located.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | CREATE STANDARD-WISE MAPPING
                |--------------------------------------------------------------------------
                */

                StandardWiseSubject::create([

                    'standard_id' =>
                        $standard->id,

                    'subject_id' =>
                        $masterSubject->id,

                    'standard_name' =>
                        $standard->standard_name,

                    /*
                    | Use master subject name so that
                    | all mappings remain consistent.
                    */

                    'subject_name' =>
                        $masterSubject->subject_name,

                    'sort_order' =>
                        $sortOrder,

                    'is_optional' =>
                        $isOptional,

                    'is_active' =>
                        $isActive,
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
                    'Subject mapped successfully to the selected Standard.'
                );

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        StandardWiseSubject $subject
    ) {

        $subject->load([
            'standard',
            'subject',
            'subject.subjectType',
        ]);


        $standards =
            Standard::where(
                'is_active',
                1
            )
            ->orderBy(
                'display_order'
            )
            ->get();


        $subjectTypes =
            DB::table(
                'subject_types'
            )
            ->where(
                'is_active',
                1
            )
            ->orderBy(
                'id'
            )
            ->get();


        return view(
            'subjects.edit',
            compact(
                'subject',
                'standards',
                'subjectTypes'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | The StandardWiseSubject mapping is what is being edited.
    |
    | We do NOT blindly update the master Subject because that master
    | may be shared by many standards.
    |
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        StandardWiseSubject $subject
    ) {

        $request->validate([

            'standard_id' => [
                'required',
                'integer',
                'exists:standards,id',
            ],

            'subject_name' => [
                'required',
                'string',
                'max:255',
            ],

            'subject_code' => [
                'required',
                'string',
                'max:50',
            ],

            'short_name' => [
                'nullable',
                'string',
                'max:20',
            ],

            'subject_type_id' => [
                'required',
                'integer',
                'exists:subject_types,id',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_optional' => [
                'nullable',
            ],

            'is_active' => [
                'nullable',
            ],
        ]);


        try {

            DB::transaction(function () use (
                $request,
                $subject
            ) {

                /*
                |--------------------------------------------------------------------------
                | CURRENT MASTER
                |--------------------------------------------------------------------------
                */

                if (
                    !$subject->subject_id
                ) {

                    throw new \Exception(
                        'This mapping does not have a valid Subject ID.'
                    );
                }


                $currentMaster =
                    Subject::findOrFail(
                        (int)$subject->subject_id
                    );


                /*
                |--------------------------------------------------------------------------
                | NEW STANDARD
                |--------------------------------------------------------------------------
                */

                $standard =
                    Standard::findOrFail(
                        (int)$request->standard_id
                    );


                /*
                |--------------------------------------------------------------------------
                | NORMALIZE
                |--------------------------------------------------------------------------
                */

                $subjectName =
                    trim(
                        (string)$request->subject_name
                    );


                $subjectCode =
                    strtoupper(
                        trim(
                            (string)$request->subject_code
                        )
                    );


                $shortName =
                    $request->filled('short_name')
                        ? trim(
                            (string)$request->short_name
                        )
                        : null;


                $subjectTypeId =
                    (int)$request->subject_type_id;


                $sortOrder =
                    (int)(
                        $request->sort_order ?? 0
                    );


                $isOptional =
                    $request->has('is_optional')
                        ? 1
                        : 0;


                $isActive =
                    $request->has('is_active')
                        ? 1
                        : 0;


                $sectionId =
                    (int)$standard->section_id;


                /*
                |--------------------------------------------------------------------------
                | CHECK EXISTING MASTER SUBJECT
                |--------------------------------------------------------------------------
                */

                $masterSubject =
                    $this->findExistingMasterSubject(
                        $subjectCode,
                        $sectionId,
                        $subjectTypeId
                    );


                /*
                |--------------------------------------------------------------------------
                | DESTINATION STANDARD ALREADY HAS SAME SUBJECT
                |--------------------------------------------------------------------------
                */

                if (
                    $masterSubject &&
                    $this->standardAlreadyHasSubject(
                        $standard->id,
                        $masterSubject->id,
                        $subject->id
                    )
                ) {

                    throw new \Exception(
                        'This subject is already mapped to the selected Standard.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | CASE 1:
                | Same master subject
                |--------------------------------------------------------------------------
                */

                if (
                    $masterSubject &&
                    (int)$masterSubject->id ===
                    (int)$currentMaster->id
                ) {

                    /*
                    |----------------------------------------------------------------------
                    | We are editing the existing master subject itself.
                    |
                    | Since this master can be shared by multiple standards,
                    | update it consistently.
                    |----------------------------------------------------------------------

                    */

                    $masterSubject->update([

                        'subject_name' =>
                            $subjectName,

                        'subject_code' =>
                            $subjectCode,

                        'short_name' =>
                            $shortName,

                        'section_id' =>
                            $sectionId,

                        'subject_type_id' =>
                            $subjectTypeId,

                        'display_order' =>
                            $sortOrder,

                        'is_active' =>
                            $isActive,
                    ]);


                    $finalMasterSubject =
                        $masterSubject;

                }

                /*
                |--------------------------------------------------------------------------
                | CASE 2:
                | Another master subject already exists
                |--------------------------------------------------------------------------
                */

                elseif ($masterSubject) {

                    /*
                    |----------------------------------------------------------------------
                    | Reuse the existing master subject.
                    |----------------------------------------------------------------------

                    */

                    $finalMasterSubject =
                        $masterSubject;

                }

                /*
                |--------------------------------------------------------------------------
                | CASE 3:
                | No existing master subject
                |--------------------------------------------------------------------------
                */

                else {

                    /*
                    |----------------------------------------------------------------------
                    | Reuse current master if it is safe
                    |----------------------------------------------------------------------

                    */

                    $otherUsageExists =
                        StandardWiseSubject::where(
                            'subject_id',
                            $currentMaster->id
                        )
                        ->where(
                            'id',
                            '!=',
                            $subject->id
                        )
                        ->exists();


                    if (
                        !$otherUsageExists
                    ) {

                        /*
                        |------------------------------------------------------------------
                        | Current master is used nowhere else.
                        | We may safely update it.
                        |------------------------------------------------------------------
                        */

                        $currentMaster->update([

                            'subject_name' =>
                                $subjectName,

                            'subject_code' =>
                                $subjectCode,

                            'short_name' =>
                                $shortName,

                            'section_id' =>
                                $sectionId,

                            'subject_type_id' =>
                                $subjectTypeId,

                            'display_order' =>
                                $sortOrder,

                            'is_active' =>
                                $isActive,
                        ]);


                        $finalMasterSubject =
                            $currentMaster;

                    } else {

                        /*
                        |------------------------------------------------------------------
                        | Current master is shared.
                        |
                        | Do not change it silently.
                        | Create a new master instead.
                        |------------------------------------------------------------------
                        */

                        $finalMasterSubject =
                            Subject::create([

                                'subject_name' =>
                                    $subjectName,

                                'subject_code' =>
                                    $subjectCode,

                                'short_name' =>
                                    $shortName,

                                'section_id' =>
                                    $sectionId,

                                'subject_type_id' =>
                                    $subjectTypeId,

                                'display_order' =>
                                    $sortOrder,

                                'is_active' =>
                                    $isActive,
                            ]);
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | UPDATE STANDARD MAPPING
                |--------------------------------------------------------------------------
                */

                $subject->update([

                    'standard_id' =>
                        $standard->id,

                    'subject_id' =>
                        $finalMasterSubject->id,

                    'standard_name' =>
                        $standard->standard_name,

                    'subject_name' =>
                        $finalMasterSubject->subject_name,

                    'sort_order' =>
                        $sortOrder,

                    'is_optional' =>
                        $isOptional,

                    'is_active' =>
                        $isActive,
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
                    'Subject updated successfully.'
                );

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    |
    | Deletes only the StandardWiseSubject mapping.
    |
    | Master Subject is deactivated only when no active mappings remain.
    |
    |--------------------------------------------------------------------------
    */

    public function destroy(
        StandardWiseSubject $subject
    ) {

        try {

            DB::transaction(function () use (
                $subject
            ) {

                $standardId =
                    $subject->standard_id;


                $subjectId =
                    $subject->subject_id;


                /*
                |--------------------------------------------------------------------------
                | DELETE ONLY MAPPING
                |--------------------------------------------------------------------------
                */

                $subject->delete();


                /*
                |--------------------------------------------------------------------------
                | CHECK REMAINING ACTIVE MAPPINGS
                |--------------------------------------------------------------------------
                */

                if ($subjectId) {

                    $stillMapped =
                        StandardWiseSubject::where(
                            'subject_id',
                            $subjectId
                        )
                        ->where(
                            'is_active',
                            1
                        )
                        ->exists();


                    /*
                    |--------------------------------------------------------------------------
                    | DEACTIVATE MASTER ONLY IF UNUSED
                    |--------------------------------------------------------------------------
                    */

                    if (!$stillMapped) {

                        Subject::where(
                            'id',
                            $subjectId
                        )
                        ->update([

                            'is_active' =>
                                0,

                            'updated_at' =>
                                now(),
                        ]);
                    }
                }
            });


            return redirect()
                ->route(
                    'subjects.index',
                    [
                        'standard_id' =>
                            request(
                                'standard_id'
                            ),
                    ]
                )
                ->with(
                    'success',
                    'Subject Mapping deleted successfully.'
                );

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}