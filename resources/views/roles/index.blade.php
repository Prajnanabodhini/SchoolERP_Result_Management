<x-app-layout>

<div class="max-w-7xl mx-auto py-6 px-4">


<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

    <div class="flex justify-between items-center mb-4">

        <h2 class="text-2xl font-bold text-blue-600">
            Role Master
        </h2>

        <a href="{{ route('roles.create') }}"
           class="erp-btn erp-btn-add">
            + Add Role
        </a>

    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full border">

        <thead class="bg-blue-100">

            <tr>
                <th class="border p-2">ID</th>
                <th class="border p-2">Role Name</th>
                <th class="border p-2">Description</th>
                <th class="border p-2">Status</th>
                <th class="border p-2">Action</th>
            </tr>

        </thead>

        <tbody>

            @forelse($roles as $role)

            <tr>

                <td class="border p-2">
                    {{ $role->id }}
                </td>

                <td class="border p-2">
                    {{ $role->name }}
                </td>

                <td class="border p-2">
                    {{ $role->description }}
                </td>

                <td class="border p-2">
                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                </td>

                <td class="border p-2">

                    <a href="{{ route('roles.edit', $role->id) }}"
                       class="erp-btn erp-btn-edit">
                        Edit
                    </a>

                    <form action="{{ route('roles.destroy', $role->id) }}"
                          method="POST"
                          style="display:inline-block; margin-left:8px;">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="erp-btn erp-btn-delete"
                                onclick="return confirm('Delete this Role?')">
                            Delete
                        </button>

                    </form>

                </td>

            </tr>

            @empty

            <tr>
                <td colspan="5" class="border p-4 text-center">
                    No Roles Found
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>


</div>

</x-app-layout>
