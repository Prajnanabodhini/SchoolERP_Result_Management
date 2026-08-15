<x-app-layout>

<div class="max-w-3xl mx-auto py-4 px-4">

<div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100 rounded-xl shadow-xl border-4 border-amber-400 p-5">


<h2 class="text-2xl font-bold text-blue-700 mb-4">
    Edit Exam Pattern
</h2>


<form method="POST"
      action="{{ route('exam-patterns.update',$examPattern->id) }}">

@csrf
@method('PUT')


<div class="mb-3">

<label class="font-semibold">
    Pattern Name
</label>

<input type="text"
       name="pattern_name"
       value="{{ $examPattern->pattern_name }}"
       class="border rounded w-full p-2"
       required>

</div>



<div class="mb-3">

<label class="font-semibold">
    Pattern Type
</label>

<input type="text"
       name="pattern_type"
       value="{{ $examPattern->pattern_type }}"
       class="border rounded w-full p-2">

</div>



<div class="mb-3">

<label class="font-semibold">
    Description
</label>

<textarea name="description"
          class="border rounded w-full p-2">{{ $examPattern->description }}</textarea>

</div>



<div class="mb-3">

<label class="font-semibold">
    Status
</label>


<select name="is_active"
        class="border rounded w-full p-2">


<option value="1"
{{ $examPattern->is_active == 1 ? 'selected':'' }}>
Active
</option>


<option value="0"
{{ $examPattern->is_active == 0 ? 'selected':'' }}>
Inactive
</option>


</select>

</div>



<button class="erp-btn erp-btn-save">
    Update
</button>


<a href="{{ route('exam-patterns.index') }}"
   class="erp-btn">

    Back

</a>


</form>


</div>

</div>

</x-app-layout>