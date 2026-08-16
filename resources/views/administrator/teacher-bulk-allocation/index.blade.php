<x-app-layout>

<div style="
    width:100%;
    max-width:100%;
    margin:0;
    padding:12px;
    box-sizing:border-box;
">

    <div style="
        width:100%;
        background:white;
        border-radius:10px;
        padding:14px;
        border:1px solid #d1d5db;
        box-shadow:0 3px 8px rgba(0,0,0,.12);
        box-sizing:border-box;
    ">

        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:14px;
        ">

            <h2 style="
                margin:0;
                color:#1d4ed8;
                font-size:21px;
                font-weight:bold;
            ">
                Teacher Bulk Allocation
            </h2>

        </div>


        {{-- =====================================================
             SUCCESS MESSAGE
        ====================================================== --}}

        @if(session('success'))

            <div style="
                background:#dcfce7;
                color:#166534;
                border:1px solid #86efac;
                padding:9px 12px;
                border-radius:6px;
                margin-bottom:12px;
                font-weight:bold;
                font-size:13px;
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
                padding:9px 12px;
                border-radius:6px;
                margin-bottom:12px;
                font-weight:bold;
                font-size:13px;
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
                padding:9px 12px;
                border-radius:6px;
                margin-bottom:12px;
                font-size:13px;
            ">

                <strong>
                    Please correct the following:
                </strong>

                <ul style="
                    margin:5px 0 0 18px;
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
             FILTER
        ====================================================== --}}

        <div style="
            width:100%;
            background:#ffffff;
            border:1px solid #d1d5db;
            border-radius:7px;
            padding:10px;
            margin-bottom:12px;
            box-sizing:border-box;
        ">

            <form
                method="GET"
                action="{{ route('teacher-bulk-allocation.index') }}"
            >

                <div style="
                    display:flex;
                    align-items:flex-end;
                    gap:8px;
                    width:100%;
                ">

                    {{-- STANDARD --}}

                    <div style="
                        flex:0 0 180px;
                        max-width:180px;
                    ">

                        <label
                            for="standard_id"
                            style="
                                display:block;
                                font-weight:600;
                                margin-bottom:4px;
                                font-size:13px;
                            "
                        >
                            Standard
                        </label>

                        <select
                            name="standard_id"
                            id="standard_id"
                            style="
                                width:100%;
                                height:35px;
                                border:1px solid #d1d5db;
                                border-radius:5px;
                                padding:4px 8px;
                                background:#fff;
                                font-size:13px;
                                box-sizing:border-box;
                            "
                        >

                            <option value="">
                                All Standards
                            </option>

                            @foreach($standards as $standard)

                                <option
                                    value="{{ $standard->id }}"
                                    {{ (string)$standardId === (string)$standard->id ? 'selected' : '' }}
                                >
                                    {{ $standard->standard_name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- SEARCH --}}

                    <button
                        type="submit"
                        style="
                            width:70px;
                            height:35px;
                            background:#16a34a;
                            color:white;
                            border:none;
                            padding:0;
                            border-radius:5px;
                            cursor:pointer;
                            font-weight:600;
                            font-size:13px;
                            box-sizing:border-box;
                        "
                    >
                        Search
                    </button>


                    {{-- RESET --}}

                    <a
                        href="{{ route('teacher-bulk-allocation.index') }}"
                        style="
                            width:70px;
                            height:35px;
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                            background:#6b7280;
                            color:white;
                            padding:0;
                            border-radius:5px;
                            text-decoration:none;
                            font-weight:600;
                            font-size:13px;
                            box-sizing:border-box;
                        "
                    >
                        Reset
                    </a>


                    {{-- NEW ALLOCATION --}}

                    <a
                        href="{{ route('teacher-bulk-allocation.create') }}"
                        style="
                            width:auto;
                            min-width:145px;
                            height:35px;
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                            background:#16a34a;
                            color:white;
                            padding:0 14px;
                            border-radius:5px;
                            text-decoration:none;
                            font-weight:bold;
                            font-size:13px;
                            margin-left:auto;
                            box-sizing:border-box;
                        "
                    >
                        + New Allocation
                    </a>

                </div>

            </form>

        </div>


        {{-- =====================================================
             TABLE
        ====================================================== --}}

        <div style="
            width:100%;
            overflow:hidden;
        ">

            <table style="
                width:100%;
                table-layout:fixed;
                border-collapse:collapse;
                margin:0;
                font-size:13px;
            ">

                <colgroup>

                    <col style="width:3%;">

                    <col style="width:10%;">

                    <col style="width:9%;">

                    <col style="width:8%;">

                    <col style="width:9%;">

                    <col style="width:5%;">

                    <col style="width:13%;">

                    <col style="width:29%;">

                    <col style="width:14%;">

                </colgroup>


                {{-- =================================================
                     TABLE HEADER
                ================================================== --}}

                <thead>

                    <tr style="
                        background:#1e3a8a;
                        color:white;
                    ">

                        <th style="
                            padding:8px 4px;
                            border:1px solid #d1d5db;
                            text-align:center;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            #
                        </th>

                        <th style="
                            padding:8px 5px;
                            border:1px solid #d1d5db;
                            text-align:left;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            Teacher
                        </th>

                        <th style="
                            padding:8px 5px;
                            border:1px solid #d1d5db;
                            text-align:left;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            Academic Year
                        </th>

                        <th style="
                            padding:8px 5px;
                            border:1px solid #d1d5db;
                            text-align:left;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            Section
                        </th>

                        <th style="
                            padding:8px 5px;
                            border:1px solid #d1d5db;
                            text-align:left;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            Standard
                        </th>

                        <th style="
                            padding:8px 4px;
                            border:1px solid #d1d5db;
                            text-align:center;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            Div.
                        </th>

                        <th style="
                            padding:8px 5px;
                            border:1px solid #d1d5db;
                            text-align:left;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            Exam
                        </th>

                        <th style="
                            padding:8px 5px;
                            border:1px solid #d1d5db;
                            text-align:left;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            Subjects
                        </th>

                        <th style="
                            padding:8px 4px;
                            border:1px solid #d1d5db;
                            text-align:center;
                            font-size:13px;
                            font-weight:bold;
                        ">
                            Action
                        </th>

                    </tr>

                </thead>


                {{-- =================================================
                     TABLE BODY
                ================================================== --}}

                <tbody>

                @forelse($allocations as $allocation)

                    @php

                        $examNames =
                            $allocation
                                ->subjectAllocations
                                ->map(function ($item) {
                                    return optional($item->exam)->exam_name;
                                })
                                ->filter()
                                ->unique()
                                ->values();

                    @endphp


                    <tr style="
                        background:{{ $loop->even ? '#f9fafb' : '#ffffff' }};
                    ">


                        {{-- NUMBER --}}

                        <td style="
                            padding:7px 4px;
                            border:1px solid #d1d5db;
                            text-align:center;
                            vertical-align:middle;
                            font-size:13px;
                        ">
                            {{ $allocations->firstItem() + $loop->index }}
                        </td>


                        {{-- TEACHER --}}

                        <td style="
                            padding:7px 5px;
                            border:1px solid #d1d5db;
                            font-weight:bold;
                            vertical-align:middle;
                            overflow-wrap:anywhere;
                            word-break:break-word;
                            font-size:13px;
                        ">
                            {{ optional($allocation->teacher)->name ?? '-' }}
                        </td>


                        {{-- ACADEMIC YEAR --}}

                        <td style="
                            padding:7px 5px;
                            border:1px solid #d1d5db;
                            vertical-align:middle;
                            overflow-wrap:anywhere;
                            font-size:13px;
                        ">
                            {{ optional($allocation->academicYear)->year_name ?? '-' }}
                        </td>


                        {{-- SECTION --}}

                        <td style="
                            padding:7px 5px;
                            border:1px solid #d1d5db;
                            vertical-align:middle;
                            overflow-wrap:anywhere;
                            word-break:break-word;
                            font-size:13px;
                        ">
                            {{ optional($allocation->section)->section_name ?? '-' }}
                        </td>


                        {{-- STANDARD --}}

                        <td style="
                            padding:7px 5px;
                            border:1px solid #d1d5db;
                            font-weight:600;
                            vertical-align:middle;
                            overflow-wrap:anywhere;
                            word-break:break-word;
                            font-size:13px;
                        ">
                            {{ optional($allocation->standard)->standard_name ?? '-' }}
                        </td>


                        {{-- DIVISION --}}

                        <td style="
                            padding:7px 4px;
                            border:1px solid #d1d5db;
                            text-align:center;
                            vertical-align:middle;
                            font-size:13px;
                        ">
                            {{ optional($allocation->division)->division_name ?? '-' }}
                        </td>


                        {{-- EXAM --}}

                        <td style="
                            padding:7px 5px;
                            border:1px solid #d1d5db;
                            vertical-align:middle;
                            overflow-wrap:anywhere;
                            word-break:break-word;
                            line-height:1.3;
                            font-size:13px;
                        ">

                            @if($examNames->isNotEmpty())

                                @foreach($examNames as $examName)

                                    <div style="
                                        margin:0 0 2px 0;
                                        font-weight:500;
                                    ">
                                        {{ $examName }}
                                    </div>

                                @endforeach

                            @else

                                <span style="
                                    color:#9ca3af;
                                ">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- SUBJECTS --}}

                        <td style="
                            padding:6px 5px;
                            border:1px solid #d1d5db;
                            vertical-align:middle;
                            overflow:hidden;
                        ">

                            @if(
                                isset($allocation->displaySubjects)
                                &&
                                $allocation->displaySubjects->isNotEmpty()
                            )

                                <div style="
                                    display:flex;
                                    flex-wrap:wrap;
                                    align-items:center;
                                    gap:4px;
                                    width:100%;
                                ">

                                    @foreach(
                                        $allocation->displaySubjects
                                        as $subject
                                    )

                                        <span style="
                                            display:inline-flex;
                                            align-items:center;
                                            background:#dbeafe;
                                            color:#1e40af;
                                            border:1px solid #93c5fd;
                                            border-radius:4px;
                                            padding:3px 6px;
                                            font-size:12px;
                                            font-weight:bold;
                                            line-height:1.2;
                                            overflow-wrap:anywhere;
                                            word-break:break-word;
                                            box-sizing:border-box;
                                        ">
                                            {{ $subject->subject_name }}
                                        </span>

                                    @endforeach

                                </div>

                            @else

                                <span style="
                                    color:#9ca3af;
                                    font-size:13px;
                                ">
                                    No subjects
                                </span>

                            @endif

                        </td>


                        {{-- ACTION --}}

                        <td style="
                            padding:6px 3px;
                            border:1px solid #d1d5db;
                            text-align:center;
                            vertical-align:middle;
                        ">

                            <div style="
                                display:flex;
                                justify-content:center;
                                align-items:center;
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
                                        width:64px;
                                        height:30px;
                                        display:inline-flex;
                                        align-items:center;
                                        justify-content:center;
                                        box-sizing:border-box;
                                        background:#2563eb;
                                        color:white;
                                        padding:0;
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
                                        display:inline-block;
                                        margin:0;
                                        padding:0;
                                    "
                                >

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        style="
                                            width:64px;
                                            height:30px;
                                            display:inline-flex;
                                            align-items:center;
                                            justify-content:center;
                                            box-sizing:border-box;
                                            background:#dc2626;
                                            color:white;
                                            border:none;
                                            padding:0;
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
                                padding:25px 10px;
                                text-align:center;
                                color:#6b7280;
                                border:1px solid #d1d5db;
                                font-size:13px;
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
                                        display:inline-flex;
                                        align-items:center;
                                        justify-content:center;
                                        height:32px;
                                        background:#16a34a;
                                        color:white;
                                        padding:0 14px;
                                        border-radius:5px;
                                        text-decoration:none;
                                        font-weight:bold;
                                        font-size:13px;
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
                margin-top:14px;
                display:flex;
                justify-content:center;
                width:100%;
            ">
                {{ $allocations->onEachSide(3)->links() }}
            </div>

        @endif

    </div>

</div>

</x-app-layout>