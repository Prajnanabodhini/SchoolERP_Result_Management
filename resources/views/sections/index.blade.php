<x-app-layout>

<div class="max-w-7xl mx-auto py-4 px-4">

<div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100 rounded-xl shadow-xl border-4 border-amber-400 p-5">

    <div class="flex justify-between items-center mb-4">

        <h2 class="text-2xl font-bold text-blue-700">
            Section Master
        </h2>

        <a href="{{ route('sections.create') }}"
           class="erp-btn erp-btn-add">
            + Add Section
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
            <th class="border p-2">Section Name</th>
            <th class="border p-2">Display Order</th>
            <th class="border p-2">Status</th>
            <th class="border p-2">Action</th>
        </tr>

        </thead>

        <tbody>

        @foreach($sections as $section)

        <tr class="hover:bg-yellow-50">

            <td class="border p-2">{{ $section->id }}</td>

            <td class="border p-2">
                {{ $section->section_name }}
            </td>

            <td class="border p-2">
                {{ $section->display_order }}
            </td>

            <td class="border p-2">
                {{ $section->is_active ? 'Active' : 'Inactive' }}
            </td>

            <td class="border p-2 text-center">

                <a href="{{ route('sections.edit',$section->id) }}"
                   class="erp-btn erp-btn-edit">
                    Edit
                </a>

                <form action="{{ route('sections.destroy',$section->id) }}"
                      method="POST"
                      style="display:inline-block;">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="erp-btn erp-btn-delete"
                            onclick="return confirm('Delete this Section?')">
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