@if(!auth()->check())
<script>
    window.location.href = "{{ route('login') }}";
</script>
@endif
<!DOCTYPE html>
{{-- <div style="background:red;color:white;padding:20px;">
APP BLADE TEST 123
</div> --}}

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">


<title>{{ config('app.name', 'Laravel') }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

@vite([
    'resources/css/app.css',
    'resources/css/erp-responsive.css',
    'resources/js/app.js'
])



<style>

/* GLOBAL ERP RESPONSIVE */

*{
    box-sizing:border-box;
}

img{
    max-width:100%;
    height:auto;
}

.erp-container{
    width:100%;
    overflow-x:auto;
}

.erp-table{
    width:100%;
    border-collapse:collapse;
}

.erp-table th,
.erp-table td{
    padding:6px;
    border:1px solid #ccc;
}

/* Mobile */

@media (max-width:768px){

    body{
        font-size:14px;
    }

    .max-w-7xl{
        padding-left:8px !important;
        padding-right:8px !important;
    }

    .erp-btn{
        width:100%;
        margin-bottom:5px;
    }

    input,
    select,
    textarea{
        width:100% !important;
        min-width:auto !important;
    }

    .flex{
        flex-direction:column;
        align-items:stretch !important;
    }

    table{
        display:block;
        overflow-x:auto;
        white-space:nowrap;
    }

    .filter-row{
        flex-direction:column;
        align-items:stretch;
    }
}

</style>

</head>

<body class="font-sans antialiased m-0">


{{-- <div style="
    height:98vh;
    background:#FFFDE7;
    border:12px solid #8B4513;
    margin:5px;
    border-radius:15px;
    overflow:auto;
"> --}}

<div class="erp-layout-container">

    <div  style="background:#8B4513; color:white; padding:10px; width:100%;">


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
{{-- <div style="background:yellow;padding:10px;">
SECTIONID = {{ session('sectionid') }}
</div> --}}

@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Validation Error',
    text: '{{ session('error') }}'
});
</script>
@endif

    {{-- @include('layouts.navigation') --}}
    @if(auth()->check())
    @include('layouts.navigation')
@endif
    {{-- <div style="background:red;color:white;padding:10px;">
TEST CONTENT AREA
</div> --}}
@if(session('year_name'))

<div class="bg-blue-100 border-b-2 border-blue-300 px-4 py-2 text-center font-bold text-blue-900">

    Academic Year : {{ session('year_name') }}

    &nbsp;&nbsp;|&nbsp;&nbsp;

    Section : {{ session('section_name') }}

</div>

@endif

    @isset($header)
        <header class="bg-white shadow border-b">
            <div class="max-w-7xl mx-auto py-2 px-4">
                {{ $header }}
            </div>
        </header>
    @endisset

<main class="min-h-screen bg-gradient-to-br from-red-100 via-yellow-100 to-orange-100 p-4">
    {{-- @yield('content') --}}
    @isset($slot)
        {{ $slot }}
    @else
        @yield('content')
    @endisset
</main>


</div>
@include('components.toast')

<script>
// setInterval(function () {

//     fetch('/check-session')
//         .then(response => response.json())
//         .then(data => {

//             if (!data.authenticated) {

//                 alert('Session expired. Please login again.');

//                 window.location.href = '/login';
//             }

//         });

// }, 60000);
// </script>

</body>
</html>
