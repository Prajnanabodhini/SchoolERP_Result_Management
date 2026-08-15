<x-app-layout>

<div class="p-3">

    {{-- ============================================================
         PAGE TITLE
    ============================================================ --}}
    <div class="mb-2">

        <h2 style="
            font-size:22px;
            font-weight:600;
            color:#92400E;
            margin:0;">

            Exam Progress Dashboard

        </h2>

    </div>


    {{-- ============================================================
         FILTER SECTION
    ============================================================ --}}
    <div class="bg-white border rounded shadow p-3">

        <form method="GET">

            <div style="
                display:flex;
                align-items:center;
                gap:10px;
                flex-wrap:wrap;
                font-size:14px;">


                {{-- =================================================
                     EXAM
                ================================================== --}}
                <label style="font-weight:600;">
                    Exam
                </label>

                <select
                    name="exam_master_id"
                    class="border rounded"
                    style="
                        width:180px;
                        height:30px;
                        font-size:14px;
                        padding:2px 6px;">

                    <option value="">
                        All Exams
                    </option>

                    @foreach($exams as $exam)

                        <option
                            value="{{ $exam->id }}"
                            {{ $examId == $exam->id ? 'selected' : '' }}>

                            {{ $exam->exam_name }}

                        </option>

                    @endforeach

                </select>


                {{-- =================================================
                     STANDARD
                ================================================== --}}
                <label style="font-weight:600;">
                    Standard
                </label>

                <select
                    name="standard_id"
                    class="border rounded"
                    style="
                        width:140px;
                        height:30px;
                        font-size:14px;
                        padding:2px 6px;">

                    <option value="">
                        All Standards
                    </option>

                    @foreach($standards as $standard)

                        <option
                            value="{{ $standard->id }}"
                            {{ $standardId == $standard->id ? 'selected' : '' }}>

                            {{ $standard->standard_name }}

                        </option>

                    @endforeach

                </select>


                {{-- =================================================
                     DIVISION
                ================================================== --}}
                <label style="font-weight:600;">
                    Division
                </label>

                <select
                    name="division_id"
                    class="border rounded"
                    style="
                        width:140px;
                        height:30px;
                        font-size:14px;
                        padding:2px 6px;">

                    <option value="">
                        All Divisions
                    </option>

                    @foreach($divisions as $division)

                        <option
                            value="{{ $division->id }}"
                            {{ $divisionId == $division->id ? 'selected' : '' }}>

                            {{ $division->division_name }}

                        </option>

                    @endforeach

                </select>


                {{-- =================================================
                     SEARCH BUTTON
                ================================================== --}}
                <button
                    type="submit"
                    class="erp-btn erp-btn-save"
                    style="
                        height:30px;
                        padding:0 12px;
                        font-size:14px;">

                    Search

                </button>



            </div>

        </form>
<div style="
    margin-top:12px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
">

    <span style="
        background:#DCFCE7;
        color:#166534;
        padding:6px 12px;
        border-radius:4px;
        font-size:14px;
        font-weight:600;">
        COMPLETED : {{ $completed }}
    </span>

    <span style="
        background:#FCE7F3;
        color:#9D174D;
        padding:6px 12px;
        border-radius:4px;
        font-size:14px;
        font-weight:600;">
        PENDING : {{ $pending }}
    </span>

    <span style="
        background:#DBEAFE;
        color:#1E40AF;
        padding:6px 12px;
        border-radius:4px;
        font-size:14px;
        font-weight:600;">
        NOT ALLOCATED : {{ $notAllocated }}
    </span>

</div>

    {{-- ============================================================
         RESULT TABLE
    ============================================================ --}}
    <div class="bg-white border rounded shadow p-3 mt-4">

        <div style="
            overflow-x:auto;
            width:100%;">

            <table
                class="w-full border border-gray-300"
                style="
                    border-collapse:collapse;
                    width:100%;">

                <thead class="bg-blue-100">

                    <tr style="font-size:14px;">

                        <th
                            class="border p-2"
                            style="width:20%;">

                            Exam

                        </th>

                        <th
                            class="border p-2"
                            style="width:13%;">

                            Standard

                        </th>

                        <th
                            class="border p-2"
                            style="width:10%;">

                            Division

                        </th>

                        <th
                            class="border p-2"
                            style="width:22%;">

                            Subject

                        </th>

                        <th
                            class="border p-2"
                            style="width:20%;">

                            Teacher

                        </th>

                        <th
                            class="border p-2"
                            style="width:15%;">

                            Status

                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($statuses as $status)

                        <tr
                            style="font-size:14px;"
                            class="hover:bg-yellow-50">


                            {{-- =================================================
                                 EXAM
                            ================================================== --}}
                            <td class="border p-2">

                                {{ $status->exam?->exam_name ?? '-' }}

                            </td>


                            {{-- =================================================
                                 STANDARD
                            ================================================== --}}
                            <td class="border p-2">

                                {{ $status->standard?->standard_name ?? '-' }}

                            </td>


                            {{-- =================================================
                                 DIVISION
                            ================================================== --}}
                            <td
                                class="border p-2 text-center">

                                {{ $status->division?->division_name ?? '-' }}

                            </td>


                            {{-- =================================================
                                 SUBJECT

                                 IMPORTANT:
                                 teacher_marks_statuses.subject_id refers to
                                 standard_wise_subjects.id

                                 Therefore use:
                                 standardWiseSubject->subject_name
                            ================================================== --}}
                            <td class="border p-2">

    {{ $status->subject?->subject_name ?? '-' }}

</td>


                            {{-- =================================================
                                 TEACHER
                            ================================================== --}}
                            <td class="border p-2">

                                {{ $status->teacher?->name ?? '-' }}

                            </td>


                            {{-- =================================================
                                 STATUS
                            ================================================== --}}
                            <td class="border p-2 text-center">

                                @php

                                    $currentStatus =
                                        strtoupper(
                                            trim(
                                                $status->status ?? ''
                                            )
                                        );

                                @endphp


                                {{-- COMPLETED --}}
                                @if($currentStatus === 'COMPLETED')

                                    <span style="
                                        background:#DCFCE7;
                                        color:#166534;
                                        padding:4px 10px;
                                        border-radius:4px;
                                        font-size:14px;
                                        font-weight:600;
                                        display:inline-block;">

                                        COMPLETED

                                    </span>


                                {{-- PENDING --}}
                                @elseif($currentStatus === 'PENDING')

                                    <span style="
                                        background:#FCE7F3;
                                        color:#9D174D;
                                        padding:4px 10px;
                                        border-radius:4px;
                                        font-size:14px;
                                        font-weight:600;
                                        display:inline-block;">

                                        PENDING

                                    </span>


                                {{-- NOT ALLOCATED --}}
                                @elseif($currentStatus === 'NOT_ALLOCATED')

                                    <span style="
                                        background:#DBEAFE;
                                        color:#1E40AF;
                                        padding:4px 10px;
                                        border-radius:4px;
                                        font-size:14px;
                                        font-weight:600;
                                        display:inline-block;">

                                        NOT ALLOCATED

                                    </span>


                                {{-- OTHER STATUS --}}
                                @else

                                    <span style="
                                        background:#F3F4F6;
                                        color:#374151;
                                        padding:4px 10px;
                                        border-radius:4px;
                                        font-size:14px;
                                        font-weight:600;
                                        display:inline-block;">

                                        {{ $status->status ?? '-' }}

                                    </span>

                                @endif

                            </td>

                        </tr>


                    @empty

                        {{-- =================================================
                             NO RECORDS
                        ================================================== --}}
                        <tr>

                            <td
                                colspan="6"
                                class="border p-4 text-center"
                                style="font-size:14px;">

                                No records found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ============================================================
             PAGINATION
        ============================================================ --}}
        <div class="mt-4 flex justify-center">

            {{ $statuses->onEachSide(5)->links() }}

        </div>

    </div>

</div>

</x-app-layout>

