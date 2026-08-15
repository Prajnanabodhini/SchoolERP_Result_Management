<x-guest-layout>


<!-- Session Status -->
<x-auth-session-status class="mb-4" :status="session('status')" />

<div class="text-center mb-6">
    <h2 class="text-3xl font-bold text-red-500">
        Login
    </h2>
</div>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <!-- Email Address -->
    <div>
    <x-input-label for="User Name" :value="__('Username')" />

    <x-text-input
        id="name"
        class="block mt-1 w-full"
        type="text"
        name="name"
        :value="old('name')"
        required
        autofocus
        autocomplete="username"
    />

    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

    <!-- Password -->
    <div class="mt-4">

        <x-input-label for="password"
                       :value="__('Password')" />

        <x-text-input id="password"
                      class="block mt-1 w-full"
                      type="password"
                      name="password"
                      required
                      autocomplete="current-password" />

        <x-input-error :messages="$errors->get('password')"
                       class="mt-2" />

    </div>

    <!-- Remember Me -->
    <div class="block mt-4">

        <label for="remember_me"
               class="inline-flex items-center">

            <input id="remember_me"
                   type="checkbox"
                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                   name="remember">

            <span class="ms-2 text-sm text-gray-600">
                {{ __('Remember me') }}
            </span>

        </label>

    </div>

    <div class="flex items-center justify-end gap-2 mt-5">

        @if (Route::has('password.request'))
            <a class="underline text-sm text-gray-600 hover:text-gray-900"
               href="{{ route('password.request') }}">
                Forgot Password?
            </a>
        @endif

        
        <button type="submit"
     class="erp-btn erp-btn-save">
Login </button>


    </div>

</form>

@if($errors->any())

<div id="errorPopup" style="
position:fixed;
top:20px;
right:20px;
background:#dc2626;
color:white;
padding:15px 20px;
border-radius:5px;
font-weight:bold;
z-index:9999;
box-shadow:0 0 10px rgba(0,0,0,0.3);
">
    {{ $errors->first() }}
</div>

<script>
setTimeout(function () {
    let popup = document.getElementById('errorPopup');
    if (popup) {
        popup.style.display = 'none';
    }
}, 3000);
</script>

@endif
</x-guest-layout>
