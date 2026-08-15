<x-app-layout>

<div class="max-w-7xl mx-auto py-4 px-4">

<div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100 rounded-xl shadow-xl border-4 border-amber-400 p-5">

    <div class="flex justify-between items-center mb-4">

        <h2 class="text-2xl font-bold text-blue-700">
            Division Master
        </h2>

        <a href="{{ route('divisions.create') }}"
           class="erp-btn erp-btn-add">
            + Add Division
        </a>

    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full border border-gray-400 bg-white">

        <thead class="bg-blue-200">

        <tr>
            <th class="border p-2">ID</th>
            <th class="border p-2">Division Name</th>
            <th class="border p-2">Display Order</th>
            <th class="border p-2">Status</th>
            <th class="border p-2">Action</th>
        </tr>

        </thead>

        <tbody>

        @foreach($divisions as $division)

        <tr class="hover:bg-yellow-50">

            <td class="border p-2">{{ $division->id }}</td>

            <td class="border p-2">
                {{ $division->division_name }}
            </td>

            <td class="border p-2">
                {{ $division->display_order }}
            </td>

            <td class="border p-2">
                {{ $division->is_active ? 'Active' : 'Inactive' }}
            </td>

            <td class="border p-2 text-center">

                <a href="{{ route('divisions.edit',$division->id) }}"
                   class="erp-btn erp-btn-edit">
                    Edit
                </a>

                <form action="{{ route('divisions.destroy',$division->id) }}"
                      method="POST"
                      style="display:inline-block;">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="erp-btn erp-btn-delete"
                            onclick="return confirm('Delete this Division?')">
                        Delete
                    </button>

                </form>

            </td>

        </tr>

        @endforeach

        </tbody>

    </table>

</div>

</div>

</x-app-layout>