<x-app-layout>

<div class="max-w-7xl mx-auto py-6 px-4">

<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

<div class="flex justify-between items-center mb-4">


<h2 class="text-2xl font-bold text-blue-600">
    User Master
</h2>

<a href="{{ route('users.create') }}"
   class="erp-btn erp-btn-add">
    + Add User
</a>


</div>

@if(session('success')) <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
{{ session('success') }} </div>
@endif

<table class="w-full border">


<thead class="bg-blue-100">

<tr>
    <th class="border p-2">ID</th>
    <th class="border p-2">Name</th>
    <th class="border p-2">Email</th>
    <th class="border p-2">Role</th>
    <th class="border p-2">Status</th>
    <th class="border p-2">Action</th>
</tr>

</thead>

<tbody>

@forelse($users as $user)

    <tr>

        <td class="border p-2">
            {{ $user->id }}
        </td>

        <td class="border p-2">
            {{ $user->name }}
        </td>

        <td class="border p-2">
            {{ $user->email }}
        </td>

        <td class="border p-2">
            {{ $user->role }}
        </td>

        <td class="border p-2">
            {{ $user->is_active ? 'Active' : 'Inactive' }}
        </td>

        <td class="border p-2">

            <a href="{{ route('users.edit',$user->id) }}"
               class="erp-btn erp-btn-edit">
                Edit
            </a>

            <form action="{{ route('users.destroy',$user->id) }}"
                  method="POST"
                  style="display:inline-block;">

                @csrf
                @method('DELETE')

                <button type="submit"
                        class="erp-btn erp-btn-delete"
                        onclick="return confirm('Delete User?')">
                    Delete
                </button>

            </form>

        </td>

    </tr>

@empty

    <tr>
        <td colspan="6" class="text-center p-4">
            No Users Found
        </td>
    </tr>

@endforelse

</tbody>


</table>

</div>

</div>

</x-app-layout>
