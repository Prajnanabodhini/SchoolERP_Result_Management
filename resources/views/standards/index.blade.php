<x-app-layout>

<div class="max-w-7xl mx-auto py-4 px-4">

<div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100 rounded-xl shadow-xl border-4 border-amber-400 p-5">

    <div class="flex justify-between items-center mb-4">

        <h2 class="text-2xl font-bold text-blue-700">
            Standard Master
        </h2>

        <a href="{{ route('standards.create') }}"
           class="erp-btn erp-btn-add">
            + Add Standard
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
                <th class="border p-2">Standard Name</th>
                <th class="border p-2">Display Order</th>
                <th class="border p-2">Status</th>
                <th class="border p-2">Action</th>
            </tr>

        </thead>

        <tbody>

            @foreach($standards as $standard)

            <tr class="hover:bg-yellow-50">

                <td class="border p-2">
                    {{ $standard->id }}
                </td>

                <td class="border p-2">
                    {{ $standard->standard_name }}
                </td>

                <td class="border p-2">
                    {{ $standard->display_order }}
                </td>

                <td class="border p-2">
                    {{ $standard->is_active ? 'Active' : 'Inactive' }}
                </td>

                <td class="border p-2 text-center">

                    <a href="{{ route('standards.edit',$standard->id) }}"
                       class="erp-btn erp-btn-edit">
                        Edit
                    </a>

                    <form action="{{ route('standards.destroy',$standard->id) }}"
                          method="POST"
                          style="display:inline-block;">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="erp-btn erp-btn-delete"
                                onclick="return confirm('Delete this Standard?')">
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