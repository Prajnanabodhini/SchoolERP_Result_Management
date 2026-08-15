<x-app-layout>

<div class="max-w-4xl mx-auto py-4 px-4">


<div class="bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100 rounded-xl shadow-xl border-4 border-amber-400 p-5">

    <h2 class="text-2xl font-bold text-green-600 text-center mb-5">
        Add Subject Type
    </h2>

    <form method="POST" action="{{ route('subject-types.store') }}">
        @csrf

        <div class="mb-3">

            <label class="block font-semibold mb-2">
                Subject Type Name
            </label>

            <input type="text"
                   name="type_name"
                   class="w-full border rounded p-2"
                   required>

            @error('type_name')
                <div class="text-red-500 mt-1">
                    {{ $message }}
                </div>
            @enderror

        </div>

        <div class="mb-4">

            <label class="block font-semibold mb-2">
                Description
            </label>

            <textarea name="description"
                      class="w-full border rounded p-2"
                      rows="2"></textarea>

        </div>

        <div class="flex items-center justify-center gap-4">

            <label class="font-semibold">
                <input type="checkbox"
                       name="is_active"
                       checked>
                Active
            </label>

            <button type="submit"
                    class="erp-btn erp-btn-save">
                Save
            </button>

            <a href="{{ route('subject-types.index') }}"
               class="erp-btn erp-btn-cancel">
                Cancel
            </a>

        </div>

    </form>

</div>


</div>

</x-app-layout>
