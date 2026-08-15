<x-app-layout>

@if ($errors->any())
<div style="
    background:#FEE2E2;
    color:#B91C1C;
    border:1px solid #EF4444;
    padding:12px;
    margin-bottom:15px;
    border-radius:6px;">
    <ul style="margin:0;padding-left:20px;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div style="max-width:700px; margin:auto; padding:20px;">

<div style="background:white;
            border-radius:12px;
            padding:20px;
            border:1px solid #d1d5db;
            box-shadow:0 4px 10px rgba(0,0,0,0.15);">

<h2 class="text-2xl font-bold text-green-600 text-center mb-6">
    Add User
</h2>


<form method="POST" action="{{ route('users.store') }}">
    @csrf

    <div class="mb-4">
        <label class="block font-semibold mb-2">
            User Name
        </label>

        <input type="text"
               name="name"
               value="{{ old('name') }}"
               class="w-full border rounded p-2"
               required>
    </div>

    <div class="mb-4">
        <label class="block font-semibold mb-2">
            Email
        </label>

        <input type="email"
               name="email"
               value="{{ old('email') }}"
               class="w-full border rounded p-2"
               required>
    </div>

    <div class="mb-4">
        <label class="block font-semibold mb-2">
            Role
        </label>

        <select name="role"
                class="w-full border rounded p-2">

            <option value="Administrator">Administrator</option>
            <option value="Principal">Principal</option>
            <option value="Teacher">Teacher</option>
            <option value="Clerk">Clerk</option>
            <option value="Accountant">Accountant</option>
            <option value="Student">Student</option>
            <option value="Parent">Parent</option>

        </select>
    </div>

    <div class="mb-4">
        <label class="block font-semibold mb-2">
            Password
        </label>

        <input type="password"
               name="password"
               class="w-full border rounded p-2"
               required>
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

            <a href="{{ route('users.index') }}"
               class="erp-btn erp-btn-cancel">
                Cancel
            </a>

        </div>

    </div>

</form>

</div>

</div>

</x-app-layout>