<x-app-layout>

<div style="max-width:700px;margin:auto;padding:20px;">

    <div style="
        background:white;
        border-radius:12px;
        padding:20px;
        border:1px solid #d1d5db;
        box-shadow:0 4px 10px rgba(0,0,0,.15);
    ">

        <h2 class="text-2xl font-bold text-yellow-600 text-center mb-6">
            Edit Subject
        </h2>


        {{-- =========================================================
             VALIDATION ERRORS
        ========================================================== --}}

        @if ($errors->any())

            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">

                <ul class="list-disc pl-5">

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- =========================================================
             ERROR MESSAGE
        ========================================================== --}}

        @if(session('error'))

            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">

                {{ session('error') }}

            </div>

        @endif


        {{-- =========================================================
             SUCCESS MESSAGE
        ========================================================== --}}

        @if(session('success'))

            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">

                {{ session('success') }}

            </div>

        @endif


        {{-- =========================================================
             SUBJECT MASTER INFORMATION
        ========================================================== --}}

        @php

            $masterSubject =
                $subject->subject ?? null;

            $masterSubjectId =
                $subject->subject_id
                ?? $masterSubject?->id
                ?? null;

            $masterSubjectName =
                $masterSubject?->subject_name
                ?? $subject->subject_name
                ?? '';

            $masterSubjectCode =
                $masterSubject?->subject_code
                ?? '';

            $masterShortName =
                $masterSubject?->short_name
                ?? '';

            $masterSubjectTypeId =
                $masterSubject?->subject_type_id
                ?? '';

        @endphp


        {{-- =========================================================
             FORM
        ========================================================== --}}

        <form
            method="POST"
            action="{{ route('subjects.update', $subject->id) }}"
            id="subjectEditForm"
        >

            @csrf

            @method('PUT')


            {{-- =====================================================
                 STANDARD
            ====================================================== --}}

            <div class="mb-4">

                <label
                    for="standard_id"
                    class="block font-semibold mb-2"
                >
                    Standard
                </label>

                <select
                    name="standard_id"
                    id="standard_id"
                    class="w-full border rounded p-2"
                    required
                >

                    <option value="">
                        Select Standard
                    </option>

                    @foreach($standards as $standard)

                        <option
                            value="{{ $standard->id }}"
                            {{ (string)old(
                                'standard_id',
                                $subject->standard_id
                            ) === (string)$standard->id
                                ? 'selected'
                                : '' }}
                        >
                            {{ $standard->standard_name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- =====================================================
                 SUBJECT MASTER ID
            ====================================================== --}}

            @if($masterSubjectId)

                <div class="mb-4">

                    <label
                        class="block font-semibold mb-2"
                    >
                        Subject Master ID
                    </label>

                    <input
                        type="text"
                        value="{{ $masterSubjectId }}"
                        readonly
                        class="w-full border rounded p-2 bg-gray-100"
                    >

                </div>

            @endif


            {{-- =====================================================
                 SUBJECT NAME
            ====================================================== --}}

            <div class="mb-4">

                <label
                    for="subject_name"
                    class="block font-semibold mb-2"
                >
                    Subject Name
                </label>

                <input
                    type="text"
                    name="subject_name"
                    id="subject_name"
                    class="w-full border rounded p-2"
                    value="{{ old(
                        'subject_name',
                        $masterSubjectName
                    ) }}"
                    maxlength="255"
                    placeholder="Enter Subject Name"
                    required
                >

            </div>


            {{-- =====================================================
                 SUBJECT CODE
            ====================================================== --}}

            <div class="mb-4">

                <label
                    for="subject_code"
                    class="block font-semibold mb-2"
                >
                    Subject Code
                </label>

                <input
                    type="text"
                    name="subject_code"
                    id="subject_code"
                    class="w-full border rounded p-2"
                    value="{{ old(
                        'subject_code',
                        $masterSubjectCode
                    ) }}"
                    maxlength="50"
                    placeholder="Enter Subject Code"
                    required
                >

            </div>


            {{-- =====================================================
                 SHORT NAME
            ====================================================== --}}

            <div class="mb-4">

                <label
                    for="short_name"
                    class="block font-semibold mb-2"
                >
                    Short Name
                </label>

                <input
                    type="text"
                    name="short_name"
                    id="short_name"
                    class="w-full border rounded p-2"
                    value="{{ old(
                        'short_name',
                        $masterShortName
                    ) }}"
                    maxlength="20"
                    placeholder="Enter Short Name"
                >

            </div>


            {{-- =====================================================
                 SUBJECT TYPE
            ====================================================== --}}

            <div class="mb-4">

                <label
                    for="subject_type_id"
                    class="block font-semibold mb-2"
                >
                    Subject Type
                </label>

                <select
                    name="subject_type_id"
                    id="subject_type_id"
                    class="w-full border rounded p-2"
                    required
                >

                    <option value="">
                        Select Subject Type
                    </option>

                    @foreach($subjectTypes as $subjectType)

                        <option
                            value="{{ $subjectType->id }}"
                            {{ (string)old(
                                'subject_type_id',
                                $masterSubjectTypeId
                            ) === (string)$subjectType->id
                                ? 'selected'
                                : '' }}
                        >

                            {{ $subjectType->name
                                ?? $subjectType->subject_type
                                ?? $subjectType->type_name
                                ?? $subjectType->description
                                ?? 'Type ' . $subjectType->id
                            }}

                        </option>

                    @endforeach

                </select>

            </div>


            {{-- =====================================================
                 DISPLAY ORDER
            ====================================================== --}}

            <div class="mb-4">

                <label
                    for="sort_order"
                    class="block font-semibold mb-2"
                >
                    Display Order
                </label>

                <input
                    type="number"
                    name="sort_order"
                    id="sort_order"
                    value="{{ old(
                        'sort_order',
                        $subject->sort_order
                    ) }}"
                    class="w-full border rounded p-2"
                    min="0"
                    required
                >

            </div>


            {{-- =====================================================
                 OPTIONAL SUBJECT
            ====================================================== --}}

            <div class="mb-4">

                <label class="flex items-center gap-2">

                    <input
                        type="checkbox"
                        name="is_optional"
                        value="1"
                        {{ old(
                            'is_optional',
                            $subject->is_optional
                        ) ? 'checked' : '' }}
                    >

                    <span>
                        Optional Subject
                    </span>

                </label>

            </div>


            {{-- =====================================================
                 ACTIVE
            ====================================================== --}}

            <div class="mb-4">

                <label class="flex items-center gap-2">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old(
                            'is_active',
                            $subject->is_active
                        ) ? 'checked' : '' }}
                    >

                    <span>
                        Active
                    </span>

                </label>

            </div>


            {{-- =====================================================
                 SHARED MASTER WARNING
            ====================================================== --}}

            <div
                style="
                    background:#fff7ed;
                    border:1px solid #fed7aa;
                    color:#9a3412;
                    padding:11px 13px;
                    border-radius:6px;
                    font-size:13px;
                    margin-top:15px;
                    line-height:1.55;
                "
            >

                <strong>Important:</strong>

                This record contains a

                <strong>Subject Master</strong>

                and a

                <strong>Standard-wise Mapping</strong>.

                <br><br>

                The same Subject Master can be used by multiple standards.

                <br>

                Subject Master ID:

                <strong>
                    {{ $masterSubjectId ?? '-' }}
                </strong>

                <br><br>

                Changing the Subject Name, Subject Code, Short Name or
                Subject Type can affect other standards using the same
                Subject Master.

                <br><br>

                Display Order, Optional and Active are maintained for the
                selected Standard mapping.

            </div>


            {{-- =====================================================
                 BUTTONS
            ====================================================== --}}

            <div class="flex justify-end items-center gap-2 mt-6">

                <button
                    type="submit"
                    class="erp-btn erp-btn-save"
                    id="updateSubjectButton"
                >
                    Update
                </button>


                <a
                    href="{{ route('subjects.index') }}"
                    class="erp-btn erp-btn-cancel"
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

        const form =
            document.getElementById(
                'subjectEditForm'
            );

        const subjectName =
            document.getElementById(
                'subject_name'
            );

        const subjectCode =
            document.getElementById(
                'subject_code'
            );

        const standard =
            document.getElementById(
                'standard_id'
            );

        const subjectType =
            document.getElementById(
                'subject_type_id'
            );

        const updateButton =
            document.getElementById(
                'updateSubjectButton'
            );


        /*
        |--------------------------------------------------------------------------
        | UPPERCASE SUBJECT CODE
        |--------------------------------------------------------------------------
        */

        subjectCode.addEventListener(
            'input',
            function () {

                this.value =
                    this.value.toUpperCase();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | BASIC VALIDATION
        |--------------------------------------------------------------------------
        */

        form.addEventListener(
            'submit',
            function (event) {

                /*
                |--------------------------------------------------------------
                | Standard
                |--------------------------------------------------------------
                */

                if (
                    !standard.value
                ) {

                    event.preventDefault();

                    alert(
                        'Please select Standard.'
                    );

                    standard.focus();

                    return;
                }


                /*
                |--------------------------------------------------------------
                | Subject Name
                |--------------------------------------------------------------
                */

                if (
                    !subjectName.value.trim()
                ) {

                    event.preventDefault();

                    alert(
                        'Please enter Subject Name.'
                    );

                    subjectName.focus();

                    return;
                }


                /*
                |--------------------------------------------------------------
                | Subject Code
                |--------------------------------------------------------------
                */

                if (
                    !subjectCode.value.trim()
                ) {

                    event.preventDefault();

                    alert(
                        'Please enter Subject Code.'
                    );

                    subjectCode.focus();

                    return;
                }


                /*
                |--------------------------------------------------------------
                | Subject Type
                |--------------------------------------------------------------
                */

                if (
                    !subjectType.value
                ) {

                    event.preventDefault();

                    alert(
                        'Please select Subject Type.'
                    );

                    subjectType.focus();

                    return;
                }


                /*
                |--------------------------------------------------------------
                | Prevent double submission
                |--------------------------------------------------------------
                */

                updateButton.disabled =
                    true;

                updateButton.innerText =
                    'Updating...';

            }
        );

    }
);

</script>

</x-app-layout>