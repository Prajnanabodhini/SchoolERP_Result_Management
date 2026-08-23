<x-app-layout>

<style>

    /*
    |--------------------------------------------------------------------------
    | PAGE
    |--------------------------------------------------------------------------
    */

    .assignment-page,
    .assignment-page * {
        box-sizing: border-box;
        font-family: Arial, Helvetica, sans-serif !important;
    }


    /*
    |--------------------------------------------------------------------------
    | CARD
    |--------------------------------------------------------------------------
    */

    .assignment-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        padding: 20px;
    }


    /*
    |--------------------------------------------------------------------------
    | TITLE
    |--------------------------------------------------------------------------
    */

    .assignment-title {
        margin: 0 0 15px;
        font-size: 20px;
        font-weight: 700;
        color: #1d4ed8;
    }


    /*
    |--------------------------------------------------------------------------
    | FORM GRID
    |--------------------------------------------------------------------------
    */

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }


    .form-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }


    .form-label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }


    /*
    |--------------------------------------------------------------------------
    | SELECT WRAPPER
    |--------------------------------------------------------------------------
    */

    .form-select-wrapper {
        position: relative;
        width: 100%;
    }


    /*
    |--------------------------------------------------------------------------
    | FORM CONTROL
    |--------------------------------------------------------------------------
    */

    .form-control {
        width: 100%;
        height: 35px;

        padding:
            5px
            34px
            5px
            9px;

        border: 1px solid #d1d5db;
        border-radius: 5px;

        background: #ffffff;
        color: #111827;

        font-size: 13px;

        outline: none;

        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;

        cursor: pointer;
    }


    .form-control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }


    /*
    |--------------------------------------------------------------------------
    | CUSTOM DROPDOWN ARROW
    |--------------------------------------------------------------------------
    */

    .form-select-arrow {
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
    | DISABLED SELECT
    |--------------------------------------------------------------------------
    */

    .form-control:disabled {
        background: #f3f4f6;
        color: #6b7280;
        cursor: not-allowed;
    }


    .form-control:disabled + .form-select-arrow {
        border-top-color: #9ca3af;
    }


    /*
    |--------------------------------------------------------------------------
    | ERROR MESSAGE
    |--------------------------------------------------------------------------
    */

    .message-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
        padding: 10px 12px;
        border-radius: 5px;
        margin-bottom: 12px;
        font-size: 13px;
        font-weight: 600;
    }


    /*
    |--------------------------------------------------------------------------
    | HELP BOX
    |--------------------------------------------------------------------------
    */

    .help-box {
        grid-column: 1 / -1;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e3a8a;
        padding: 10px 12px;
        border-radius: 5px;
        font-size: 12px;
    }


    /*
    |--------------------------------------------------------------------------
    | BUTTON ROW
    |--------------------------------------------------------------------------
    */

    .button-row {
        margin-top: 18px;
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }


    /*
    |--------------------------------------------------------------------------
    | BUTTON
    |--------------------------------------------------------------------------
    */

    .btn {
        height: 34px;
        padding: 5px 14px;
        border: 0;
        border-radius: 5px;

        font-size: 12px;
        font-weight: 600;

        text-decoration: none;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        cursor: pointer;
        white-space: nowrap;
    }


    .btn-save {
        background: #2563eb;
        color: #ffffff;
    }


    .btn-save:hover {
        background: #1d4ed8;
    }


    .btn-cancel {
        background: #6b7280;
        color: #ffffff;
    }


    .btn-cancel:hover {
        background: #4b5563;
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media(max-width:800px) {

        .form-grid {
            grid-template-columns: 1fr;
        }


        .help-box {
            grid-column: auto;
        }

    }

</style>


<div class="erp-page assignment-page">

    <div class="assignment-card">


        {{-- =========================================================
             TITLE
        ========================================================== --}}

        <h2 class="assignment-title">
            ASSIGN USER DESIGNATION
        </h2>


        {{-- =========================================================
             SESSION ERROR
        ========================================================== --}}

        @if(session('error'))

            <div class="message-error">
                {{ session('error') }}
            </div>

        @endif


        {{-- =========================================================
             VALIDATION ERRORS
        ========================================================== --}}

        @if($errors->any())

            <div class="message-error">

                <strong>
                    Please correct the following:
                </strong>

                <ul style="
                    margin:6px 0 0 20px;
                    padding:0;
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
            action="{{ route('user-designations.store') }}"
        >

            @csrf


            <div class="form-grid">


                {{-- =================================================
                     USER
                ================================================== --}}

                <div class="form-group">

                    <label class="form-label">
                        User
                    </label>


                    <div class="form-select-wrapper">

                        <select
                            name="user_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                Select User
                            </option>


                            @foreach($users as $user)

                                <option
                                    value="{{ $user->id }}"
                                    {{
                                        old('user_id')
                                        ==
                                        $user->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{ $user->name }}

                                    @if($user->email)

                                        -
                                        {{ $user->email }}

                                    @endif

                                </option>

                            @endforeach

                        </select>


                        <span class="form-select-arrow"></span>

                    </div>

                </div>


                {{-- =================================================
                     DESIGNATION
                ================================================== --}}

                <div class="form-group">

                    <label class="form-label">
                        Designation
                    </label>


                    <div class="form-select-wrapper">

                        <select
                            name="designation_id"
                            id="designation_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                Select Designation
                            </option>


                            @foreach($designations as $designation)

                                <option
                                    value="{{ $designation->id }}"
                                    data-code="{{
                                        strtoupper(
                                            $designation->designation_code
                                        )
                                    }}"
                                    {{
                                        old('designation_id')
                                        ==
                                        $designation->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{ $designation->designation_name }}

                                </option>

                            @endforeach

                        </select>


                        <span class="form-select-arrow"></span>

                    </div>

                </div>


                {{-- =================================================
                     ACADEMIC YEAR
                ================================================== --}}

                <div class="form-group">

                    <label class="form-label">
                        Academic Year
                    </label>


                    <div class="form-select-wrapper">

                        <select
                            name="academic_year_id"
                            id="academic_year_id"
                            class="form-control"
                        >

                            <option value="">
                                Not Applicable
                            </option>


                            @foreach($academicYears as $year)

                                <option
                                    value="{{ $year->id }}"
                                    {{
                                        old(
                                            'academic_year_id'
                                        )
                                        ==
                                        $year->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{ $year->year_name }}

                                </option>

                            @endforeach

                        </select>


                        <span class="form-select-arrow"></span>

                    </div>

                </div>


                {{-- =================================================
                     STANDARD
                ================================================== --}}

                <div class="form-group">

                    <label class="form-label">
                        Standard
                    </label>


                    <div class="form-select-wrapper">

                        <select
                            name="standard_id"
                            id="standard_id"
                            class="form-control"
                        >

                            <option value="">
                                Not Applicable
                            </option>


                            @foreach($standards as $standard)

                                <option
                                    value="{{ $standard->id }}"
                                    {{
                                        old(
                                            'standard_id'
                                        )
                                        ==
                                        $standard->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{ $standard->standard_name }}

                                </option>

                            @endforeach

                        </select>


                        <span class="form-select-arrow"></span>

                    </div>

                </div>


                {{-- =================================================
                     DIVISION
                ================================================== --}}

                <div class="form-group">

                    <label class="form-label">
                        Division
                    </label>


                    <div class="form-select-wrapper">

                        <select
                            name="division_id"
                            id="division_id"
                            class="form-control"
                        >

                            <option value="">
                                Not Applicable
                            </option>


                            @foreach($divisions as $division)

                                <option
                                    value="{{ $division->id }}"
                                    {{
                                        old(
                                            'division_id'
                                        )
                                        ==
                                        $division->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{ $division->division_name }}

                                </option>

                            @endforeach

                        </select>


                        <span class="form-select-arrow"></span>

                    </div>

                </div>


                {{-- =================================================
                     HELP
                ================================================== --}}

                <div class="help-box">

                    <strong>
                        Class Teacher:
                    </strong>

                    Academic Year, Standard and Division are required.

                    <br>

                    <strong>
                        Principal:
                    </strong>

                    Academic Year may be selected, but Standard and
                    Division are not required.

                    <br>

                    <strong>
                        Other designations:
                    </strong>

                    Can be assigned without class details.

                </div>

            </div>


            {{-- =========================================================
                 BUTTONS
            ========================================================== --}}

            <div class="button-row">

                <button
                    type="submit"
                    class="btn btn-save"
                >
                    Save Assignment
                </button>


                <a
                    href="{{ route(
                        'user-designations.index'
                    ) }}"
                    class="btn btn-cancel"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const designationSelect =
            document.getElementById(
                'designation_id'
            );


        const standardSelect =
            document.getElementById(
                'standard_id'
            );


        const divisionSelect =
            document.getElementById(
                'division_id'
            );


        /*
        |--------------------------------------------------------------------------
        | UPDATE DESIGNATION FIELDS
        |--------------------------------------------------------------------------
        */

        function updateDesignationFields()
        {

            if (
                !designationSelect ||
                !standardSelect ||
                !divisionSelect
            ) {

                return;

            }


            const option =
                designationSelect.options[
                    designationSelect.selectedIndex
                ];


            const code =
                option
                    ? (
                        option.dataset.code
                        || ''
                    ).toUpperCase()
                    : '';


            /*
            |--------------------------------------------------------------------------
            | PRINCIPAL
            |--------------------------------------------------------------------------
            */

            if (
                code === 'PRINCIPAL'
            ) {

                standardSelect.value =
                    '';

                divisionSelect.value =
                    '';


                standardSelect.disabled =
                    true;

                divisionSelect.disabled =
                    true;


            } else {

                standardSelect.disabled =
                    false;

                divisionSelect.disabled =
                    false;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | DESIGNATION CHANGE
        |--------------------------------------------------------------------------
        */

        if (designationSelect) {

            designationSelect.addEventListener(
                'change',
                updateDesignationFields
            );

        }


        /*
        |--------------------------------------------------------------------------
        | INITIAL STATE
        |--------------------------------------------------------------------------
        */

        updateDesignationFields();

    }
);

</script>

</x-app-layout>