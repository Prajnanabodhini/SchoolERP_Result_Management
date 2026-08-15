<x-app-layout>

<div style="max-width:700px; margin:auto; padding:20px;">

<div style="background:white;border-radius:12px;padding:20px;border:1px solid #d1d5db;box-shadow:0 4px 10px rgba(0,0,0,0.15);">

<h2 class="text-2xl font-bold text-yellow-600 text-center mb-6">
    Edit Division
</h2>

<form method="POST"
      action="{{ route('divisions.update',$division->id) }}">

@csrf
@method('PUT')

<div class="mb-4">

<label class="block font-semibold mb-2">
    Division Name
</label>

<input type="text"
       name="division_name"
       value="{{ $division->division_name }}"
       class="w-full border rounded p-2"
       required>

</div>

<div class="mb-4">

<label class="block font-semibold mb-2">
    Display Order
</label>

<input type="number"
       name="display_order"
       value="{{ $division->display_order }}"
       class="w-full border rounded p-2">

</div>

<div class="flex justify-between items-center mt-6">

<label>
<input type="checkbox"
       name="is_active"
       {{ $division->is_active ? 'checked' : '' }}>
Active
</label>

<div>

<button type="submit"
        class="erp-btn erp-btn-save">
    Update
</button>

<a href="{{ route('divisions.index') }}"
   class="erp-btn erp-btn-cancel">
    Cancel
</a>

</div>

</div>

</form>

</div>

</div>

</x-app-layout>