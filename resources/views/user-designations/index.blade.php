<x-app-layout>

<style>

    .user-designation-page,
    .user-designation-page * {
        box-sizing: border-box;
        font-family: Arial, Helvetica, sans-serif !important;
    }

    .assignment-card {
        background:#ffffff;
        border:1px solid #e5e7eb;
        border-radius:8px;
        box-shadow:0 2px 8px rgba(0,0,0,.06);
        padding:20px;
    }

    .assignment-header {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        margin-bottom:15px;
        flex-wrap:wrap;
    }

    .assignment-title {
        margin:0;
        font-size:20px;
        font-weight:700;
        color:#1d4ed8;
    }

    .assignment-btn {
        height:34px;
        padding:5px 14px;
        border:0;
        border-radius:5px;
        font-size:12px;
        font-weight:600;
        cursor:pointer;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        white-space:nowrap;
    }

    .btn-add {
        background:#2563eb;
        color:#ffffff;
    }

    .btn-add:hover {
        background:#1d4ed8;
    }

    .btn-edit {
        background:#f59e0b;
        color:#ffffff;
    }

    .btn-edit:hover {
        background:#d97706;
    }

    .btn-delete {
        background:#dc2626;
        color:#ffffff;
    }

    .btn-delete:hover {
        background:#b91c1c;
    }

    .message-success {
        background:#dcfce7;
        color:#166534;
        border:1px solid #86efac;
        padding:10px 12px;
        border-radius:5px;
        margin-bottom:12px;
        font-size:13px;
        font-weight:600;
    }

    .message-error {
        background:#fee2e2;
        color:#991b1b;
        border:1px solid #fca5a5;
        padding:10px 12px;
        border-radius:5px;
        margin-bottom:12px;
        font-size:13px;
        font-weight:600;
    }

    .table-wrapper {
        width:100%;
        overflow-x:auto;
    }

    .assignment-table {
        width:100%;
        border-collapse:collapse;
        font-size:13px;
    }

    .assignment-table th {
        background:#dbeafe;
        color:#1e3a8a;
        border:1px solid #cbd5e1;
        padding:8px;
        text-align:center;
        font-weight:700;
        white-space:nowrap;
    }

    .assignment-table td {
        border:1px solid #d1d5db;
        padding:8px;
        vertical-align:middle;
    }

    .assignment-table tbody tr:hover {
        background:#f8fafc;
    }

    .id-column {
        width:60px;
        min-width:60px;
        text-align:center;
    }

    .user-column {
        min-width:190px;
        font-weight:600;
    }

    .designation-column {
        min-width:170px;
    }

    .year-column {
        min-width:120px;
    }

    .standard-column {
        width:130px;
        min-width:130px;
    }

    .division-column {
        width:100px;
        min-width:100px;
        text-align:center;
    }

    .action-column {
        width:180px;
        min-width:180px;
        text-align:center;
        white-space:nowrap;
    }

    .action-buttons {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        white-space:nowrap;
    }

    .action-form {
        display:inline;
        margin:0;
        padding:0;
    }

    .no-data {
        padding:20px !important;
        text-align:center;
        color:#6b7280;
        font-weight:600;
    }

</style>


<div class="erp-page user-designation-page">

    <div class="assignment-card">

        {{-- =========================================================
             HEADER
        ========================================================== --}}

        <div class="assignment-header">

            <h2 class="assignment-title">
                USER DESIGNATION ASSIGNMENT
            </h2>


            <a
                href="{{ route('user-designations.create') }}"
                class="assignment-btn btn-add"
            >
                Assign Designation
            </a>

        </div>


        {{-- SUCCESS --}}

        @if(session('success'))

            <div class="message-success">
                {{ session('success') }}
            </div>

        @endif


        {{-- ERROR --}}

        @if(session('error'))

            <div class="message-error">
                {{ session('error') }}
            </div>

        @endif


        {{-- TABLE --}}

        <div class="table-wrapper">

            <table class="assignment-table">

                <thead>

                    <tr>

                        <th class="id-column">
                            ID
                        </th>

                        <th class="user-column">
                            User
                        </th>

                        <th class="designation-column">
                            Designation
                        </th>

                        <th class="year-column">
                            Academic Year
                        </th>

                        <th class="standard-column">
                            Standard
                        </th>

                        <th class="division-column">
                            Division
                        </th>

                        <th class="action-column">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                @forelse(
                    $assignments as $assignment
                )

                    <tr>

                        <td class="id-column">
                            {{ $assignment->id }}
                        </td>


                        <td class="user-column">

                            {{
                                optional(
                                    $assignment->user
                                )->name
                                ?? '-'
                            }}

                            @if(
                                optional(
                                    $assignment->user
                                )->email
                            )

                                <div style="
                                    font-size:11px;
                                    font-weight:400;
                                    color:#6b7280;
                                    margin-top:2px;
                                ">
                                    {{
                                        $assignment->user->email
                                    }}
                                </div>

                            @endif

                        </td>


                        <td class="designation-column">

                            {{
                                optional(
                                    $assignment->designation
                                )->designation_name
                                ?? '-'
                            }}

                        </td>


                        <td class="year-column">

                            {{
                                optional(
                                    $assignment->academicYear
                                )->year_name
                                ??
                                '-'
                            }}

                        </td>


                        <td class="standard-column">

                            {{
                                optional(
                                    $assignment->standard
                                )->standard_name
                                ??
                                '-'
                            }}

                        </td>


                        <td class="division-column">

                            {{
                                optional(
                                    $assignment->division
                                )->division_name
                                ??
                                '-'
                            }}

                        </td>


                        <td class="action-column">

                            <div class="action-buttons">

                                <a
                                    href="{{ route(
                                        'user-designations.edit',
                                        $assignment
                                    ) }}"
                                    class="assignment-btn btn-edit"
                                >
                                    Edit
                                </a>


                                <form
                                    method="POST"
                                    action="{{ route(
                                        'user-designations.destroy',
                                        $assignment
                                    ) }}"
                                    class="action-form"
                                    onsubmit="
                                        return confirm(
                                            'Delete this designation assignment?'
                                        );
                                    "
                                >

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="assignment-btn btn-delete"
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
                            colspan="7"
                            class="no-data"
                        >
                            No user designation assignments found.
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

</x-app-layout>