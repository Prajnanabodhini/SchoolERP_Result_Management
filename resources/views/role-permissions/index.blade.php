<x-app-layout>

<div class="max-w-7xl mx-auto py-6 px-4">


<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

    <div class="flex justify-between items-center mb-4">


<h2 class="text-2xl font-bold text-blue-600">
    Role Permission Master
</h2>


</div>


    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

<form method="GET" action="{{ route('role-permissions.index') }}">


<div class="mb-4 flex items-center gap-3">

    <label class="font-semibold">
        Select Role
    </label>

    <select name="role_id"
            onchange="this.form.submit()"
            class="border rounded p-2">

        <option value="">
            Select Role
        </option>

        @foreach($roles as $role)

            <option value="{{ $role->id }}"
                {{ $selectedRole == $role->id ? 'selected' : '' }}>

                {{ $role->role_name }}

            </option>

        @endforeach

    </select>

</div>


</form>

    @if($selectedRole)

    <form method="POST"
          action="{{ route('role-permissions.store') }}">

        @csrf

        <input type="hidden"
               name="role_id"
               value="{{ $selectedRole }}">

        <table class="w-full border">

            <thead class="bg-blue-100">

                <tr>

                    <th class="border p-2">
                        Menu
                    </th>

                    <th class="border p-2">
                        View
                    </th>

                    <th class="border p-2">
                        Add
                    </th>

                    <th class="border p-2">
                        Edit
                    </th>

                    <th class="border p-2">
                        Delete
                    </th>

                    <th class="border p-2">
                        Print
                    </th>

                    <th class="border p-2">
                        Export
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($menus as $menu)

                <tr>

                    <td class="border p-2">
                        {{ $menu }}
                    </td>

                    <td class="border text-center">
                        <input type="checkbox"
                               name="permissions[{{ $menu }}][view]"
                               {{ isset($permissions[$menu]) && $permissions[$menu]->can_view ? 'checked' : '' }}>
                    </td>

                    <td class="border text-center">
                        <input type="checkbox"
                               name="permissions[{{ $menu }}][add]"
                               {{ isset($permissions[$menu]) && $permissions[$menu]->can_add ? 'checked' : '' }}>
                    </td>

                    <td class="border text-center">
                        <input type="checkbox"
                               name="permissions[{{ $menu }}][edit]"
                               {{ isset($permissions[$menu]) && $permissions[$menu]->can_edit ? 'checked' : '' }}>
                    </td>

                    <td class="border text-center">
                        <input type="checkbox"
                               name="permissions[{{ $menu }}][delete]"
                               {{ isset($permissions[$menu]) && $permissions[$menu]->can_delete ? 'checked' : '' }}>
                    </td>

                    <td class="border text-center">
                        <input type="checkbox"
                               name="permissions[{{ $menu }}][print]"
                               {{ isset($permissions[$menu]) && $permissions[$menu]->can_print ? 'checked' : '' }}>
                    </td>

                    <td class="border text-center">
                        <input type="checkbox"
                               name="permissions[{{ $menu }}][export]"
                               {{ isset($permissions[$menu]) && $permissions[$menu]->can_export ? 'checked' : '' }}>
                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

        <div class="mt-4">


<button type="submit"
        class="erp-btn erp-btn-save">
    Save Permissions
</button>


</div>


            <button type="submit"
                    class="erp-btn erp-btn-save">
                Save Permissions
            </button>

        </div>

    </form>

    @endif

</div>


</div>

</x-app-layout>
