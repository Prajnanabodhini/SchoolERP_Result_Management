<x-guest-layout>


<form method="POST" action="{{ route('register') }}">

    @csrf

    <div class="text-center mb-2">
        <h2 class="text-2xl font-bold text-red-600">
            User Registration
        </h2>
    </div>

    <!-- Name -->
    <div>
        <x-input-label for="name" :value="__('Name')" />

        <x-text-input id="name"
                      class="block mt-1 w-full text-sm py-1 px-2"
                      type="text"
                      name="name"
                      :value="old('name')"
                      required
                      autofocus
                      autocomplete="name" />

        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <!-- Role -->
    <div class="mt-2">

        <x-input-label for="role" :value="__('Role')" />

        <select id="role"
                name="role"
                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm p-2 text-sm">

            <option value="Administrator">Administrator</option>
            <option value="Principal">Principal</option>
            <option value="Teacher" selected>Teacher</option>
            <option value="Clerk">Clerk</option>
            <option value="Accountant">Accountant</option>
            <option value="Student">Student</option>
            <option value="Parent">Parent</option>

        </select>

    </div>

    <!-- Email -->
    <div class="mt-2">

        <x-input-label for="email" :value="__('Email')" />

        <x-text-input id="email"
                      class="block mt-1 w-full text-sm py-1 px-2"
                      type="email"
                      name="email"
                      :value="old('email')"
                      required
                      autocomplete="username" />

        <x-input-error :messages="$errors->get('email')" class="mt-1" />

    </div>

    <!-- Password -->
    <div class="mt-2">

        <x-input-label for="password" :value="__('Password')" />

        <x-text-input id="password"
                      class="block mt-1 w-full text-sm py-1 px-2"
                      type="password"
                      name="password"
                      required
                      autocomplete="new-password" />

        <x-input-error :messages="$errors->get('password')" class="mt-1" />

    </div>

    <!-- Confirm Password -->
    <div class="mt-2">

        <x-input-label for="password_confirmation"
                       :value="__('Confirm Password')" />

        <x-text-input id="password_confirmation"
                      class="block mt-1 w-full text-sm py-1 px-2"
                      type="password"
                      name="password_confirmation"
                      required
                      autocomplete="new-password" />

        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />

    </div>

    <!-- Buttons -->
    <div class="flex items-center justify-between mt-6">

        <a class="underline text-sm text-gray-600 hover:text-gray-900"
           href="{{ route('login') }}">
            Already registered?
        </a>

        <div class="flex gap-2">

            <a href="{{ route('login') }}"
               class="erp-btn erp-btn-cancel">
                Cancel
            </a>

            <button type="submit"
                    class="erp-btn erp-btn-save">
                Register
            </button>

        </div>

    </div>

</form>


</x-guest-layout>
