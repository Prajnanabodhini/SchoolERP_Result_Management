<x-app-layout>

<div class="max-w-5xl mx-auto py-4 px-4">


<div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100 rounded-xl shadow-xl border-4 border-amber-400 p-5">


<h2 class="text-2xl font-bold text-blue-700 mb-4">
    Edit Exam Pattern Subject Allocation
</h2>



<form method="POST"
action="{{ route('exam-pattern-details.update',$detail->id) }}">

@csrf
@method('PUT')



<div class="grid grid-cols-2 gap-4 mb-5">


<div>

<label class="font-semibold">
Exam Pattern
</label>


<select name="exam_pattern_id"
        class="border rounded w-full p-2"
        required>


@foreach($patterns as $pattern)

<option value="{{ $pattern->id }}"
@if($pattern->id == $detail->exam_pattern_id)
selected
@endif
>

{{ $pattern->pattern_name }}

</option>

@endforeach


</select>


</div>




<div>

<label class="font-semibold">
Standard
</label>


<select name="standard_id"
class="border rounded w-full p-2"
required>


@foreach($standards as $standard)

<option value="{{ $standard->id }}"
@if($standard->id == $detail->standard_id)
selected
@endif
>

{{ $standard->standard_name }}

</option>


@endforeach


</select>


</div>


</div>




<h3 class="font-bold text-blue-700 mb-3">
Select Subjects
</h3>



<div class="grid grid-cols-3 gap-3 bg-white p-4 border rounded">


@foreach($subjects as $subject)


<label class="border p-2 rounded hover:bg-yellow-50">


<input type="checkbox"
name="subjects[]"
value="{{ $subject->id }}"

@if(in_array($subject->id,$selectedSubjects))
checked
@endif

>


{{ $subject->subject_name }}


</label>



@endforeach


</div>



<div class="mt-5">


<button class="erp-btn erp-btn-save">

Update Allocation

</button>



<a href="{{ route('exam-pattern-details.index') }}"
class="erp-btn">

Back

</a>


</div>



</form>


</div>

</div>


</x-app-layout>