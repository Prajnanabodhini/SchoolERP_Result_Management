<x-app-layout>

<div style="max-width:700px;margin:auto;padding:20px;">

    <div style="
        background:white;
        border-radius:12px;
        padding:20px;
        border:1px solid #d1d5db;
        box-shadow:0 4px 10px rgba(0,0,0,.15);
    ">

        <h2 class="text-2xl font-bold text-green-600 text-center mb-6">
            Add Standard Wise Subject
        </h2>


        {{-- VALIDATION ERRORS --}}
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


        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))

            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">

                {{ session('success') }}

            </div>

        @endif


        <form method="POST"
              action="{{ route('subjects.store') }}">

            @csrf


            {{-- =====================================================
                 STANDARD
            ====================================================== --}}

            <div class="mb-4">

                <label class="block font-semibold mb-2">
                    Standard
                </label>

                <select name="standard_id"
                        id="standard_id"
                        class="w-full border rounded p-2"
                        required>

                    <option value="">
                        Select Standard
                    </option>

                    @foreach($standards as $standard)

                        <option value="{{ $standard->id }}"
                            {{ old('standard_id') == $standard->id ? 'selected' : '' }}>

                            {{ $standard->standard_name }}

                        </option>

                    @endforeach

                </select>

            </div>


            {{-- =====================================================
                 MASTER SUBJECT
            ====================================================== --}}

            <div class="mb-4">

                <label class="block font-semibold mb-2">
                    Subject
                </label>

                <select name="subject_id"
                        id="subject_id"
                        class="w-full border rounded p-2"
                        required>

                    <option value="">
                        Select Subject
                    </option>

                    @foreach($masterSubjects as $masterSubject)

                        <option value="{{ $masterSubject->id }}"
                                data-name="{{ $masterSubject->subject_name }}"
                                data-code="{{ $masterSubject->subject_code }}"
                                data-short="{{ $masterSubject->short_name }}"
                                {{ old('subject_id') == $masterSubject->id ? 'selected' : '' }}>

                            {{ $masterSubject->subject_name }}

                            @if($masterSubject->subject_code)
                                ({{ $masterSubject->subject_code }})
                            @endif

                        </option>

                    @endforeach

                </select>

            </div>


            {{-- =====================================================
                 SUBJECT CODE
            ====================================================== --}}

            <div class="mb-4">

                <label class="block font-semibold mb-2">
                    Subject Code
                </label>

                <input type="text"
       name="subject_code"
       id="subject_code"
       class="w-full border rounded p-2 bg-gray-100"
       value="{{ old('subject_code') }}"
       readonly>

            </div>


            {{-- =====================================================
                 SUBJECT NAME
            ====================================================== --}}

            <div class="mb-4">

                <label class="block font-semibold mb-2">
                    Subject Name
                </label>

                <input type="text"
                       name="subject_name"
                       id="subject_name"
                       class="w-full border rounded p-2 bg-gray-100"
                       value="{{ old('subject_name') }}"
                       readonly
                       required>

            </div>


            {{-- =====================================================
                 SHORT NAME
            ====================================================== --}}

            <div class="mb-4">

                <label class="block font-semibold mb-2">
                    Short Name
                </label>

                <input type="text"
       name="short_name"
       id="short_name"
       class="w-full border rounded p-2 bg-gray-100"
       value="{{ old('short_name') }}"
       readonly>

            </div>


            {{-- =====================================================
                 DISPLAY ORDER
            ====================================================== --}}

            <div class="mb-4">

                <label class="block font-semibold mb-2">
                    Display Order
                </label>

                <input type="number"
                       name="sort_order"
                       value="{{ old('sort_order', $nextSortOrder) }}"
                       class="w-full border rounded p-2"
                       min="0"
                       required>

            </div>


            {{-- =====================================================
                 OPTIONAL
            ====================================================== --}}

            <div class="mb-4">

                <label>

                    <input type="checkbox"
                           name="is_optional"
                           value="1"
                           {{ old('is_optional') ? 'checked' : '' }}>

                    Optional Subject

                </label>

            </div>


            {{-- =====================================================
                 ACTIVE
            ====================================================== --}}

            <div class="mb-4">

                <label>

                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}>

                    Active

                </label>

            </div>


            {{-- =====================================================
                 BUTTONS
            ====================================================== --}}

            <div class="flex justify-end items-center gap-2 mt-6">

                <button type="submit"
                        class="erp-btn erp-btn-save">

                    Save

                </button>


                <a href="{{ route('subjects.index') }}"
                   class="erp-btn erp-btn-cancel">

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>


{{-- =============================================================
     SUBJECT AUTO LOAD
============================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    const subjectDropdown =
        document.getElementById('subject_id');

    const subjectName =
        document.getElementById('subject_name');

    const subjectCode =
        document.getElementById('subject_code');

    const shortName =
        document.getElementById('short_name');


    function loadSubjectDetails() {

        const selected =
            subjectDropdown.options[
                subjectDropdown.selectedIndex
            ];


        if (!selected || !selected.value) {

            subjectName.value = '';
            subjectCode.value = '';
            shortName.value = '';

            return;
        }


        subjectName.value =
            selected.getAttribute('data-name') || '';


        subjectCode.value =
            selected.getAttribute('data-code') || '';


        shortName.value =
            selected.getAttribute('data-short') || '';

    }


    subjectDropdown.addEventListener(
        'change',
        loadSubjectDetails
    );


    // Load values when validation fails
    loadSubjectDetails();

});

</script>

</x-app-layout>