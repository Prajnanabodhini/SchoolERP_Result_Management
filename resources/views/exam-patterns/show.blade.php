<x-app-layout>

<div class="max-w-3xl mx-auto py-4 px-4">


<div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100 rounded-xl shadow-xl border-4 border-amber-400 p-5">


<h2 class="text-2xl font-bold text-blue-700 mb-4">
    Exam Pattern Details
</h2>



<table class="w-full border border-gray-400 bg-white">


<tr>
<td class="border p-2 font-bold">
Pattern Name
</td>

<td class="border p-2">
{{ $examPattern->pattern_name }}
</td>
</tr>



<tr>
<td class="border p-2 font-bold">
Pattern Type
</td>

<td class="border p-2">
{{ $examPattern->pattern_type }}
</td>
</tr>



<tr>
<td class="border p-2 font-bold">
Description
</td>

<td class="border p-2">
{{ $examPattern->description }}
</td>
</tr>



<tr>
<td class="border p-2 font-bold">
Status
</td>

<td class="border p-2">

@if($examPattern->is_active)

<span class="text-green-700 font-bold">
Active
</span>

@else

<span class="text-red-700 font-bold">
Inactive
</span>

@endif

</td>
</tr>


</table>



<div class="mt-4">

<a href="{{ route('exam-patterns.index') }}"
class="erp-btn">

Back

</a>

</div>



</div>

</div>


</x-app-layout>