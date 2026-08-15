<x-app-layout>

<div class="max-w-7xl mx-auto py-6 px-4">

<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

<div class="flex justify-between items-center mb-4">

    <h2 class="text-2xl font-bold text-blue-600">
        Academic Year Master
    </h2>

    <a href="{{ route('academic-years.create') }}"
       class="erp-btn erp-btn-add">
        + Add Academic Year
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
        <th class="border p-2">Academic Year</th>
        <th class="border p-2">Start Date</th>
        <th class="border p-2">End Date</th>
        <th class="border p-2">Current</th>
        <th class="border p-2">Status</th>
        <th class="border p-2">Action</th>
    </tr>

    </thead>

    <tbody>

    @forelse($years as $year)

        <tr>

            <td class="border p-2">{{ $year->id }}</td>
            <td class="border p-2">{{ $year->year_name }}</td>
            <td class="border p-2">{{ $year->start_date }}</td>
            <td class="border p-2">{{ $year->end_date }}</td>
            <td class="border p-2">{{ $year->is_current ? 'Yes' : 'No' }}</td>
            <td class="border p-2">{{ $year->is_active ? 'Active' : 'Inactive' }}</td>

            <td class="border p-2">

                <a href="{{ route('academic-years.edit',$year->id) }}"
                   class="erp-btn erp-btn-edit">
                    Edit
                </a>

                <form action="{{ route('academic-years.destroy',$year->id) }}"
                      method="POST"
                      style="display:inline-block;">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="erp-btn erp-btn-delete"
                            onclick="return confirm('Delete Academic Year?')">
                        Delete
                    </button>

                </form>

            </td>

        </tr>

    @empty

        <tr>
            <td colspan="7" class="text-center p-4">
                No Academic Years Found
            </td>
        </tr>

    @endforelse

    </tbody>

</table>

</div>

</div>

</x-app-layout>