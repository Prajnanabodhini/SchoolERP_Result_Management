@if(!auth()->check())
<script>
    window.location.href = "{{ route('login') }}";
</script>
@endif

<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'Laravel') }}
    </title>

    <link rel="preconnect"
          href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
        rel="stylesheet"
    />

    @vite([
        'resources/css/app.css',
        'resources/css/erp-responsive.css',
        'resources/js/app.js'
    ])


    <style>

        /*
        |--------------------------------------------------------------------------
        | GLOBAL ERP RESPONSIVE
        |--------------------------------------------------------------------------
        */

        * {
            box-sizing: border-box;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        .erp-container {
            width: 100%;
            overflow-x: auto;
        }

        .erp-table {
            width: 100%;
            border-collapse: collapse;
        }

        .erp-table th,
        .erp-table td {
            padding: 6px;
            border: 1px solid #ccc;
        }


        /*
        |--------------------------------------------------------------------------
        | SCHOOL HEADER
        |--------------------------------------------------------------------------
        */

        .school-header {
            background: #8B4513;
            color: white;
            padding: 10px;
            width: 100%;
        }

        .school-header-inner {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .school-logo {
            height: 70px;
            width: auto;
            flex-shrink: 0;
        }

        .school-name {
            text-align: center;
            line-height: 1.2;
        }

        .school-name h1 {
            margin: 0;
            font-size: 30px;
            font-weight: 800;
        }

        .school-name p {
            margin: 4px 0 0 0;
            font-size: 20px;
            font-weight: 700;
        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 768px) {

            body {
                font-size: 14px;
            }

            .max-w-7xl {
                padding-left: 8px !important;
                padding-right: 8px !important;
            }

            .erp-btn {
                width: 100%;
                margin-bottom: 5px;
            }

            input,
            select,
            textarea {
                width: 100% !important;
                min-width: auto !important;
            }

            .flex {
                flex-direction: column;
                align-items: stretch !important;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }


            /*
            |--------------------------------------------------------------------------
            | MOBILE SCHOOL HEADER
            |--------------------------------------------------------------------------
            */

            .school-header {
                padding: 8px;
            }

            .school-header-inner {
                gap: 8px;
            }

            .school-logo {
                height: 48px;
            }

            .school-name h1 {
                font-size: 15px;
            }

            .school-name p {
                font-size: 13px;
            }
        }

    </style>

</head>


<body class="font-sans antialiased m-0">


<div class="erp-layout-container">


    <!-- ==============================================================
         SCHOOL HEADER
         ============================================================== -->

    @php
        $schoolCode = session('school_code', 'shirgaon');
    @endphp


    <div class="school-header">

        <div class="school-header-inner">

            <img
                src="{{ asset('images/school-logo.png') }}"
                alt="School Logo"
                class="school-logo"
            >


            <div class="school-name">


                @if($schoolCode === 'chikhali')

                    <h1>
                        PRAJNANABODHINI ENGLISH MEDIUM SCHOOL CHIKHALI
                    </h1>

                @else

                    <h1>
                        PRAJNANABODHINI ENGLISH MEDIUM SCHOOL & JR. COLLEGE
                    </h1>

                    <p>
                        SHIRGAON
                    </p>

                @endif


            </div>

        </div>

    </div>


    <!-- ==============================================================
         ERROR MESSAGE
         ============================================================== -->

    @if(session('error'))

        <script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: '{{ session('error') }}'
            });
        </script>

    @endif


    <!-- ==============================================================
         NAVIGATION
         ============================================================== -->

    @if(auth()->check())

        @include('layouts.navigation')

    @endif


    <!-- ==============================================================
         ACADEMIC YEAR / SECTION
         ============================================================== -->

    @if(session('year_name'))

        <div
            class="bg-blue-100 border-b-2 border-blue-300 px-4 py-2
                   text-center font-bold text-blue-900"
        >

            Academic Year :
            {{ session('year_name') }}

            &nbsp;&nbsp;|&nbsp;&nbsp;

            Section :
            {{ session('section_name') }}

        </div>

    @endif


    <!-- ==============================================================
         OPTIONAL HEADER SLOT
         ============================================================== -->

    @isset($header)

        <header class="bg-white shadow border-b">

            <div class="max-w-7xl mx-auto py-2 px-4">

                {{ $header }}

            </div>

        </header>

    @endisset


    <!-- ==============================================================
         MAIN CONTENT
         ============================================================== -->

    <main
        class="min-h-screen bg-gradient-to-br
               from-red-100 via-yellow-100 to-orange-100 p-4"
    >

        @isset($slot)

            {{ $slot }}

        @else

            @yield('content')

        @endisset

    </main>


</div>


@include('components.toast')


<script>
    /*
    |--------------------------------------------------------------------------
    | SESSION CHECK
    |--------------------------------------------------------------------------
    |
    | Keep disabled unless required.
    |
    */

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
</script>


</body>

</html>