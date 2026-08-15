<x-app-layout>

<div class="max-w-5xl mx-auto py-4 px-4">

<div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100 rounded-xl shadow-xl border-4 border-amber-400 p-5">


<h2 class="text-2xl font-bold text-blue-700 mb-4">
    Exam Pattern Subject Allocation
</h2>


<form method="POST"
      action="{{ route('exam-pattern-details.store') }}">

@csrf


<div class="grid grid-cols-2 gap-4 mb-5">


<div>

<label class="font-semibold">
Exam Pattern
</label>


<select name="exam_pattern_id"
        class="border rounded w-full p-2"
        required>


<option value="">
Select Pattern
</option>


@foreach($patterns as $pattern)

<option value="{{ $pattern->id }}">

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


</div>



<h3 class="font-bold text-blue-700 mb-3">
Select Subjects
</h3>


<div id="subject-area"
class="grid grid-cols-3 gap-3 bg-white p-4 border rounded">

<p class="text-gray-500">
Select standard to load subjects
</p>

</div>


<div class="mt-5">


<button class="erp-btn erp-btn-save">

Save Allocation

</button>



<a href="{{ route('exam-pattern-details.index') }}"
class="erp-btn">

Back

</a>


</div>


</form>


</div>

</div>
<script>

document
.querySelector('select[name="standard_id"]')
.addEventListener('change', function(){


let standardId = this.value;


let area = document.getElementById('subject-area');


area.innerHTML = "Loading subjects...";


if(!standardId)
{
    area.innerHTML =
    "Select standard to load subjects";

    return;
}



fetch(
    "/exam-pattern-details/get-subjects/"
    + standardId
)

.then(response => response.json())

.then(data => {


area.innerHTML = "";


data.forEach(function(subject){


area.innerHTML += `

<label class="border p-2 rounded hover:bg-yellow-50">

<input type="checkbox"
name="subjects[]"
value="${subject.id}">

${subject.subject_name}

</label>

`;


});


});


});


</script>
</x-app-layout>