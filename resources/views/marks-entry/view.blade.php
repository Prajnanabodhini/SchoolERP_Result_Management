<x-app-layout>

@php
$currentFilters = request()->query();
@endphp

<style>
.filter-row{
    display:flex;
    align-items:center;
    gap:6px;
    flex-wrap:nowrap;
}
.tabs-container{
    display:flex;
    border-bottom:2px solid #2563EB;
    margin-bottom:20px;
}

.active-tab{
    background:#2563EB;
    color:white !important;
    padding:10px 20px;
    text-decoration:none;
    border-radius:6px 6px 0 0;
    margin-right:4px;
    font-weight:bold;
}

.inactive-tab{
    background:#E5E7EB;
    color:#111827 !important;
    padding:10px 20px;
    text-decoration:none;
    border-radius:6px 6px 0 0;
    margin-right:4px;
    font-weight:bold;
}


.mark-input{
    width:70px;
    height:28px;
    padding:2px 4px;
    font-size:13px;
    text-align:center;
}
</style>

<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

    <h2 class="text-xl font-bold text-blue-600 mb-4">
    Examination Marks
</h2>

    {{-- Tabs --}}
    <div class="tabs-container">

<a href="{{ route('marks-entry.index', request()->query()) }}"
   class="
   {{ request()->routeIs('marks-entry.index')
      ? 'active-tab'
      : 'inactive-tab' }}">
   Marks Entry
</a>

<a href="{{ route('marks-entry.view', request()->query()) }}"
   class="
   {{ request()->routeIs('marks-entry.view')
      ? 'active-tab'
      : 'inactive-tab' }}">
   View / Edit Marks
</a>

@if(hasAdministrationAccess())

<a href="{{ route('marks-entry.edit',[
    'exam_master_id'=>request('exam_master_id'),
    'teacher_subject_allocation_id'=>request('teacher_subject_allocation_id')
]) }}"
class="erp-btn erp-btn-edit">

Edit Marks

</a>

@endif

</div>
    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4 mb-4 border border-gray-200">

        <form method="GET"
              action="{{ route('marks-entry.view') }}">

            <div class="flex items-center gap-3 flex-wrap">

                <label class="font-semibold">
                    Exam
                </label>

                <select name="exam_master_id"
                        class="border rounded"
style="font-size:12px;height:30px;padding:2px 6px; width:200px">

                    <option value="">
                        Select
                    </option>

                    @foreach($exams as $examRow)

                    <option value="{{ $examRow->id }}"
                        {{ request('exam_master_id') == $examRow->id ? 'selected' : '' }}>

                        {{ $examRow->exam_name }}

                    </option>

                    @endforeach

                </select>

                <label class="font-semibold">
                    Standard
                </label>

                <select name="standard_id"
        onchange="this.form.submit()"
        class="border rounded"
style="font-size:12px;height:30px;padding:2px 6px;">
        
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

                <label class="font-semibold">
                    Division
                </label>

                <select name="division_id"
                        class="border rounded"
style="font-size:12px;height:30px;padding:2px 6px;">

                    <option value="">
                        Select Division
                    </option>

                    @foreach($divisions as $division)

                    <option value="{{ $division->id }}"
                        {{ request('division_id') == $division->id ? 'selected' : '' }}>

                        {{ $division->division_name }}

                    </option>

                    @endforeach

                </select>

                <label class="font-semibold">
                    Subject
                </label>

                <select name="subject_id"
                        class="border rounded"
style="font-size:12px;height:30px;padding:2px 6px;">

                    <option value="">
                        Select Subject
                    </option>

                    @foreach($subjects as $subject)

                    <option value="{{ $subject->id }}"
                        {{ request('subject_id') == $subject->id ? 'selected' : '' }}>

                        {{ $subject->subject_name }}

                    </option>

                    @endforeach

                </select>

                <button
    type="submit"
    class="erp-btn erp-btn-save">

    Search

</button>

@if($records->count())

<span style="
background:#DBEAFE;
color:#1E40AF;
padding:6px 12px;
border-radius:4px;
font-weight:600;
margin-left:10px;
">

Students :
{{ $records->count() }}

</span>

@endif
@if(isset($marks))

<span style="
background:#DBEAFE;
padding:6px 12px;
border-radius:4px;
font-weight:bold;
color:#1E40AF;
">

Records :
{{ $marks->count() }}

</span>

@endif
            </div>

        </form>

    </div>

    <form method="POST"
          action="{{ route('marks-entry.save') }}">

        @csrf

        @if($records->count()==0)

<div style="
background:#FEF2F2;
border:1px solid #FCA5A5;
padding:10px;
border-radius:5px;
margin-bottom:15px;
color:#DC2626;
font-weight:bold;
">

No records found.

</div>

@endif

        <table class="w-full border border-gray-300 text-sm bg-white">

            <thead class="bg-blue-100">

            <tr>
<th>Student ID</th>
                <th class="border p-2">
                    Student
                </th>

                @if($showTheory)
                    <th class="border p-2">Theory Max</th>
                    <th class="border p-2">Theory Pass</th>
                    <th class="border p-2">Theory Obtained</th>
                @endif

                @if($showOral)
                    <th class="border p-2">Oral Max</th>
                    <th class="border p-2">Oral Pass</th>
                    <th class="border p-2">Oral Obtained</th>
                @endif

                @if($showPractical)
                    <th class="border p-2">Practical Max</th>
                    <th class="border p-2">Practical Pass</th>
                    <th class="border p-2">Practical Obtained</th>
                @endif

            </tr>

            </thead>

            <tbody>

            @forelse($records as $row)

            <tr>

                <input type="hidden"
                       name="mark_ids[]"
                       value="{{ $row->id }}">
<td>
    {{ $row->student_id }}
</td>
                <td class="border p-2">
                    {{ $row->studname }}
                </td>

                @if($showTheory)

                <td class="border p-2 text-center">
                    {{ $row->theory_max_marks }}
                </td>

                <td class="border p-2 text-center">
                    {{ $row->theory_passing_marks }}
                </td>

                <td class="border p-2 text-center">

                    @if(auth()->user()->role == 'Admin')

                    <input type="number"
                           name="theory_marks[{{ $row->id }}]"
                           value="{{ $row->theory_obtained_marks }}"
                           class="mark-input">

                    @else

                    {{ $row->theory_obtained_marks }}

                    @endif

                </td>

                @endif


                @if($showOral)

                <td class="border p-2 text-center">
                    {{ $row->oral_max_marks }}
                </td>

                <td class="border p-2 text-center">
                    {{ $row->oral_passing_marks }}
                </td>

                <td class="border p-2 text-center">

                    @if(auth()->user()->role == 'Admin')

                    <input type="number"
                           name="oral_marks[{{ $row->id }}]"
                           value="{{ $row->oral_obtained_marks }}"
                           class="mark-input">

                    @else

                    {{ $row->oral_obtained_marks }}

                    @endif

                </td>

                @endif


                @if($showPractical)

                <td class="border p-2 text-center">
                    {{ $row->practical_max_marks }}
                </td>

                <td class="border p-2 text-center">
                    {{ $row->practical_passing_marks }}
                </td>

                <td class="border p-2 text-center">

                    @if(auth()->user()->role == 'Admin')

                    <input type="number"
                           name="practical_marks[{{ $row->id }}]"
                           value="{{ $row->practical_obtained_marks }}"
                           class="mark-input">

                    @else

                    {{ $row->practical_obtained_marks }}

                    @endif

                </td>

                @endif

            </tr>

            @empty

            <tr>
                <td colspan="20"
                    class="border p-3 text-center">
                    No records found.
                </td>
            </tr>

            @endforelse

            </tbody>

        </table>

        @if(auth()->user()->role == 'Admin')

        <div class="mt-4">

            <button type="submit"
                    class="erp-btn erp-btn-save">

                Update Marks

            </button>

        </div>

        @endif

    </form>

</div>

</x-app-layout>