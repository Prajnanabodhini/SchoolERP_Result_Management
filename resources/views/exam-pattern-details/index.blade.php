<x-app-layout>

<div class="max-w-7xl mx-auto py-4 px-4">


<div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100 rounded-xl shadow-xl border-4 border-amber-400 p-5">


<div class="flex justify-between items-center mb-4">


<h2 class="text-2xl font-bold text-blue-700">
    Exam Pattern Subject Allocation
</h2>


<a href="{{ route('exam-pattern-details.create') }}"
   class="erp-btn erp-btn-add">

    + Add Allocation

</a>


</div>



@if(session('success'))

<div class="bg-green-100 text-green-700 p-3 rounded mb-4">

    {{ session('success') }}

</div>

@endif




<table class="w-full border border-gray-400 bg-white text-sm">


<thead class="bg-blue-200">

<tr>

<th class="border p-2">
    Pattern
</th>


<th class="border p-2">
    Standard
</th>


<th class="border p-2">
    Subject
</th>


<th class="border p-2">
    Display Order
</th>


<th class="border p-2">
    Action
</th>


</tr>

</thead>



<tbody>


@forelse($details as $detail)


<tr class="hover:bg-yellow-50">


<td class="border p-2">

    {{ $detail->examPattern->pattern_name ?? '' }}

</td>



<td class="border p-2">

    {{ $detail->standard->standard_name ?? '' }}

</td>



<td class="border p-2">

    {{ $detail->subject->subject_name ?? '' }}

</td>



<td class="border p-2 text-center">

    {{ $detail->display_order }}

</td>



<td class="border p-2 text-center">


<form method="POST"
      action="{{ route('exam-pattern-details.destroy',$detail->id) }}"
      style="display:inline-block;">


@csrf

@method('DELETE')

<a href="{{ route('exam-pattern-details.edit',$detail->id) }}"
style="
background:#f59e0b;
color:white;
padding:8px 14px;
border-radius:6px;
font-weight:600;
text-decoration:none;
margin-right:5px;
">

Edit

</a>
<button type="submit"

style="
background:#dc2626;
color:white;
padding:8px 14px;
border:none;
border-radius:6px;
font-size:14px;
font-weight:600;
cursor:pointer;
"

onclick="return confirm('Remove Subject Allocation?')">


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