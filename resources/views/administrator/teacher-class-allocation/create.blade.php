<x-app-layout>

@if(session('error'))
<div style="
    background:#FEE2E2;
    color:#B91C1C;
    border:1px solid #EF4444;
    padding:12px;
    margin-bottom:15px;
    border-radius:6px;
    font-weight:bold;">
    {{ session('error') }}
</div>
@endif

<div style="max-width:700px; margin:auto; padding:20px;">

<div style="background:white;
            border-radius:12px;
            padding:20px;
            border:1px solid #d1d5db;
            box-shadow:0 4px 10px rgba(0,0,0,0.15);">

<h2 class="text-2xl font-bold text-green-600 text-center mb-6">
    Teacher Class Allocation
</h2>

<form method="POST"
      action="{{ route('teacher-class-allocation.store') }}">

    @csrf

    <div class="mb-4">

        <label class="block font-semibold mb-2">
            Teacher
        </label>

        <select name="user_id"
                class="w-full border rounded p-2"
                required>

            <option value="">
                Select Teacher
            </option>

            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}">
                    {{ $teacher->name }}
                </option>
            @endforeach

        </select>

    </div>

    <div class="mb-4">

        <label class="block font-semibold mb-2">
            Academic Year
        </label>

        <select name="academic_year_id"
                class="w-full border rounded p-2"
                required>

            <option value="">
                Select Academic Year
            </option>

            @foreach($academicYears as $year)
                <option value="{{ $year->id }}">
                    {{ $year->year_name }}
                </option>
            @endforeach

        </select>

    </div>

    <div class="mb-4">

        <label class="block font-semibold mb-2">
            Section
        </label>

        <select name="section_id"
                class="w-full border rounded p-2"
                required>

            <option value="">
                Select Section
            </option>

            @foreach($sections as $section)
                <option value="{{ $section->id }}">
                    {{ $section->section_name }}
                </option>
            @endforeach

        </select>

    </div>

    <div class="mb-4">

        <label class="block font-semibold mb-2">
            Standard
        </label>

        <select name="standard_id"
                class="w-full border rounded p-2"
                required>

            <option value="">
                Select Standard
            </option>

            @foreach($standards as $standard)
                <option value="{{ $standard->id }}">
                    {{ $standard->standard_name }}
                </option>
            @endforeach

        </select>

    </div>

    <div class="mb-4">

        <label class="block font-semibold mb-2">
            Division
        </label>

        <select name="division_id"
                class="w-full border rounded p-2"
                required>

            <option value="">
                Select Division
            </option>

            @foreach($divisions as $division)
                <option value="{{ $division->id }}">
                    {{ $division->division_name }}
                </option>
            @endforeach

        </select>

    </div>

    <div class="flex justify-between items-center mt-6">

        <label>
            <input type="checkbox"
                   name="is_class_teacher"
                   value="1">
            Class Teacher
        </label>

        <div>

            <button type="submit"
                    class="erp-btn erp-btn-save">
                Save
            </button>

            <a href="{{ route('teacher-class-allocation.index') }}"
               class="erp-btn erp-btn-cancel">
                Cancel
            </a>

        </div>

    </div>

</form>

</div>

</div>

</x-app-layout>