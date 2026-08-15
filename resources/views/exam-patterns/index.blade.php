<x-app-layout>

<div class="max-w-7xl mx-auto py-4 px-4">

<div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100 rounded-xl shadow-xl border-4 border-amber-400 p-5">


<div class="flex justify-between items-center mb-4">

    <h2 class="text-2xl font-bold text-blue-700">
        Exam Pattern Master
    </h2>


    <a href="{{ route('exam-patterns.create') }}"
       class="erp-btn erp-btn-add">
        + Add Exam Pattern
    </a>

</div>


@if(session('success'))

<div class="bg-green-100 text-green-700 p-3 rounded mb-4">
    {{ session('success') }}
</div>

@endif



<table class="w-full table-fixed border border-gray-400 bg-white text-sm">

<thead class="bg-blue-200">

<tr>

<th class="border p-2 w-[20%]">
    Pattern Name
</th>


<th class="border p-2 w-[20%]">
    Pattern Type
</th>


<th class="border p-2">
    Description
</th>


<th class="border p-2 w-[15%]">
    Status
</th>


<th class="border p-2 w-[18%]">
    Action
</th>

</tr>

</thead>


<tbody>


@forelse($patterns as $pattern)


<tr class="hover:bg-yellow-50">


<td class="border p-2">
    {{ $pattern->pattern_name }}
</td>


<td class="border p-2">
    {{ $pattern->pattern_type }}
</td>


<td class="border p-2">
    {{ $pattern->description }}
</td>


<td class="border p-2 text-center">

@if($pattern->is_active)

<span class="text-green-700 font-bold">
Active
</span>

@else

<span class="text-red-700 font-bold">
Inactive
</span>

@endif

</td>



<td class="border p-2 text-center whitespace-nowrap">


<a href="{{ route('exam-patterns.edit',$pattern->id) }}"
style="
background:#f59e0b;
color:white;
padding:8px 14px;
border-radius:6px;
font-weight:600;
text-decoration:none;
">
Edit
</a>



<form action="{{ route('exam-patterns.destroy',$pattern->id) }}"
method="POST"
style="display:inline-block;">

@csrf
@method('DELETE')


<button type="submit"
style="
background:#dc2626;
color:white;
padding:8px 14px;
border:none;
border-radius:6px;
font-weight:600;
cursor:pointer;
"
onclick="return confirm('Delete Exam Pattern?')">

Delete

</button>


</form>


</td>


</tr>


@empty


<tr>

<td colspan="5"
class="border p-4 text-center">

No Records Found

</td>

</tr>


@endforelse


</tbody>


</table>


</div>

</div>

</x-app-layout>