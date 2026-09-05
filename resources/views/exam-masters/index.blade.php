<x-app-layout>

<style>

.exam-master-page,
.exam-master-page * {
    font-family: Arial, sans-serif !important;
    font-size: 13px !important;
}

.exam-master-page h2 {
    font-size: 22px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    margin: 0;
}

.exam-master-page table {
    width: 100%;
    font-size: 13px !important;
    border-collapse: collapse;
}

.exam-master-page table th {
    font-size: 13px !important;
    font-weight: 700 !important;
    text-align: center;
    padding: 8px !important;
    white-space: nowrap;
}

.exam-master-page table td {
    font-size: 13px !important;
    padding: 8px !important;
    vertical-align: middle;
}

.exam-master-page .erp-btn {
    font-size: 13px !important;
    padding: 5px 10px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 700;
    cursor: pointer;
}

.exam-master-page .erp-btn-add {
    background: #16A34A;
    color: #ffffff !important;
    border: 1px solid #15803D;
}

.exam-master-page .erp-btn-add:hover {
    background: #15803D;
}

.exam-master-page .erp-btn-edit {
    background: #2563EB;
    color: #ffffff !important;
    border: 1px solid #1D4ED8;
}

.exam-master-page .erp-btn-edit:hover {
    background: #1D4ED8;
}

.exam-master-page .erp-btn-delete {
    background: #DC2626;
    color: #ffffff !important;
    border: 1px solid #B91C1C;
}

.exam-master-page .erp-btn-delete:hover {
    background: #B91C1C;
}

.exam-master-page .text-green-700,
.exam-master-page .text-red-700 {
    font-size: 13px !important;
    font-weight: 600 !important;
}


/* ==========================================================================
   FILTER
   ========================================================================== */

.exam-master-filter {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}

.exam-master-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.exam-master-filter-label {
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #374151;
}

.exam-master-filter-select {
    height: 34px;
    min-width: 190px;
    padding: 4px 9px;
    font-size: 12px !important;
    font-weight: 600 !important;
    border: 1px solid #9CA3AF;
    border-radius: 5px;
    background: #ffffff;
    color: #111827;
}

.exam-master-filter-select:focus {
    outline: none;
    border-color: #2563EB;
    box-shadow: 0 0 0 1px #2563EB;
}

.exam-master-filter-button {
    height: 34px;
    padding: 5px 12px !important;
    background: #2563EB;
    color: #ffffff;
    border: 1px solid #1D4ED8;
}

.exam-master-filter-button:hover {
    background: #1D4ED8;
}

.exam-master-clear-button {
    height: 34px;
    padding: 5px 12px !important;
    background: #6B7280;
    color: #ffffff !important;
    border: 1px solid #4B5563;
}

.exam-master-clear-button:hover {
    background: #4B5563;
}


/* ==========================================================================
   PASSING PERCENTAGE
   ========================================================================== */

.passing-percentage-35 {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    background: #DCFCE7;
    color: #166534;
    border: 1px solid #86EFAC;
    font-weight: 700;
    font-size: 12px !important;
}

.passing-percentage-40 {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    background: #DBEAFE;
    color: #1E40AF;
    border: 1px solid #93C5FD;
    font-weight: 700;
    font-size: 12px !important;
}


/* ==========================================================================
   EXAM / STANDARD
   ========================================================================== */

.exam-name-cell {
    font-weight: 600;
    min-width: 230px;
}

.standard-name-cell {
    font-weight: 600;
}

.action-cell {
    min-width: 180px;
}


/* ==========================================================================
   ACTIVE / INACTIVE
   ========================================================================== */

.active-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    background: #DCFCE7;
    color: #166534;
    border: 1px solid #86EFAC;
    font-weight: 700;
    font-size: 12px !important;
}

.inactive-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    background: #FEE2E2;
    color: #991B1B;
    border: 1px solid #FCA5A5;
    font-weight: 700;
    font-size: 12px !important;
}


/* ==========================================================================
   RESPONSIVE
   ========================================================================== */

@media (max-width: 800px) {

    .exam-master-filter {
        flex-direction: column;
        align-items: stretch;
    }

    .exam-master-filter-group {
        width: 100%;
    }

    .exam-master-filter-select {
        width: 100%;
    }

    .exam-master-filter .erp-btn {
        width: 100%;
    }

}

</style>


<div class="max-w-7xl mx-auto py-4 px-4 exam-master-page">

    <div class="
        bg-gradient-to-br
        from-red-100
        via-yellow-100
        to-orange-100
        rounded-xl
        shadow-xl
        border-4
        border-amber-400
        p-5
    ">


        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div class="flex justify-between items-center mb-4">

            <h2>
                Exam Master
            </h2>

            <a
                href="{{ route('exam-masters.create') }}"
                class="erp-btn erp-btn-add"
            >
                + Add Exam
            </a>

        </div>


        {{-- =====================================================
             ACADEMIC YEAR FILTER
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('exam-masters.index') }}"
        >

            <div class="exam-master-filter">

                <div class="exam-master-filter-group">

                    <label
                        for="academic_year_id"
                        class="exam-master-filter-label"
                    >
                        Academic Year
                    </label>


                    <select
                        name="academic_year_id"
                        id="academic_year_id"
                        class="exam-master-filter-select"
                    >

                        <option value="">
                            All Academic Years
                        </option>


                        @foreach($academicYears as $academicYear)

                            <option
                                value="{{ $academicYear->id }}"
                                {{ (string)($academicYearId ?? request('academic_year_id')) === (string)$academicYear->id ? 'selected' : '' }}
                            >
                                {{ $academicYear->year_name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                <button
                    type="submit"
                    class="erp-btn exam-master-filter-button"
                >
                    Filter
                </button>


                <a
                    href="{{ route('exam-masters.index') }}"
                    class="erp-btn exam-master-clear-button"
                >
                    Clear
                </a>

            </div>

        </form>


        {{-- =====================================================
             SUCCESS MESSAGE
        ====================================================== --}}

        @if(session('success'))

            <div class="
                bg-green-100
                border
                border-green-300
                text-green-700
                p-3
                rounded
                mb-4
            ">

                {{ session('success') }}

            </div>

        @endif


        {{-- =====================================================
             ERROR MESSAGE
        ====================================================== --}}

        @if(session('error'))

            <div class="
                bg-red-100
                border
                border-red-300
                text-red-700
                p-3
                rounded
                mb-4
            ">

                {{ session('error') }}

            </div>

        @endif


        {{-- =====================================================
             EXAM MASTER TABLE
        ====================================================== --}}

        <div class="overflow-x-auto">

            <table class="
                border
                border-gray-400
                bg-white
            ">

                <thead class="bg-blue-200">

                    <tr>

                        <th class="border">
                            Exam ID
                        </th>

                        <th class="border">
                            Academic Year
                        </th>

                        <th class="border">
                            Exam Name
                        </th>

                        <th class="border">
                            Standard ID
                        </th>

                        <th class="border">
                            Standard Name
                        </th>

                        <th class="border">
                            Passing %
                        </th>

                        <th class="border">
                            Status
                        </th>

                        <th class="border">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                @forelse($examMasters as $exam)

                    @php

                        /*
                        |--------------------------------------------------------------------------
                        | STANDARD ID
                        |--------------------------------------------------------------------------
                        */

                        $standardId =
                            (int) (
                                $exam->standard_id
                                ?? 0
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | STANDARD NAME
                        |--------------------------------------------------------------------------
                        */

                        $standardName =
                            strtoupper(
                                trim(
                                    (string) (
                                        optional(
                                            $exam->standard
                                        )->standard_name
                                        ?? ''
                                    )
                                )
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | NORMALIZED STANDARD NAME
                        |--------------------------------------------------------------------------
                        */

                        $normalizedStandardName =
                            preg_replace(
                                '/[^A-Z0-9]+/',
                                '',
                                $standardName
                            ) ?? '';


                        /*
                        |--------------------------------------------------------------------------
                        | PASSING PERCENTAGE
                        |--------------------------------------------------------------------------
                        |
                        | 35%:
                        |
                        | Nursery
                        | JrKg
                        | SrKg
                        | 9th
                        | 10th
                        | 11th
                        | 12th
                        |
                        | 40%:
                        | All other Standards
                        |
                        |--------------------------------------------------------------------------
                        */

                        $is35PercentStandard =

                            /*
                            |------------------------------------------------------
                            | FIXED STANDARD IDs
                            |------------------------------------------------------
                            */

                            in_array(
                                $standardId,
                                [
                                    9,
                                    10,
                                    11,
                                    12,
                                ],
                                true
                            )

                            ||

                            /*
                            |------------------------------------------------------
                            | NURSERY / KG BY NAME
                            |------------------------------------------------------
                            */

                            in_array(
                                $normalizedStandardName,
                                [
                                    'NURSERY',
                                    'NUR',

                                    'JRKG',
                                    'JUNIORKG',
                                    'JUNIORKINDERGARTEN',

                                    'SRKG',
                                    'SENIORKG',
                                    'SENIORKINDERGARTEN',
                                ],
                                true
                            )

                            ||

                            /*
                            |------------------------------------------------------
                            | 9TH / 10TH BY NAME
                            |------------------------------------------------------
                            */

                            in_array(
                                $normalizedStandardName,
                                [
                                    'NINTH',
                                    '9TH',
                                    'IX',

                                    'TENTH',
                                    '10TH',
                                    'X',
                                ],
                                true
                            )

                            ||

                            /*
                            |------------------------------------------------------
                            | 11TH BY NAME
                            |------------------------------------------------------
                            |
                            | This is the important fix for:
                            |
                            | ELEVENTH
                            | ELEVENTH SCIENCE
                            | ELEVENTH COMMERCE
                            | ELEVENTH ARTS
                            | ELEVENTH HUMANITIES
                            |
                            | Your actual record is:
                            |
                            | standard_id = 19
                            | standard_name = ELEVENTH SCIENCE
                            |
                            | Therefore it MUST be 35%.
                            |
                            */

                            str_contains(
                                $normalizedStandardName,
                                'ELEVENTH'
                            )

                            ||

                            $normalizedStandardName === 'XI'

                            ||

                            /*
                            |------------------------------------------------------
                            | 12TH BY NAME
                            |------------------------------------------------------
                            */

                            str_contains(
                                $normalizedStandardName,
                                'TWELFTH'
                            )

                            ||

                            $normalizedStandardName === 'XII';


                        $passingPercentage =
                            $is35PercentStandard
                                ? 35
                                : 40;

                    @endphp


                    <tr class="hover:bg-yellow-50">


                        {{-- =================================================
                             EXAM ID
                        ================================================== --}}

                        <td class="border text-center">

                            {{ $exam->id }}

                        </td>


                        {{-- =================================================
                             ACADEMIC YEAR
                        ================================================== --}}

                        <td class="border text-center">

                            {{
                                $exam->academicYear->year_name
                                ?? '-'
                            }}

                        </td>


                        {{-- =================================================
                             EXAM NAME
                        ================================================== --}}

                        <td class="border exam-name-cell">

                            {{ $exam->exam_name }}

                        </td>


                        {{-- =================================================
                             STANDARD ID
                        ================================================== --}}

                        <td class="border text-center">

                            {{ $exam->standard_id }}

                        </td>


                        {{-- =================================================
                             STANDARD NAME
                        ================================================== --}}

                        <td class="border standard-name-cell">

                            {{
                                $exam->standard->standard_name
                                ?? '-'
                            }}

                        </td>


                        {{-- =================================================
                             PASSING PERCENTAGE
                        ================================================== --}}

                        <td class="border text-center">

                            <span
                                class="{{
                                    $passingPercentage === 35
                                        ? 'passing-percentage-35'
                                        : 'passing-percentage-40'
                                }}"
                            >

                                {{ $passingPercentage }}%

                            </span>

                        </td>


                        {{-- =================================================
                             STATUS
                        ================================================== --}}

                        <td class="border text-center">

                            @if($exam->is_active)

                                <span class="active-badge">
                                    ACTIVE
                                </span>

                            @else

                                <span class="inactive-badge">
                                    INACTIVE
                                </span>

                            @endif

                        </td>


                        {{-- =================================================
                             ACTION
                        ================================================== --}}

                        <td class="
                            border
                            text-center
                            whitespace-nowrap
                            action-cell
                        ">


                            {{-- EDIT --}}

                            <a
                                href="{{ route(
                                    'exam-masters.edit',
                                    $exam->id
                                ) }}"
                                class="erp-btn erp-btn-edit"
                            >
                                Edit
                            </a>


                            {{-- DELETE --}}

                            <form
                                action="{{ route(
                                    'exam-masters.destroy',
                                    $exam->id
                                ) }}"
                                method="POST"
                                style="display:inline-block;"
                            >

                                @csrf

                                @method('DELETE')


                                <button
                                    type="submit"
                                    class="erp-btn erp-btn-delete"
                                    onclick="return confirmExamDelete();"
                                >
                                    Force Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="8"
                            class="
                                border
                                p-4
                                text-center
                                text-gray-500
                            "
                        >
                            No Records Found
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- =========================================================
     DELETE CONFIRMATION
========================================================= --}}

<script>

function confirmExamDelete()
{
    return confirm(
        'FINAL WARNING!\n\n' +

        'You are about to permanently delete this Exam.\n\n' +

        'Depending on the controller/database relationships, ' +
        'the following related records may also be deleted:\n\n' +

        '• Exam Subject Configuration\n' +
        '• Teacher Marks Status\n' +
        '• Marks Entries\n' +
        '• Student Marks\n' +
        '• Student Results\n' +
        '• Result Details\n\n' +

        'THIS ACTION CANNOT BE UNDONE.\n\n' +

        'Click OK only if you are sure you want to delete this Exam.'
    );
}

</script>

</x-app-layout>