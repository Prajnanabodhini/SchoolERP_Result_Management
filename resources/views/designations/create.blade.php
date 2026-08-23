<x-app-layout>

<div class="erp-page">

    <style>

        .designation-create-page,
        .designation-create-page * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif !important;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD
        |--------------------------------------------------------------------------
        */

        .designation-create-card {
            width: 100%;
        }


        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        .designation-create-title {
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

        .designation-create-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }


        .designation-create-full {
            grid-column: 1 / -1;
        }


        /*
        |--------------------------------------------------------------------------
        | LABEL
        |--------------------------------------------------------------------------
        */

        .designation-create-label {
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

        .designation-create-input,
        .designation-create-textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            background: #ffffff;
            color: #111827;
            font-size: 13px;
            box-sizing: border-box;
        }


        .designation-create-input {
            height: 34px;
            padding: 5px 8px;
        }


        .designation-create-textarea {
            min-height: 78px;
            padding: 7px 8px;
            resize: vertical;
        }


        .designation-create-input:focus,
        .designation-create-textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 1px #2563eb;
        }


        /*
        |--------------------------------------------------------------------------
        | SELECT WRAPPER
        |--------------------------------------------------------------------------
        */

        .designation-create-select-wrapper {
            position: relative;
            width: 100%;
        }


        /*
        |--------------------------------------------------------------------------
        | SELECT
        |--------------------------------------------------------------------------
        */

        .designation-create-select {
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


        .designation-create-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 1px #2563eb;
        }


        /*
        |--------------------------------------------------------------------------
        | DROPDOWN ARROW
        |--------------------------------------------------------------------------
        */

        .designation-create-select-arrow {
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

        .designation-create-checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            font-size: 13px;
            font-weight: 600;

            color: #374151;

            cursor: pointer;
        }


        .designation-create-checkbox {
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

        .designation-create-error-box {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;

            padding: 10px;

            border-radius: 5px;

            margin-bottom: 12px;

            font-size: 13px;
        }


        .designation-create-field-error {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
        }


        /*
        |--------------------------------------------------------------------------
        | BUTTONS
        |--------------------------------------------------------------------------
        */

        .designation-create-actions {
            margin-top: 18px;

            display: flex;
            align-items: center;

            gap: 8px;

            flex-wrap: wrap;
        }


        .designation-create-save-btn {
            min-width: 80px;
            white-space: nowrap;
        }


        .designation-create-cancel-btn {
            min-width: 80px;

            background: #6b7280;
            color: #ffffff;

            white-space: nowrap;
            text-align: center;

            text-decoration: none;
        }


        .designation-create-cancel-btn:hover {
            background: #4b5563;
            color: #ffffff;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 700px) {

            .designation-create-grid {
                grid-template-columns: 1fr;
            }


            .designation-create-full {
                grid-column: auto;
            }

        }

    </style>


    <div class="erp-card designation-create-page designation-create-card">


        {{-- =========================================================
             TITLE
        ========================================================== --}}

        <h2 class="designation-create-title">
            ADD DESIGNATION
        </h2>


        {{-- =========================================================
             VALIDATION ERRORS
        ========================================================== --}}

        @if($errors->any())

            <div class="designation-create-error-box">

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
             FORM
        ========================================================== --}}

        <form
            method="POST"
            action="{{ route('designations.store') }}"
        >

            @csrf


            <div class="designation-create-grid">


                {{-- =================================================
                     DESIGNATION NAME
                ================================================== --}}

                <div>

                    <label
                        for="designation_name"
                        class="designation-create-label"
                    >
                        Designation Name
                    </label>


                    <input
                        type="text"
                        name="designation_name"
                        id="designation_name"
                        value="{{ old('designation_name') }}"
                        required
                        maxlength="100"
                        class="designation-create-input"
                    >


                    @error('designation_name')

                        <div class="designation-create-field-error">
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
                        class="designation-create-label"
                    >
                        Designation Code
                    </label>


                    <input
                        type="text"
                        name="designation_code"
                        id="designation_code"
                        value="{{ old('designation_code') }}"
                        required
                        maxlength="50"
                        class="designation-create-input"
                        style="text-transform:uppercase;"
                    >


                    @error('designation_code')

                        <div class="designation-create-field-error">
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
                        class="designation-create-label"
                    >
                        Section
                    </label>


                    <div class="designation-create-select-wrapper">

                        <select
                            name="section_id"
                            id="section_id"
                            required
                            class="designation-create-select"
                        >

                            <option value="">
                                Select Section
                            </option>


                            @foreach($sections as $section)

                                <option
                                    value="{{ $section->id }}"
                                    {{
                                        (string) old(
                                            'section_id'
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
                            class="designation-create-select-arrow"
                        ></span>

                    </div>


                    @error('section_id')

                        <div class="designation-create-field-error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- =================================================
                     DESCRIPTION
                ================================================== --}}

                <div class="designation-create-full">

                    <label
                        for="description"
                        class="designation-create-label"
                    >
                        Description
                    </label>


                    <textarea
                        name="description"
                        id="description"
                        rows="3"
                        class="designation-create-textarea"
                    >{{ old('description') }}</textarea>


                    @error('description')

                        <div class="designation-create-field-error">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- =================================================
                     ACTIVE
                ================================================== --}}

                <div>

                    <label
                        class="designation-create-checkbox-label"
                    >

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="designation-create-checkbox"
                            {{
                                old(
                                    'is_active',
                                    1
                                )
                                    ? 'checked'
                                    : ''
                            }}
                        >

                        Active

                    </label>

                </div>

            </div>


            {{-- =========================================================
                 BUTTONS
            ========================================================== --}}

            <div class="designation-create-actions">


                <button
                    type="submit"
                    class="
                        erp-btn
                        erp-btn-save
                        designation-create-save-btn
                    "
                >
                    Save
                </button>


                <a
                    href="{{ route('designations.index') }}"
                    class="
                        erp-btn
                        designation-create-cancel-btn
                    "
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</x-app-layout>