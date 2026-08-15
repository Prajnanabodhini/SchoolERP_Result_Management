<x-app-layout>

<div style="
    max-width:1400px;
    margin:auto;
    padding:15px;
">

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div style="
        background:white;
        border-radius:10px;
        padding:18px;
        border:1px solid #d1d5db;
        box-shadow:0 3px 8px rgba(0,0,0,.12);
    ">

        <div style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
            margin-bottom:18px;
        ">

            <h2 style="
                margin:0;
                color:#1d4ed8;
                font-size:22px;
                font-weight:bold;
            ">
                Teacher Bulk Allocation
            </h2>


            <a
                href="{{ route('teacher-bulk-allocation.create') }}"
                style="
                    background:#16a34a;
                    color:white;
                    padding:9px 18px;
                    border-radius:5px;
                    text-decoration:none;
                    font-weight:bold;
                "
            >
                + New Allocation
            </a>

        </div>


        {{-- =====================================================
             SUCCESS MESSAGE
        ====================================================== --}}

        @if(session('success'))

            <div style="
                background:#dcfce7;
                color:#166534;
                border:1px solid #86efac;
                padding:10px 14px;
                border-radius:6px;
                margin-bottom:15px;
                font-weight:bold;
            ">
                {{ session('success') }}
            </div>

        @endif


        {{-- =====================================================
             ERROR MESSAGE
        ====================================================== --}}

        @if(session('error'))

            <div style="
                background:#fee2e2;
                color:#991b1b;
                border:1px solid #fca5a5;
                padding:10px 14px;
                border-radius:6px;
                margin-bottom:15px;
                font-weight:bold;
            ">
                {{ session('error') }}
            </div>

        @endif


        {{-- =====================================================
             VALIDATION ERRORS
        ====================================================== --}}

        @if($errors->any())

            <div style="
                background:#fee2e2;
                color:#991b1b;
                border:1px solid #fca5a5;
                padding:10px 14px;
                border-radius:6px;
                margin-bottom:15px;
            ">

                <strong>
                    Please correct the following:
                </strong>

                <ul style="
                    margin:6px 0 0 20px;
                ">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- =====================================================
             TABLE
        ====================================================== --}}

        <div style="
            overflow-x:auto;
        ">

            <table style="
                width:100%;
                border-collapse:collapse;
                min-width:1100px;
            ">

                <thead>

                    <tr style="
                        background:#1e3a8a;
                        color:white;
                    ">

                        <th style="
                            padding:10px;
                            border:1px solid #d1d5db;
                            width:50px;
                        ">
                            #
                        </th>


                        <th style="
                            padding:10px;
                            border:1px solid #d1d5db;
                            text-align:left;
                        ">
                            Teacher
                        </th>


                        <th style="
                            padding:10px;
                            border:1px solid #d1d5db;
                            text-align:left;
                        ">
                            Academic Year
                        </th>


                        <th style="
                            padding:10px;
                            border:1px solid #d1d5db;
                            text-align:left;
                        ">
                            Section
                        </th>


                        <th style="
                            padding:10px;
                            border:1px solid #d1d5db;
                            text-align:left;
                        ">
                            Standard
                        </th>


                        <th style="
                            padding:10px;
                            border:1px solid #d1d5db;
                            text-align:left;
                        ">
                            Division
                        </th>


                        <th style="
                            padding:10px;
                            border:1px solid #d1d5db;
                            text-align:left;
                        ">
                            Exam
                        </th>


                        <th style="
                            padding:10px;
                            border:1px solid #d1d5db;
                            text-align:left;
                        ">
                            Subjects
                        </th>


                        <th style="
                            padding:10px;
                            border:1px solid #d1d5db;
                            text-align:center;
                            width:150px;
                        ">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($allocations as $allocation)

                        <tr style="
                            background:
                                {{ $loop->even ? '#f9fafb' : 'white' }};
                        ">

                            {{-- =================================================
                                 NUMBER
                            ================================================== --}}

                            <td style="
                                padding:9px;
                                border:1px solid #d1d5db;
                                text-align:center;
                            ">
                                {{ $allocations->firstItem() + $loop->index }}
                            </td>


                            {{-- =================================================
                                 TEACHER
                            ================================================== --}}

                            <td style="
                                padding:9px;
                                border:1px solid #d1d5db;
                                font-weight:bold;
                            ">

                                {{ $allocation->teacher->name ?? '-' }}

                            </td>


                            {{-- =================================================
                                 ACADEMIC YEAR
                            ================================================== --}}

                            <td style="
                                padding:9px;
                                border:1px solid #d1d5db;
                            ">

                                {{ $allocation->academicYear->year_name ?? '-' }}

                            </td>


                            {{-- =================================================
                                 SECTION
                            ================================================== --}}

                            <td style="
                                padding:9px;
                                border:1px solid #d1d5db;
                            ">

                                {{ $allocation->section->section_name ?? '-' }}

                            </td>


                            {{-- =================================================
                                 STANDARD
                            ================================================== --}}

                            <td style="
                                padding:9px;
                                border:1px solid #d1d5db;
                            ">

                                {{ $allocation->standard->standard_name ?? '-' }}

                            </td>


                            {{-- =================================================
                                 DIVISION
                            ================================================== --}}

                            <td style="
                                padding:9px;
                                border:1px solid #d1d5db;
                            ">

                                {{ $allocation->division->division_name ?? '-' }}

                            </td>


                            {{-- =================================================
                                 EXAM
                            ================================================== --}}

                            <td style="
                                padding:9px;
                                border:1px solid #d1d5db;
                            ">

                                @php

                                    $examNames =
                                        $allocation
                                            ->subjectAllocations
                                            ->map(function($item) {

                                                return
                                                    $item->exam->exam_name
                                                    ?? null;

                                            })
                                            ->filter()
                                            ->unique()
                                            ->values();

                                @endphp


                                @if($examNames->isNotEmpty())

                                    @foreach($examNames as $examName)

                                        <div style="
                                            margin-bottom:3px;
                                        ">
                                            {{ $examName }}
                                        </div>

                                    @endforeach

                                @else

                                    -

                                @endif

                            </td>


                            {{-- =================================================
                                 SUBJECTS
                            ================================================== --}}

                            <td style="
                                padding:9px;
                                border:1px solid #d1d5db;
                            ">

                                @if(
                                    $allocation
                                        ->subjectAllocations
                                        ->isNotEmpty()
                                )

                                    @foreach(
                                        $allocation->subjectAllocations
                                        as $subjectAllocation
                                    )

                                        @if($subjectAllocation->subject)

                                            <span style="
                                                display:inline-block;
                                                background:#dbeafe;
                                                color:#1e40af;
                                                border:1px solid #93c5fd;
                                                border-radius:4px;
                                                padding:3px 7px;
                                                margin:2px;
                                                font-size:12px;
                                                font-weight:bold;
                                            ">

                                                {{ $subjectAllocation->subject->subject_name }}

                                            </span>

                                        @endif

                                    @endforeach

                                @else

                                    <span style="
                                        color:#9ca3af;
                                    ">
                                        No subjects
                                    </span>

                                @endif

                            </td>


                            {{-- =================================================
                                 ACTION
                            ================================================== --}}

                            <td style="
                                padding:9px;
                                border:1px solid #d1d5db;
                                text-align:center;
                            ">

                                <div style="
                                    display:flex;
                                    justify-content:center;
                                    gap:5px;
                                    flex-wrap:wrap;
                                ">


                                    {{-- EDIT --}}

                                    <a
                                        href="{{ route(
                                            'teacher-bulk-allocation.edit',
                                            $allocation->id
                                        ) }}"
                                        style="
                                            background:#2563eb;
                                            color:white;
                                            padding:5px 10px;
                                            border-radius:4px;
                                            text-decoration:none;
                                            font-size:12px;
                                            font-weight:bold;
                                        "
                                    >
                                        Edit
                                    </a>


                                    {{-- DELETE --}}

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'teacher-bulk-allocation.destroy',
                                            $allocation->id
                                        ) }}"
                                        onsubmit="
                                            return confirm(
                                                'Are you sure you want to delete this allocation?'
                                            );
                                        "
                                        style="
                                            display:inline;
                                        "
                                    >

                                        @csrf

                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            style="
                                                background:#dc2626;
                                                color:white;
                                                border:none;
                                                padding:5px 10px;
                                                border-radius:4px;
                                                cursor:pointer;
                                                font-size:12px;
                                                font-weight:bold;
                                            "
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="9"
                                style="
                                    padding:25px;
                                    text-align:center;
                                    color:#6b7280;
                                    border:1px solid #d1d5db;
                                "
                            >

                                No teacher allocations found.

                                <div style="
                                    margin-top:10px;
                                ">

                                    <a
                                        href="{{ route(
                                            'teacher-bulk-allocation.create'
                                        ) }}"
                                        style="
                                            display:inline-block;
                                            background:#16a34a;
                                            color:white;
                                            padding:8px 15px;
                                            border-radius:5px;
                                            text-decoration:none;
                                            font-weight:bold;
                                        "
                                    >
                                        Create First Allocation
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =====================================================
             PAGINATION
        ====================================================== --}}

        @if($allocations->hasPages())

            <div style="
                margin-top:18px;
                display:flex;
                justify-content:center;
            ">

                {{ $allocations->links() }}

            </div>

        @endif

    </div>

</div>

</x-app-layout>