<x-app-layout>

<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold text-blue-600">
        Exam Subject Allocation
    </h2>
</div>

@if(session('success'))
<div class="bg-green-100 text-green-700 p-3 rounded mb-4">
    {{ session('success') }}
</div>
@endif

<form method="GET"
      action="{{ route('exam-subjects.index') }}">

<div class="mb-4 flex items-center gap-3 flex-nowrap">

    <label class="font-semibold">
        Exam
    </label>

    <select name="exam_master_id"
            class="border rounded px-2 py-1 w-48 text-sm">

        <option value="">
            Select Exam
        </option>

        @foreach($exams as $exam)

        <option value="{{ $exam->id }}"
            {{ request('exam_master_id') == $exam->id ? 'selected' : '' }}>

            {{ $exam->exam_name }}

@if($exam->examPattern)
    ({{ $exam->examPattern->pattern_name }})
@endif

        </option>

        @endforeach

    </select>

    <label class="font-semibold">
        Standard
    </label>

    <select name="standard_id"
            class="border rounded px-2 py-1 w-48 text-sm">

        <option value="">
            Select Standard
        </option>

        @foreach($standards as $standard)

        <option value="{{ $standard->id }}"
            {{ request('standard_id') == $standard->id ? 'selected' : '' }}>

            {{ $standard->standard_name }}

        </option>

        @endforeach

    </select>

    <button type="submit"
            class="erp-btn erp-btn-save">
        Load Subjects
    </button>

</div>

</form>

@if(count($subjects))

<form method="POST"
      action="{{ route('exam-subjects.save') }}">

@csrf

<input type="hidden"
       name="exam_master_id"
       value="{{ request('exam_master_id') }}">

<input type="hidden"
       name="standard_id"
       value="{{ request('standard_id') }}">

<table class="w-full border border-gray-300 text-sm">

<thead class="bg-blue-100">

<tr>

    <th class="border p-2">
        Allocate
    </th>

    <th class="border p-2">
    Subject Name
</th>

<th class="border p-2">
    Max Marks
</th>

<th class="border p-2">
    Passing Marks
</th>
</tr>

</thead>

<tbody>

@foreach($subjects as $subject)

<tr>

<td class="border p-2 text-center">

<input type="checkbox"
       name="subjects[]"
       value="{{ $subject->id }}"
       {{ in_array($subject->id,$allocatedSubjects) ? 'checked' : '' }}>

</td>

<td class="border p-2">
    {{ $subject->subject_name }}
</td>

<td class="border p-2 text-center">
    <input type="number"
           step="0.01"
           name="max_marks[{{ $subject->id }}]"
           value="{{ $subject->max_marks }}"
           class="border rounded px-2 py-1 w-24">
</td>

<td class="border p-2 text-center">
    <input type="number"
           step="0.01"
           name="passing_marks[{{ $subject->id }}]"
           value="{{ $subject->passing_marks }}"
           class="border rounded px-2 py-1 w-24">
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

</form>

@endif

</div>

</x-app-layout>