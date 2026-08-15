<x-app-layout>

<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">


<h2 class="text-xl font-bold text-blue-600 mb-4">
    Student Master
</h2>



<form method="GET"
      action="{{ route('students.index') }}">


<div class="flex items-center gap-3 mb-4">



<label class="font-semibold whitespace-nowrap">
    Academic Year
</label>


<select name="academic_year_id"
        class="border rounded px-2 py-1 w-40">


    <option value="">
        Select Year
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
        class="border rounded px-2 py-1 w-48">

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
        class="border rounded px-2 py-1 w-32">

<option value="">
Select Division
</option>


@foreach($divisions as $division)

<option value="{{ $division->id }}"

{{ request('division_id') == $division->id ? 'selected':'' }}>

{{ $division->division_name }}

</option>


@endforeach


</select>

<button type="submit"
class="erp-btn erp-btn-save">

Search

</button>




@if($students->count())


<span class="text-blue-600 font-semibold">

Total Students : {{ $students->count() }}

</span>


@endif



</div>


</form>





<table class="w-full border border-gray-300 text-sm">


<thead class="bg-blue-100">


<tr>


<th class="border p-2">
Admission No
</th>

<th>
    Roll No
</th>

<th class="border p-2">
Student Name
</th>


<th class="border p-2">
Mobile
</th>
<th class="border p-2">
    Date of Birth
</th>

<th class="border p-2">
    Admission Date
</th>

<th class="border p-2">
    Status
</th>

</tr>


</thead>




<tbody>



@forelse($students as $student)



<tr>


<td class="border p-2">

{{ $student->regno }}

</td>

<td class="border p-2">

{{ $student->rollno }}

</td>

<td class="border p-2">

{{ $student->full_name }}

</td>

<td class="border p-2">

{{ $student->fathermobile ?? '-' }}

</td>
<td class="border p-2 text-center">
@php
    $dob = trim($student->birthdate ?? '');

    try {

        if(str_contains($dob, '/'))
        {
            echo \Carbon\Carbon::createFromFormat(
                'd/m/Y',
                $dob
            )->format('d-m-Y');
        }
        elseif(str_contains($dob, '-'))
        {
            echo \Carbon\Carbon::createFromFormat(
                'd-m-Y',
                $dob
            )->format('d-m-Y');
        }

    } catch (\Exception $e) {

        echo $dob;
    }
@endphp
</td>

<td class="border p-2 text-center">
@if(!empty($student->admitdate))
    {{ \Carbon\Carbon::createFromFormat('d/m/Y', trim($student->admitdate))->format('d-m-Y') }}
@endif
</td>
<td class="border p-2 text-center">
    @if(
        strtoupper(trim($student->status ?? 'ACTIVE'))
        == 'ACTIVE'
    )
        <span class="text-green-600 font-bold">
            Active
        </span>
    @else
        <span class="text-red-600 font-bold">
            Left
        </span>
    @endif
</td>


</tr>




@empty


<tr>


<td colspan="4"
class="border p-4 text-center">


No Students Found


</td>


</tr>


@endforelse



</tbody>


</table>



</div>


</x-app-layout>