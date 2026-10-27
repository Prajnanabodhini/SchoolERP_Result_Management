<x-app-layout>
@if ($errors->has('name'))
<div style="
    background:#FEE2E2;
    color:#B91C1C;
    border:1px solid #EF4444;
    padding:12px;
    margin-bottom:15px;
    border-radius:6px;
    font-weight:bold;">
    {{ $errors->first('name') }}
</div>
@endif

<div style="max-width:700px; margin:auto; padding:20px;">

<div style="background:white;
            border-radius:12px;
            padding:20px;
            border:1px solid #d1d5db;
            box-shadow:0 4px 10px rgba(0,0,0,0.15);">

<h2 class="text-2xl font-bold text-blue-600 text-center mb-6">
    Edit User
</h2>

<form method="POST" action="{{ route('users.update', $user->id) }}">
    @csrf
    @method('PUT')

    <div class="mb-4">
        <label class="block font-semibold mb-2">Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border rounded p-2" required>
    </div>

    <div class="mb-4">
        <label class="block font-semibold mb-2">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border rounded p-2" required>
    </div>

    <div class="mb-4">
        <label class="block font-semibold mb-2">Role</label>
        <select name="role" class="w-full border rounded p-2" required>
            <option value="">Select Role</option>
            
            @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ $user->role == $role->name ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endforeach

        </select>
    </div>

    {{-- PASSWORD FIELD (VISIBLE PLAIN TEXT) --}}
    <div class="mb-4">
        <label class="block font-semibold mb-2">Password</label>
        <input 
            type="text" 
            name="password" 
            value="{{ old('password', $user->password) }}" 
            class="w-full border rounded p-2"
            required
        >
    </div>

    <div class="flex justify-between items-center mt-6">
        <label>
            <input type="checkbox" name="is_active" {{ $user->is_active ? 'checked' : '' }}> Active
        </label>
        <div>
            <button type="submit" class="erp-btn erp-btn-save">Update</button>
            <a href="{{ route('users.index') }}" class="erp-btn erp-btn-cancel">Cancel</a>
        </div>
    </div>

</form>

</div>
</div>
</x-app-layout>