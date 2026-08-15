<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100">
        <div style="height:100vh; background:#FFFDE7; border:12px solid #8B4513; margin:5px; border-radius:15px; display:flex; flex-direction:column; align-items:center; overflow-y:auto;">


<div class="w-full mb-4">


<div style="background:#8B4513; color:white; padding:10px; width:100%;">

    <div class="flex items-center justify-center gap-4">

        <img src="{{ asset('images/school-logo.png') }}"
             alt="School Logo"
             style="height:70px; width:auto;">

        <div class="text-center">

            <h1 class="text-3xl font-extrabold">
                PRAJNANABODHINI ENGLISH MEDIUM SCHOOL & JR. COLLEGE
            </h1>

            <p class="text-xl font-bold">
                SHIRGAON / CHIKHALI
            </p>

        </div>

    </div>

</div>

</div>


            <div class="w-full sm:max-w-md mt-2 px-8 py-6 shadow-2xl overflow-hidden rounded-xl border-4 border-amber-400"
     style="background:#FFCC80;">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>