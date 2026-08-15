<x-app-layout>

<div class="max-w-7xl mx-auto py-6 px-4">

<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

<div class="flex justify-between items-center mb-4">

<h2 class="text-2xl font-bold text-blue-600">
Teacher Subject Allocation
</h2>

<a href="{{ route('teacher-subject-allocation.create') }}"
   class="erp-btn erp-btn-add">

+ Add Allocation

</a>

</div>
<form method="GET" class="mb-4 flex items-center gap-2">

    <label class="font-semibold">
        Exam:
    </label>

    <select
        name="exam_master_id"
        onchange="this.form.submit()"
        class="border rounded"
style="font-size:15px;height:30px;padding:2px 6px; width:200px">

        <option value="">
            All Exams
        </option>

        @foreach($exams as $exam)

            <option
                value="{{ $exam->id }}"
                {{ $selectedExamId == $exam->id ? 'selected' : '' }}
            >
                {{ $exam->exam_name }}
            </option>

        @endforeach

    </select>

</form>
<table class="w-full border">

<thead class="bg-blue-100">

<tr>
<th class="border p-2">Exams</th>
<th class="border p-2">Teacher</th>
<th class="border p-2">Standard</th>
<th class="border p-2">Division</th>
<th class="border p-2">Subject</th>

</tr>

</thead>

<tbody>

@forelse($statuses as $row)

<tr>

    <td class="border p-2">
        {{ $row->exam->exam_name ?? '-' }}
    </td>

    <td class="border p-2">
        {{ $row->teacher->name ?? '-' }}
    </td>

    <td class="border p-2">
        {{ $row->standard->standard_name ?? '-' }}
    </td>

    <td class="border p-2">
        {{ $row->division->division_name ?? '-' }}
    </td>

    <td class="border p-2">
        {{ $row->subject->subject_name ?? '-' }}
    </td>

</tr>

@empty

<tr>
    <td colspan="5" class="text-center p-4">
        No Allocation Found
    </td>
</tr>

@endforelse

</tbody>

</table>

</div>

</div>

</x-app-layout>