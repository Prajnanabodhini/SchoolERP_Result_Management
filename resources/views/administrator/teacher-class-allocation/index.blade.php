<x-app-layout>

<div class="max-w-7xl mx-auto py-6 px-4">

<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

<div class="flex justify-between items-center mb-4">

<h2 class="text-2xl font-bold text-blue-600">
    Teacher Class Allocation
</h2>

<a href="{{ route('teacher-class-allocation.create') }}"
   class="erp-btn erp-btn-add">
    + New Allocation
</a>

</div>

@if(session('success'))
<div class="bg-green-100 text-green-700 p-3 rounded mb-4">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="bg-red-100 text-red-700 p-3 rounded mb-4">
    {{ session('error') }}
</div>
@endif

<h2 class="text-xl font-bold text-blue-600">
    Total Records : {{ $allocations->total() }}
</h2>

<table class="w-full border">

<thead class="bg-blue-100">

<tr>
    <th class="border p-2">Teacher</th>
    <th class="border p-2">Academic Year</th>
    <th class="border p-2">Section</th>
    <th class="border p-2">Standard</th>
    <th class="border p-2">Division</th>
    <th class="border p-2">Class Teacher</th>
</tr>

</thead>

<tbody>

@forelse($allocations as $row)

<tr>

<td class="border p-2">
    {{ $row->teacher->name }}
</td>

<td class="border p-2">
    {{ $row->academicYear->year_name }}
</td>

<td class="border p-2">
    {{ $row->section->section_name }}
</td>

<td class="border p-2">
    {{ $row->standard->standard_name }}
</td>

<td class="border p-2">
    {{ $row->division->division_name }}
</td>

<td class="border p-2">
    {{ $row->is_class_teacher ? 'Yes' : 'No' }}
</td>

</tr>

@empty

<tr>
    <td colspan="6" class="text-center p-4">
        No Allocation Found
    </td>
</tr>

@endforelse

</tbody>

</table>
<div style="
    display:flex;
    justify-content:center;
    align-items:center;
    margin-top:15px;">
    
    {{ $allocations->onEachSide(2)->links() }}

</div>
</div>

</div>

</x-app-layout>