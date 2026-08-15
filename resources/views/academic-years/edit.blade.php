<x-app-layout>

<div class="max-w-4xl mx-auto py-6 px-4">

<h2 class="text-2xl font-bold mb-4">
    Edit Academic Year
</h2>

<form method="POST"
      action="{{ route('academic-years.update',$academic_year->id) }}">

    @csrf
    @method('PUT')

    <div class="mb-4">

        <label>Academic Year</label>

        <input type="text"
               name="year_name"
               value="{{ $academic_year->year_name }}"
               class="border w-full p-2">

    </div>

    <div class="mb-4">

        <label>Start Date</label>

        <input type="date"
               name="start_date"
               value="{{ $academic_year->start_date }}"
               class="border w-full p-2">

    </div>

    <div class="mb-4">

        <label>End Date</label>

        <input type="date"
               name="end_date"
               value="{{ $academic_year->end_date }}"
               class="border w-full p-2">

    </div>

    <div class="mb-4">

        <label>
            <input type="checkbox"
                   name="is_current"
                   {{ $academic_year->is_current ? 'checked' : '' }}>
            Current Year
        </label>

    </div>

    <div class="mb-4">

        <label>
            <input type="checkbox"
                   name="is_active"
                   {{ $academic_year->is_active ? 'checked' : '' }}>
            Active
        </label>

    </div>

    <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded">
        Update
    </button>

</form>

</div>

</x-app-layout>