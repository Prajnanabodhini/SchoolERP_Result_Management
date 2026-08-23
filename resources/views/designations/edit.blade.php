<x-app-layout>

<div class="erp-page">

    <style>

        .designation-edit-page,
        .designation-edit-page * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif !important;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD
        |--------------------------------------------------------------------------
        */

        .designation-edit-card {
            width: 100%;
        }


        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        .designation-edit-title {
            margin: 0 0 15px 0;
            font-size: 20px;
            font-weight: 700;
            color: #1d4ed8;
        }


        /*
        |--------------------------------------------------------------------------
        | GRID
        |--------------------------------------------------------------------------
        */

        .designation-edit-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }


        .designation-edit-full {
            grid-column: 1 / -1;
        }


        /*
        |--------------------------------------------------------------------------
        | LABEL
        |--------------------------------------------------------------------------
        */

        .designation-edit-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }


        /*
        |--------------------------------------------------------------------------
        | INPUTS
        |--------------------------------------------------------------------------
        */

        .designation-edit-input,
        .designation-edit-textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            background: #ffffff;
            color: #111827;
            font-size: 13px;
            box-sizing: border-box;
        }


        .designation-edit-input {
            height: 34px;
            padding: 5px 8px;
        }


        .designation-edit-textarea {
            min-height: 78px;
            padding: 7px 8px;
            resize: vertical;
        }


        .designation-edit-input:focus,
        .designation-edit-textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 1px #2563eb;
        }


        /*
        |--------------------------------------------------------------------------
        | SELECT WRAPPER
        |--------------------------------------------------------------------------
        */

        .designation-edit-select-wrapper {
            position: relative;
            width: 100%;
        }


        /*
        |--------------------------------------------------------------------------
        | SELECT
        |--------------------------------------------------------------------------
        */

        .designation-edit-select {
            width: 100%;
            height: 34px;

            border: 1px solid #d1d5db;
            border-radius: 5px;

            padding:
                4px
                34px
                4px
                8px;

            font-size: 13px;

            color: #111827;
            background: #ffffff;

            cursor: pointer;

            outline: none;

            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }


        .designation-edit-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 1px #2563eb;
        }


        /*
        |--------------------------------------------------------------------------
        | DROPDOWN ARROW
        |--------------------------------------------------------------------------
        */

        .designation-edit-select-arrow {
            position: absolute;

            right: 11px;
            top: 50%;

            width: 0;
            height: 0;

            transform: translateY(-35%);

            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 6px solid #6b7280;

            pointer-events: none;
        }


        /*
        |--------------------------------------------------------------------------
        | CHECKBOX
        |--------------------------------------------------------------------------
        */

        .designation-edit-checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            font-size: 13px;
            font-weight: 600;

            color: #374151;

            cursor: pointer;
        }


        .designation-edit-checkbox {
            width: 16px;
            height: 16px;
            margin: 0;
            cursor: pointer;
        }


        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        .designation-edit-error-box {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;

            padding: 10px;

            border-radius: 5px;

            margin-bottom: 12px;

            font-size: 13px;
        }


        .designation-edit-field-error {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
        }


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        .designation-edit-success-box {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;

            padding: 10px;

            border-radius: 5px;

            margin-bottom: 12px;

            font-size: 13px;
            font-weight: 600;
        }


        /*
        |--------------------------------------------------------------------------
        | ERROR SESSION
        |--------------------------------------------------------------------------
        */

        .designation-edit-session-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;

            padding: 10px;

            border-radius: 5px;

            margin-bottom: 12px;

            font-size: 13px;
            font-weight: 600;
        }


        /*
        |--------------------------------------------------------------------------
        | BUTTONS
        |--------------------------------------------------------------------------
        */

        .designation-edit-actions {
            margin-top: 18px;

            display: flex;
            align-items: center;

            gap: 8px;

            flex-wrap: wrap;
        }


        .designation-edit-update-btn {
            min-width: 100px;
            white-space: nowrap;
        }


        .designation-edit-cancel-btn {
            min-width: 80px;

            background: #6b7280;
            color: #ffffff;

            white-space: nowrap;
            text-align: center;

            text-decoration: none;
        }


        .designation-edit-cancel-btn:hover {
            background: #4b5563;
            color: #ffffff;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 700px) {

            .designation-edit-grid {
                grid-template-columns: 1fr;
            }


            .designation-edit-full {
                grid-column: auto;
            }

        }

    </style>


    <div class="erp-card designation-edit-page designation-edit-card">


        {{-- =========================================================
             TITLE
        ========================================================== --}}

        <h2 class="designation-edit-title">
            EDIT DESIGNATION
        </h2>


        {{-- =========================================================
             VALIDATION ERRORS
        ========================================================== --}}

        @if($errors->any())

            <div class="designation-edit-error-box">

                <ul style="
                    margin:0;
                    padding-left:20px;
                ">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- =========================================================
             SUCCESS MESSAGE
        ========================================================== --}}

        @if(session('success'))

            <div class="designation-edit-success-box">

                {{ session('success') }}

            </div>

        @endif


        {{-- =========================================================
             ERROR MESSAGE
        ========================================================== --}}

        @if(session('error'))

            <div class="designation-edit-session-error">

                {{ session('error') }}

            </div>

        @endif


        {{-- =========================================================
             EDIT FORM
        ========================================================== --}}

        <form
            method="POST"
            action="{{ route(
                'designations.update',
                $designation
            ) }}"
        >

            @csrf

            @method('PUT')


            <div class="designation-edit-grid">


                {{-- =================================================
                     DESIGNATION NAME
                ================================================== --}}

                <div>

                    <label
                        for="designation_name"
                        class="designation-edit-label"
                    >
                        Designation Name
                    </label>


                    <input
                        type="text"
                        name="designation_name"
                        id="designation_name"
                        value="{{ old(
                            'designation_name',
                            $designation->designation_name
                        ) }}"
                        required
                        maxlength="100"
                        class="designation-edit-input"
                    >


                    @error('designation_name')

                        <div class="designation-edit-field-error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- =================================================
                     DESIGNATION CODE
                ================================================== --}}

                <div>

                    <label
                        for="designation_code"
                        class="designation-edit-label"
                    >
                        Designation Code
                    </label>


                    <input
                        type="text"
                        name="designation_code"
                        id="designation_code"
                        value="{{ old(
                            'designation_code',
                            $designation->designation_code
                        ) }}"
                        required
                        maxlength="50"
                        class="designation-edit-input"
                        style="text-transform:uppercase;"
                    >


                    @error('designation_code')

                        <div class="designation-edit-field-error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- =================================================
                     SECTION
                ================================================== --}}

                <div>

                    <label
                        for="section_id"
                        class="designation-edit-label"
                    >
                        Section
                    </label>


                    <div class="designation-edit-select-wrapper">

                        <select
                            name="section_id"
                            id="section_id"
                            required
                            class="designation-edit-select"
                        >

                            <option value="">
                                Select Section
                            </option>


                            @foreach($sections as $section)

                                <option
                                    value="{{ $section->id }}"
                                    {{
                                        (string) old(
                                            'section_id',
                                            $designation->section_id
                                        )
                                        ===
                                        (string) $section->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{ $section->section_name }}

                                </option>

                            @endforeach

                        </select>


                        <span
                            class="designation-edit-select-arrow"
                        ></span>

                    </div>


                    @error('section_id')

                        <div class="designation-edit-field-error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- =================================================
                     DESCRIPTION
                ================================================== --}}

                <div class="designation-edit-full">

                    <label
                        for="description"
                        class="designation-edit-label"
                    >
                        Description
                    </label>


                    <textarea
                        name="description"
                        id="description"
                        rows="3"
                        class="designation-edit-textarea"
                    >{{ old(
                        'description',
                        $designation->description
                    ) }}</textarea>


                    @error('description')

                        <div class="designation-edit-field-error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- =================================================
                     ACTIVE
                ================================================== --}}

                <div>

                    <label
                        class="designation-edit-checkbox-label"
                    >

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="designation-edit-checkbox"
                            {{
                                old(
                                    'is_active',
                                    $designation->is_active
                                )
                                ? 'checked'
                                : ''
                            }}
                        >

                        Active

                    </label>


                    @error('is_active')

                        <div class="designation-edit-field-error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

            </div>


            {{-- =========================================================
                 BUTTONS
            ========================================================== --}}

            <div class="designation-edit-actions">


                <button
                    type="submit"
                    class="
                        erp-btn
                        erp-btn-save
                        designation-edit-update-btn
                    "
                >
                    Update
                </button>


                <a
                    href="{{ route('designations.index') }}"
                    class="
                        erp-btn
                        designation-edit-cancel-btn
                    "
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</x-app-layout>