<x-app-layout>

<div style="max-width:700px; margin:auto; padding:20px;">

<div style="background:white;
            border-radius:12px;
            padding:20px;
            border:1px solid #d1d5db;
            box-shadow:0 4px 10px rgba(0,0,0,0.15);">

<h2 class="text-2xl font-bold text-green-600 text-center mb-6">
    Teacher Subject Allocation
</h2>

<form method="POST"
      action="{{ route('teacher-subject-allocation.store') }}">

@csrf

<div class="mb-4">

<label class="block font-semibold mb-2">
Teacher Class
</label>

<select id="allocation"
        name="teacher_class_allocation_id"
        class="w-full border rounded p-2"
        required>

<option value="">
Select Teacher Class
</option>

@foreach($classAllocations as $row)

<option value="{{ $row->id }}">

{{ $row->teacher->name }}
-
{{ $row->standard->standard_name }}
-
{{ $row->division->division_name }}

</option>

@endforeach

</select>

</div>

<div class="mb-4">

<label class="block font-semibold mb-2">
Subject
</label>

<select id="subject"
        name="subject_id"
        class="w-full border rounded p-2"
        required>

<option value="">
Select Subject
</option>

</select>

</div>
<div class="mb-4">

<label class="block font-semibold mb-2">
Exam
</label>

<select
    name="exam_master_id"
    class="w-full border rounded p-2"
    required>

    <option value="">
        Select Exam
    </option>

    @foreach($exams as $exam)

        <option value="{{ $exam->id }}">
            {{ $exam->exam_name }}
        </option>

    @endforeach

</select>

</div>
<div class="mt-6">

<button type="submit"
        class="erp-btn erp-btn-save">
Save
</button>

<a href="{{ route('teacher-subject-allocation.index') }}"
   class="erp-btn erp-btn-cancel">
Cancel
</a>

</div>

</form>

</div>

</div>

<script>

document.getElementById('allocation')
.addEventListener('change', function(){

    let id = this.value;

    fetch("{{ url('teacher-subject-allocation/subjects') }}/" + id)
    .then(res => res.json())
    .then(data => {

        let subject =
            document.getElementById('subject');

        subject.innerHTML =
            '<option value="">Select Subject</option>';

        data.forEach(function(item){

            subject.innerHTML +=
            '<option value="'+item.id+'">'+
            item.subject_name+
            '</option>';

        });

    });

});

</script>

</x-app-layout>