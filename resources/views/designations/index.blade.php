<x-app-layout>

<style>

    .designation-page,
    .designation-page * {
        box-sizing: border-box;
        font-family: Arial, Helvetica, sans-serif !important;
    }


    .designation-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        padding: 20px;
    }


    .designation-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }


    .designation-title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #1d4ed8;
    }


    .designation-btn {
        height: 34px;
        padding: 5px 14px;
        border: 0;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }


    .designation-btn-add {
        background: #2563eb;
        color: #ffffff;
    }


    .designation-btn-add:hover {
        background: #1d4ed8;
    }


    .designation-btn-edit {
        background: #f59e0b;
        color: #ffffff;
    }


    .designation-btn-edit:hover {
        background: #d97706;
    }


    .designation-btn-delete {
        background: #dc2626;
        color: #ffffff;
    }


    .designation-btn-delete:hover {
        background: #b91c1c;
    }


    .message-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
        padding: 10px 12px;
        border-radius: 5px;
        margin-bottom: 12px;
        font-size: 13px;
        font-weight: 600;
    }


    .message-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
        padding: 10px 12px;
        border-radius: 5px;
        margin-bottom: 12px;
        font-size: 13px;
        font-weight: 600;
    }


    .designation-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }


    .designation-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        background: #ffffff;
    }


    .designation-table th {
        background: #dbeafe;
        color: #1e3a8a;
        border: 1px solid #cbd5e1;
        padding: 8px;
        text-align: center;
        font-weight: 700;
        white-space: nowrap;
    }


    .designation-table td {
        border: 1px solid #d1d5db;
        padding: 8px;
        vertical-align: middle;
    }


    .designation-table tbody tr:hover {
        background: #f8fafc;
    }


    .designation-id {
        width: 70px;
        min-width: 70px;
        text-align: center;
    }


    .designation-name {
        font-weight: 600;
        min-width: 180px;
    }


    .designation-code {
        width: 110px;
        min-width: 110px;
        font-weight: 600;
    }


    .designation-section {
        width: 180px;
        min-width: 180px;
        font-weight: 600;
    }


    .designation-description {
        min-width: 250px;
    }


    .designation-status {
        width: 110px;
        min-width: 110px;
        text-align: center;
    }


    .designation-action {
        width: 190px;
        min-width: 190px;
        text-align: center;
        white-space: nowrap !important;
    }


    .status-active,
    .status-inactive {
        display: inline-block;
        padding: 4px 9px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
    }


    .status-active {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }


    .status-inactive {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }


    .action-form {
        display: inline;
        margin: 0;
        padding: 0;
    }


    .action-buttons {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
    }


    .no-data {
        padding: 20px !important;
        text-align: center;
        color: #6b7280;
        font-weight: 600;
    }


    @media (max-width: 700px) {

        .designation-card {
            padding: 12px;
        }

        .designation-header {
            align-items: flex-start;
        }

    }

</style>


<div class="erp-page designation-page">

    <div class="designation-card">

        {{-- =========================================================
             HEADER
        ========================================================== --}}

        <div class="designation-header">

            <h2 class="designation-title">
                DESIGNATION MASTER
            </h2>


            <a
                href="{{ route('designations.create') }}"
                class="designation-btn designation-btn-add"
            >
                Add Designation
            </a>

        </div>


        {{-- =========================================================
             SUCCESS MESSAGE
        ========================================================== --}}

        @if(session('success'))

            <div class="message-success">
                {{ session('success') }}
            </div>

        @endif


        {{-- =========================================================
             ERROR MESSAGE
        ========================================================== --}}

        @if(session('error'))

            <div class="message-error">
                {{ session('error') }}
            </div>

        @endif


        {{-- =========================================================
             VALIDATION ERRORS
        ========================================================== --}}

        @if($errors->any())

            <div class="message-error">

                <strong>
                    Please correct the following:
                </strong>

                <ul style="
                    margin:6px 0 0 20px;
                    padding:0;
                ">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- =========================================================
             TABLE
        ========================================================== --}}

        <div class="designation-table-wrapper">

            <table class="designation-table">

                <thead>

                    <tr>

                        {{-- ID --}}

                        <th class="designation-id">
                            ID
                        </th>


                        {{-- DESIGNATION --}}

                        <th class="designation-name">
                            Designation
                        </th>


                        {{-- CODE --}}

                        <th class="designation-code">
                            Code
                        </th>


                        {{-- SECTION --}}

                        <th class="designation-section">
                            Section
                        </th>


                        {{-- DESCRIPTION --}}

                        <th class="designation-description">
                            Description
                        </th>


                        {{-- STATUS --}}

                        <th class="designation-status">
                            Status
                        </th>


                        {{-- ACTION --}}

                        <th class="designation-action">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                @forelse($designations as $designation)

                    <tr>

                        {{-- =================================================
                             ID
                        ================================================== --}}

                        <td class="designation-id">
                            {{ $designation->id }}
                        </td>


                        {{-- =================================================
                             DESIGNATION
                        ================================================== --}}

                        <td class="designation-name">
                            {{ $designation->designation_name }}
                        </td>


                        {{-- =================================================
                             CODE
                        ================================================== --}}

                        <td class="designation-code">
                            {{ $designation->designation_code }}
                        </td>


                        {{-- =================================================
                             SECTION
                        ================================================== --}}

                        <td class="designation-section">

                            @if($designation->section)

                                {{ $designation->section->section_name }}

                            @else

                                -

                            @endif

                        </td>


                        {{-- =================================================
                             DESCRIPTION
                        ================================================== --}}

                        <td class="designation-description">
                            {{ $designation->description ?: '-' }}
                        </td>


                        {{-- =================================================
                             STATUS
                        ================================================== --}}

                        <td class="designation-status">

                            @if($designation->is_active)

                                <span class="status-active">
                                    ACTIVE
                                </span>

                            @else

                                <span class="status-inactive">
                                    INACTIVE
                                </span>

                            @endif

                        </td>


                        {{-- =================================================
                             ACTION
                        ================================================== --}}

                        <td class="designation-action">

                            <div class="action-buttons">

                                {{-- EDIT --}}

                                <a
                                    href="{{ route(
                                        'designations.edit',
                                        $designation
                                    ) }}"
                                    class="
                                        designation-btn
                                        designation-btn-edit
                                    "
                                >
                                    Edit
                                </a>


                                {{-- DELETE --}}

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'designations.destroy',
                                        $designation
                                    ) }}"
                                    class="action-form"
                                    onsubmit="
                                        return confirm(
                                            'Are you sure you want to delete this designation?'
                                        );
                                    "
                                >

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="
                                            designation-btn
                                            designation-btn-delete
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
                            colspan="7"
                            class="no-data"
                        >
                            No designations found.
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

</x-app-layout>