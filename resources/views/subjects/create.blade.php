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
            Add Subject
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


        <form
            method="POST"
            action="{{ route('subjects.store') }}"
        >

            @csrf


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
                            {{ old('standard_id') == $standard->id ? 'selected' : '' }}
                        >
                            {{ $standard->standard_name }}
                        </option>

                    @endforeach

                </select>

            </div>


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
                    value="{{ old('subject_name') }}"
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
                    value="{{ old('subject_code') }}"
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
                    value="{{ old('short_name') }}"
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
                            {{ old('subject_type_id') == $subjectType->id ? 'selected' : '' }}
                        >
                            {{ $subjectType->name
                                ?? $subjectType->subject_type
                                ?? $subjectType->type_name
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
                    value="{{ old('sort_order', $nextSortOrder) }}"
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
                        {{ old('is_optional') ? 'checked' : '' }}
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
                        {{ old('is_active', true) ? 'checked' : '' }}
                    >

                    <span>
                        Active
                    </span>

                </label>

            </div>


            {{-- =====================================================
                 INFORMATION
            ====================================================== --}}

            <div
                style="
                    background:#eff6ff;
                    border:1px solid #bfdbfe;
                    color:#1e40af;
                    padding:10px 12px;
                    border-radius:6px;
                    font-size:13px;
                    margin-top:15px;
                "
            >

                <strong>Note:</strong>

                Saving this form will create:

                <strong>one Subject Master record</strong>

                and

                <strong>one Standard Wise Subject mapping</strong>

                automatically using the same Subject ID.

            </div>


            {{-- =====================================================
                 BUTTONS
            ====================================================== --}}

            <div class="flex justify-end items-center gap-2 mt-6">

                <button
                    type="submit"
                    class="erp-btn erp-btn-save"
                >
                    Save
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

</x-app-layout>