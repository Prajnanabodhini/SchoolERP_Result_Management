<x-app-layout>

<div class="max-w-4xl mx-auto py-6 px-4">

<h2 class="text-2xl font-bold mb-4">
    Add Academic Year
</h2>

<form method="POST"
      action="{{ route('academic-years.store') }}">

    @csrf

    <div class="mb-4">

        <label>Academic Year</label>

        <input type="text"
               name="year_name"
               class="border w-full p-2"
               placeholder="2026-2027">

    </div>

    <div class="mb-4">

        <label>Start Date</label>

        <input type="date"
               name="start_date"
               class="border w-full p-2">

    </div>

    <div class="mb-4">

        <label>End Date</label>

        <input type="date"
               name="end_date"
               class="border w-full p-2">

    </div>

    <div class="mb-4">

        <label>
            <input type="checkbox"
                   name="is_current">
            Current Year
        </label>

    </div>

    <div class="mb-4">

        <label>
            <input type="checkbox"
                   name="is_active"
                   checked>
            Active
        </label>

    </div>

    <button type="submit"
            class="bg-green-600 text-white px-4 py-2 rounded">
        Save
    </button>

</form>

</div>

</x-app-layout>