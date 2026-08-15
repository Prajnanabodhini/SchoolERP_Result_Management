<x-app-layout>

<div style="max-width:700px; margin:auto; padding:20px;">

<div style="background:white;
            border-radius:12px;
            padding:20px;
            border:1px solid #d1d5db;
            box-shadow:0 4px 10px rgba(0,0,0,0.15);">

<h2 class="text-2xl font-bold text-green-600 text-center mb-6">
    Add Standard
</h2>

@if ($errors->any())
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ route('standards.store') }}">

    @csrf

    <div class="mb-4">

        <label class="block font-semibold mb-2">
            Standard Name
        </label>

        <input type="text"
               name="standard_name"
               value="{{ old('standard_name') }}"
               class="w-full border rounded p-2"
               required>

    </div>

    <div class="mb-4">

        <label class="block font-semibold mb-2">
            Display Order
        </label>

        <input type="number"
               name="display_order"
               value="{{ old('display_order',0) }}"
               class="w-full border rounded p-2">

    </div>

    <div class="flex justify-between items-center mt-6">

        <label>
            <input type="checkbox"
                   name="is_active"
                   checked>
            Active
        </label>

        <div>

            <button type="submit"
                    class="erp-btn erp-btn-save">
                Save
            </button>

            <a href="{{ route('standards.index') }}"
               class="erp-btn erp-btn-cancel">
                Cancel
            </a>

        </div>

    </div>

</form>

</div>

</div>

</x-app-layout>