<x-app-layout>

<div class="max-w-7xl mx-auto py-6 px-4">

<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

<h2 class="text-2xl font-bold text-blue-600 mb-6">
    Role Permission Master
</h2>

@if(session('success'))

<div class="bg-green-100 text-green-700 p-3 rounded mb-4">
    {{ session('success') }}
</div>

@endif

<form method="GET"
      action="{{ route('role-permissions.index') }}"
      class="mb-4">

<label class="font-semibold">
Role
</label>

<select
name="role_id"
onchange="this.form.submit()"
class="border rounded p-2"
style="min-width:250px;"

>

<option value="">
Select Role
</option>

@foreach($roles as $role)

<option
    value="{{ $role->id }}"
    {{ request('role_id') == $role->id ? 'selected' : '' }}
>
    {{ $role->name }}
</option>

@endforeach

</select>

</form>

@if(request('role_id'))

<form method="POST"
      action="{{ route('role-permissions.store') }}">

@csrf

<input
type="hidden"
name="role_id"
value="{{ request('role_id') }}"

>

<table class="w-full border border-gray-300">

<thead class="bg-blue-100">

<tr>

<th class="border p-2">
Menu / Sub Menu
</th>

<th class="border p-2 text-center">
View
<br>
<input type="checkbox"
       class="column-check"
       data-column="view">
</th>

<th class="border p-2 text-center">
Add
<br>
<input type="checkbox"
       class="column-check"
       data-column="add">
</th>

<th class="border p-2 text-center">
Edit
<br>
<input type="checkbox"
       class="column-check"
       data-column="edit">
</th>

<th class="border p-2 text-center">
Delete
<br>
<input type="checkbox"
       class="column-check"
       data-column="delete">
</th>

<th class="border p-2 text-center">
Print
<br>
<input type="checkbox"
       class="column-check"
       data-column="print">
</th>

<th class="border p-2 text-center">
Export
<br>
<input type="checkbox"
       class="column-check"
       data-column="export">
</th>

<th class="border p-2 text-center">
All
</th>

</tr>

</thead>

<tbody>

@foreach($menus as $group => $items)

<tr style="background:#DBEAFE;">
    <td colspan="8" style="font-weight:bold;">
        {{ $group }}
    </td>
</tr>

@foreach($items as $item)

@php
$perm = $permissions[$item['name']] ?? null;
@endphp

<tr>

<td class="border p-2">
    {{ $item['name'] }}
</td>

<td align="center">

    <input type="hidden"
           name="permissions[{{ $item['name'] }}][view]"
           value="0">

    <input type="checkbox"
           class="view row-checkbox"
           name="permissions[{{ $item['name'] }}][view]"
           value="1"
           {{ $perm && $perm->can_view ? 'checked' : '' }}>
</td>

<td align="center">

    <input type="hidden"
           name="permissions[{{ $item['name'] }}][add]"
           value="0">

    <input type="checkbox"
           class="add row-checkbox"
           name="permissions[{{ $item['name'] }}][add]"
           value="1"
           {{ $perm && $perm->can_add ? 'checked' : '' }}>
</td>

<td align="center">

    <input type="hidden"
           name="permissions[{{ $item['name'] }}][edit]"
           value="0">

    <input type="checkbox"
           class="edit row-checkbox"
           name="permissions[{{ $item['name'] }}][edit]"
           value="1"
           {{ $perm && $perm->can_edit ? 'checked' : '' }}>
</td>

<td align="center">

    <input type="hidden"
           name="permissions[{{ $item['name'] }}][delete]"
           value="0">

    <input type="checkbox"
           class="delete row-checkbox"
           name="permissions[{{ $item['name'] }}][delete]"
           value="1"
           {{ $perm && $perm->can_delete ? 'checked' : '' }}>
</td>

<td align="center">

    <input type="hidden"
           name="permissions[{{ $item['name'] }}][print]"
           value="0">

    <input type="checkbox"
           class="print row-checkbox"
           name="permissions[{{ $item['name'] }}][print]"
           value="1"
           {{ $perm && $perm->can_print ? 'checked' : '' }}>
</td>

<td align="center">

    <input type="hidden"
           name="permissions[{{ $item['name'] }}][export]"
           value="0">

    <input type="checkbox"
           class="export row-checkbox"
           name="permissions[{{ $item['name'] }}][export]"
           value="1"
           {{ $perm && $perm->can_export ? 'checked' : '' }}>
</td>

<td align="center">
    <input type="checkbox"
           class="row-all">
</td>

</tr>

@endforeach

@endforeach

</tbody>

</table>

<div class="mt-6 text-right">

<button
    type="submit"
    style="
        width:fit-content !important;
        min-width:unset !important;
        display:inline-block !important;
        padding:8px 16px;
        background:#16A34A;
        color:white;
    ">
    Save Permissions
</button>

</div>

</form>

@endif

</div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    // Column Select All
    document.querySelectorAll('.column-check').forEach(function(header){

        header.addEventListener('change', function(){

            let column = this.dataset.column;

            document.querySelectorAll('.' + column).forEach(function(cb){
                cb.checked = header.checked;
            });

        });

    });

    // Row Select All
    document.querySelectorAll('.row-all').forEach(function(rowAll){

        rowAll.addEventListener('change', function(){

            let row = this.closest('tr');

            row.querySelectorAll('.row-checkbox').forEach(function(cb){
                cb.checked = rowAll.checked;
            });

        });

    });

});

</script>
</x-app-layout>
