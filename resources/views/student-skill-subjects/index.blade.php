<x-app-layout>

<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">


<div class="flex justify-between items-center mb-4">

    <h2 class="text-xl font-bold text-blue-600">
        Student Skill Subject Allocation
    </h2>

</div>

@if(session('success'))

<div class="bg-green-100 text-green-700 p-3 rounded mb-4">

    {{ session('success') }}

</div>

@endif

<form method="GET"
      action="{{ route('student-skill-subject-allocation.index') }}">

    <div class="flex gap-3 items-center mb-4 flex-wrap">

        <label class="font-semibold">
    Academic Year
</label>

<select name="academic_year_id"
        class="border rounded px-2 py-1"
        style="min-width:200px;">

    <option value="">
        Select Academic Year
    </option>

    @foreach($academicYears as $year)

        <option value="{{ $year->id }}"
            {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>

            {{ $year->year_name }}

        </option>

    @endforeach

</select>

        <label class="font-semibold">
            Standard
        </label>

        <select name="standard_id"
                class="border rounded px-2 py-1">

            <option value="">
                Select
            </option>

            @foreach($standards as $standard)

            <option value="{{ $standard->id }}"
                {{ request('standard_id') == $standard->id ? 'selected' : '' }}>

                {{ $standard->standard_name }}

            </option>

            @endforeach

        </select>

        <label class="font-semibold">
            Division
        </label>

        <select name="division_id"
                class="border rounded px-2 py-1"
                style="min-width:100px;">

            <option value="">
                Select
            </option>

            @foreach($divisions as $division)

            <option value="{{ $division->id }}"
                {{ request('division_id') == $division->id ? 'selected' : '' }}>

                {{ $division->division_name }}

            </option>

            @endforeach

        </select>

        <button type="submit"
                class="erp-btn erp-btn-save">

            Load Students

        </button>

    </div>

</form>

@if(count($students))

<form method="POST"
      action="{{ route('student-skill-subject-allocation.save') }}">

    @csrf

    <table class="w-full border border-gray-300 text-sm">

        <thead class="bg-blue-100">

            <tr>

                <th class="border p-2">
                    GR No
                </th>

                <th class="border p-2">
                    Roll No
                </th>

                <th class="border p-2">
                    Student Name
                </th>

                <th class="border p-2">
                    Skill Subject
                </th>

            </tr>

        </thead>

        <tbody>

        @foreach($students as $student)

            <tr>

                <td class="border p-2">

                    {{ $student->regno }}

                </td>

<td class="border p-2">
    {{ $student->rollno }}
</td>

<td class="border p-2">
    {{ trim(($student->studname ?? '') . ' ' . ($student->fathername ?? '')) }}
</td>
                <td class="border p-2">

                    <select
                        name="skill_subject[{{ $student->Studentid }}]"
                        class="border rounded px-2 py-1 w-full">

                        <option value="">
                            Select Skill Subject
                        </option>

                        @foreach($skillSubjects as $subject)

                        <option
                            value="{{ $subject->id }}"
                            {{ $student->selected_subject == $subject->id ? 'selected' : '' }}>

                            {{ $subject->subject_name }}

                        </option>

                        @endforeach

                    </select>

                </td>

            </tr>

        @endforeach

        </tbody>

    </table>

    <div class="mt-4">

        <button type="submit"
                class="erp-btn erp-btn-save">

            Save Allocation

        </button>

    </div>
<input type="hidden"
       name="academic_year_id"
       value="{{ request('academic_year_id') }}">
</form>

@endif


</div>

</x-app-layout>
