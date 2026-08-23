<x-app-layout>

@php

    /*
    |--------------------------------------------------------------------------
    | SELECTED VALUES
    |--------------------------------------------------------------------------
    */

    $selectedTsaId =
        request('teacher_subject_allocation_id');

    $selectedExamId =
        request('exam_master_id');

    $selectedAcademicYearId =
        request('academic_year_id');

    $marksUpdated =
        request()->boolean('marks_updated');

    $marksReopened =
        request()->boolean('marks_reopened');


    /*
    |--------------------------------------------------------------------------
    | SUBJECT
    |--------------------------------------------------------------------------
    */

    $selectedSubjectName =
        optional(
            $teacherSubjectAllocation
        )->subject->subject_name
        ?? '';


    /*
    |--------------------------------------------------------------------------
    | STUDENT COUNT
    |--------------------------------------------------------------------------
    */

    $studentCount =
        isset($students)
            ? $students->count()
            : 0;


    /*
    |--------------------------------------------------------------------------
    | LAST MODIFIED MARK
    |--------------------------------------------------------------------------
    */

    $latestModifiedMark =
        collect(
            $existingMarks ?? []
        )
        ->filter(
            function ($mark) {

                return !empty(
                    $mark->updated_at
                );

            }
        )
        ->sortByDesc(
            function ($mark) {

                return $mark->updated_at;

            }
        )
        ->first();


    /*
    |--------------------------------------------------------------------------
    | LAST MODIFIED USER
    |--------------------------------------------------------------------------
    */

    $lastModifiedById =
        $latestModifiedMark->updated_by
        ?? null;


    $lastModifiedByUser =
        null;


    if (
        $lastModifiedById
    ) {

        try {

            $lastModifiedByUser =
                \App\Models\User::find(
                    $lastModifiedById
                );

        } catch (
            \Throwable $e
        ) {

            $lastModifiedByUser =
                null;

        }

    }


    $lastModifiedByName =
        $lastModifiedByUser->name
        ?? (
            $lastModifiedById
                ? 'User ID ' . $lastModifiedById
                : ''
        );


    $lastModifiedAt =
        $latestModifiedMark->updated_at
        ?? null;

@endphp


<style>

/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.admin-marks-page,
.admin-marks-page * {
    box-sizing: border-box;
    font-family: Arial, sans-serif !important;
}


.admin-marks-page h2 {
    margin: 0 0 15px;
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #1d4ed8 !important;
}


/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
*/

.admin-filter-row {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 10px;
    width: 100%;
}


.admin-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 0 0 auto;
}


.admin-filter-label {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}


.admin-filter-wrapper {
    position: relative;
    display: inline-block;
}


/*
|--------------------------------------------------------------------------
| FILTER SELECT
|--------------------------------------------------------------------------
*/

.admin-filter-select {
    height: 34px;
    padding: 5px 30px 5px 10px;
    border: 1px solid #d1d5db;
    border-radius: 5px;
    background: #fff;
    color: #111827;
    font-size: 12px;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
}


.admin-filter-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 1px #2563eb;
}


.admin-dropdown-arrow {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 6px solid #6b7280;
    pointer-events: none;
}


/*
|--------------------------------------------------------------------------
| ACADEMIC YEAR
|--------------------------------------------------------------------------
*/

.admin-academic-year-select {
    width: 165px;
}


/*
|--------------------------------------------------------------------------
| EXAM
|--------------------------------------------------------------------------
*/

.admin-exam-select {
    width: 250px;
}


/*
|--------------------------------------------------------------------------
| TEACHING ASSIGNMENT
|--------------------------------------------------------------------------
|
| Adjustable according to content.
|
*/

.admin-assignment-group {
    flex: 0 1 auto;
    min-width: 220px;
    max-width: 470px;
}


.admin-assignment-wrapper {
    width: max-content;
    max-width: 100%;
}


.admin-assignment-select {
    width: max-content;
    min-width: 260px;
    max-width: 470px;
    padding-right: 32px;
}


/*
|--------------------------------------------------------------------------
| LOAD + RESET GROUP
|--------------------------------------------------------------------------
|
| They remain together on one row with Teaching Assignment.
|--------------------------------------------------------------------------
*/

.admin-filter-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
    padding-bottom: 0;
}


/*
|--------------------------------------------------------------------------
| BUTTONS
|--------------------------------------------------------------------------
*/

.admin-erp-btn {
    height: 34px;
    padding: 5px 14px;
    border: 0;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    white-space: nowrap;
}


.admin-btn-blue {
    background: #2563eb;
    color: #fff;
}


.admin-btn-blue:hover {
    background: #1d4ed8;
}


.admin-btn-green {
    background: #16a34a;
    color: #fff;
}


.admin-btn-green:hover {
    background: #15803d;
}


.admin-btn-gray {
    background: #6b7280;
    color: #fff;
}


.admin-btn-gray:hover {
    background: #4b5563;
}


.admin-erp-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}


/*
|--------------------------------------------------------------------------
| MESSAGE BOXES
|--------------------------------------------------------------------------
*/

.admin-info-box {
    margin-bottom: 12px;
    padding: 10px 12px;
    border-radius: 5px;
    font-size: 12px;
}


.admin-success-box {
    background: #ecfdf5;
    border: 1px solid #10b981;
    color: #065f46;
}


.admin-warning-box {
    background: #fffbeb;
    border: 1px solid #f59e0b;
    color: #92400e;
}


.admin-error-box {
    background: #fef2f2;
    border: 1px solid #ef4444;
    color: #991b1b;
}


/*
|--------------------------------------------------------------------------
| SELECTED INFORMATION
|--------------------------------------------------------------------------
*/

.admin-selected-info {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
    padding: 10px 12px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 5px;
    color: #1e3a8a;
    font-size: 12px;
}


.admin-selected-item {
    font-weight: 700;
}


.admin-selected-separator {
    color: #93c5fd;
}


/*
|--------------------------------------------------------------------------
| LAST MODIFIED INFORMATION
|--------------------------------------------------------------------------
*/

.admin-modified-info {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    color: #374151;
    font-size: 12px;
}


.admin-modified-title {
    font-weight: 700;
    color: #1e3a8a;
}


.admin-modified-value {
    font-weight: 700;
    color: #111827;
}


.admin-modified-separator {
    color: #94a3b8;
}


/*
|--------------------------------------------------------------------------
| STATUS BADGE
|--------------------------------------------------------------------------
*/

.admin-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
}


.admin-status-pending {
    background: #fef3c7;
    color: #92400e;
}


.admin-status-completed {
    background: #dcfce7;
    color: #166534;
}


.admin-status-locked {
    background: #fee2e2;
    color: #991b1b;
}


.admin-status-default {
    background: #e5e7eb;
    color: #374151;
}


/*
|--------------------------------------------------------------------------
| MARKS CARD
|--------------------------------------------------------------------------
*/

.admin-marks-card {
    margin-top: 12px;
}


.admin-marks-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 10px;
    margin-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}


.admin-marks-header-title {
    font-size: 16px;
    font-weight: 700;
    color: #1d4ed8;
}


.admin-marks-header-subtitle {
    margin-top: 3px;
    color: #6b7280;
    font-size: 12px;
}


.admin-student-count {
    padding: 6px 10px;
    background: #dbeafe;
    color: #1e40af;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| TABLE
|--------------------------------------------------------------------------
*/

.admin-marks-table-wrapper {
    overflow-x: auto;
    border: 1px solid #d1d5db;
    border-radius: 5px;
}


.admin-marks-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    font-size: 12px;
}


.admin-marks-table th {
    background: #dbeafe;
    color: #1e3a8a;
    border: 1px solid #cbd5e1;
    padding: 8px 6px;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    font-weight: 700;
}


.admin-marks-table td {
    border: 1px solid #d1d5db;
    padding: 6px;
    vertical-align: middle;
    white-space: nowrap;
}


.admin-marks-table tbody tr:hover {
    background: #f8fafc;
}


.admin-center {
    text-align: center;
}


.admin-student-name {
    min-width: 300px;
    white-space: normal !important;
}


/*
|--------------------------------------------------------------------------
| MARK INPUT
|--------------------------------------------------------------------------
*/

.admin-mark-input {
    width: 62px;
    height: 30px;
    padding: 3px 5px;
    border: 1px solid #9ca3af;
    border-radius: 4px;
    text-align: center;
    font-size: 13px;
    font-weight: 600;
    background: #ffffff;
}


.admin-mark-input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 1px #2563eb;
}


.admin-mark-input:read-only {
    background: #f3f4f6;
    color: #6b7280;
    cursor: not-allowed;
}


.admin-absent-input {
    background: #fee2e2 !important;
    color: #991b1b;
}


/*
|--------------------------------------------------------------------------
| ATTENDANCE
|--------------------------------------------------------------------------
*/

.admin-attendance-btn {
    min-width: 82px;
    padding: 5px 9px;
    border: 0;
    border-radius: 4px;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}


.admin-present-btn {
    background: #16a34a;
}


.admin-present-btn:hover {
    background: #15803d;
}


.admin-absent-btn {
    background: #dc2626;
}


.admin-absent-btn:hover {
    background: #b91c1c;
}


.admin-status-present {
    color: #15803d;
    font-weight: 700;
}


.admin-status-absent {
    color: #dc2626;
    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| ACTION ROW
|--------------------------------------------------------------------------
*/

.admin-action-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px solid #e5e7eb;
    flex-wrap: wrap;
}


.admin-action-note {
    margin-left: auto;
    font-size: 12px;
    color: #6b7280;
}


/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media (max-width: 1100px) {

    .admin-assignment-group {
        max-width: 390px;
    }

    .admin-assignment-select {
        min-width: 220px;
        max-width: 390px;
    }

}


@media (max-width: 900px) {

    .admin-filter-group {
        width: 100%;
    }

    .admin-academic-year-select,
    .admin-exam-select,
    .admin-assignment-select {
        width: 100%;
        max-width: none;
    }

    .admin-assignment-wrapper {
        width: 100%;
    }

    .admin-assignment-group {
        max-width: none;
    }

    .admin-filter-actions {
        width: 100%;
    }

    .admin-filter-actions .admin-erp-btn {
        flex: 0 0 auto;
    }

    .admin-marks-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .admin-action-note {
        margin-left: 0;
    }

}

</style>


<div class="erp-page admin-marks-page">


    {{-- ==============================================================
         FILTER CARD
    ============================================================== --}}

    <div class="erp-card">

        <h2>
            EDIT EXAMINATION MARKS
        </h2>


        {{-- ==========================================================
             VALIDATION ERRORS
        =========================================================== --}}

        @if($errors->any())

            <div class="admin-info-box admin-error-box">

                <ul
                    style="
                        margin:0;
                        padding-left:20px;
                    "
                >

                    @foreach($errors->all() as $validationError)

                        <li>
                            {{ $validationError }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- ==========================================================
             UPDATE SUCCESS
        =========================================================== --}}

        @if($marksUpdated)

            <div class="admin-info-box admin-success-box">

                <strong>
                    ✓ Marks updated successfully.
                </strong>

                The current teaching assignment status has not been changed.

            </div>

        @endif


        {{-- ==========================================================
             REOPEN SUCCESS
        =========================================================== --}}

        @if($marksReopened)

            <div class="admin-info-box admin-success-box">

                <strong>
                    ✓ Marks reopened successfully.
                </strong>

            </div>

        @endif


        {{-- ==========================================================
             CONTROLLER ERROR
        =========================================================== --}}

        @if(!empty($error))

            <div class="admin-info-box admin-error-box">

                <strong>
                    Error:
                </strong>

                {{ $error }}

            </div>

        @endif


        {{-- ==========================================================
             FILTER FORM
        =========================================================== --}}

        <form
            method="GET"
            action="{{ route('result-generation.admin-marks.edit') }}"
            id="adminMarksFilterForm"
        >

            <div class="admin-filter-row">


                {{-- ==================================================
                     ACADEMIC YEAR
                =================================================== --}}

                <div class="admin-filter-group">

                    <label class="admin-filter-label">
                        Academic Year
                    </label>

                    <div class="admin-filter-wrapper">

                        <select
                            name="academic_year_id"
                            id="admin_academic_year_id"
                            class="admin-filter-select admin-academic-year-select"
                        >

                            <option value="">
                                All Academic Years
                            </option>

                            @foreach($academicYears as $year)

                                <option
                                    value="{{ $year->id }}"
                                    {{
                                        (string)$selectedAcademicYearId
                                        ===
                                        (string)$year->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{
                                        $year->year_name
                                        ?? $year->name
                                        ?? $year->id
                                    }}

                                </option>

                            @endforeach

                        </select>

                        <span class="admin-dropdown-arrow"></span>

                    </div>

                </div>


                {{-- ==================================================
                     EXAM
                =================================================== --}}

                <div class="admin-filter-group">

                    <label class="admin-filter-label">
                        Exam
                    </label>

                    <div class="admin-filter-wrapper">

                        <select
                            name="exam_master_id"
                            id="admin_exam_master_id"
                            class="admin-filter-select admin-exam-select"
                        >

                            <option value="">
                                Select Exam
                            </option>

                            @foreach($exams as $examItem)

                                <option
                                    value="{{ $examItem->id }}"
                                    data-standard-id="{{
                                        $examItem
                                            ->resolved_standard_id
                                        ?? ''
                                    }}"
                                    {{
                                        (string)$selectedExamId
                                        ===
                                        (string)$examItem->id
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    {{
                                        $examItem->display_exam_name
                                        ?? $examItem->exam_name
                                    }}

                                </option>

                            @endforeach

                        </select>

                        <span class="admin-dropdown-arrow"></span>

                    </div>

                </div>


                {{-- ==================================================
                     TEACHING ASSIGNMENT
                =================================================== --}}

                <div
                    class="
                        admin-filter-group
                        admin-assignment-group
                    "
                >

                    <label class="admin-filter-label">
                        Teaching Assignment
                    </label>

                    <div
                        class="
                            admin-filter-wrapper
                            admin-assignment-wrapper
                        "
                    >

                        <select
                            name="teacher_subject_allocation_id"
                            id="admin_teacher_subject_allocation_id"
                            class="
                                admin-filter-select
                                admin-assignment-select
                            "
                            {{
                                !$selectedExamId
                                    ? 'disabled'
                                    : ''
                            }}
                        >

                            @if(!$selectedExamId)

                                <option value="">
                                    Select Exam First
                                </option>

                            @elseif($assignments->isEmpty())

                                <option value="">
                                    No Teaching Assignment
                                </option>

                            @else

                                <option value="">
                                    Select Teaching Assignment
                                </option>


                                @foreach($assignments as $assignment)

                                    @php

                                        $teacher =
                                            optional(
                                                $assignment->allocation
                                            )->teacher;

                                        $standard =
                                            optional(
                                                $assignment->allocation
                                            )->standard;

                                        $division =
                                            optional(
                                                $assignment->allocation
                                            )->division;

                                        $teacherName =
                                            optional(
                                                $teacher
                                            )->name
                                            ?? 'Teacher';

                                        $subjectName =
                                            optional(
                                                $assignment->subject
                                            )->subject_name
                                            ?? 'Subject';

                                        $standardName =
                                            optional(
                                                $standard
                                            )->standard_name
                                            ?? '';

                                        $divisionName =
                                            optional(
                                                $division
                                            )->division_name
                                            ?? '';

                                        $status =
                                            strtoupper(
                                                trim(
                                                    (string)(
                                                        $assignment
                                                            ->resolved_status
                                                        ?? 'PENDING'
                                                    )
                                                )
                                            );

                                    @endphp


                                    <option
                                        value="{{ $assignment->id }}"
                                        data-academic-year-id="{{
                                            $assignment
                                                ->resolved_academic_year_id
                                            ?? ''
                                        }}"
                                        data-exam-id="{{
                                            $assignment
                                                ->resolved_exam_master_id
                                            ??
                                            $assignment->exam_master_id
                                        }}"
                                        {{
                                            (string)$selectedTsaId
                                            ===
                                            (string)$assignment->id
                                                ? 'selected'
                                                : ''
                                        }}
                                    >

                                        {{ $teacherName }}
                                        -
                                        {{ $subjectName }}
                                        -
                                        {{ $standardName }}

                                        @if($divisionName)

                                            -
                                            {{ $divisionName }}

                                        @endif

                                        [{{ $status }}]

                                    </option>

                                @endforeach

                            @endif

                        </select>

                        <span class="admin-dropdown-arrow"></span>

                    </div>

                </div>


                {{-- ==================================================
                     LOAD + RESET
                =================================================== --}}

                <div class="admin-filter-actions">

                    <button
                        type="submit"
                        id="adminLoadMarksButton"
                        class="
                            admin-erp-btn
                            admin-btn-blue
                        "
                        {{
                            !$selectedTsaId
                                ? 'disabled'
                                : ''
                        }}
                    >
                        Load Marks
                    </button>


                    <a
                        href="{{
                            route(
                                'result-generation.admin-marks.edit'
                            )
                        }}"
                        class="
                            admin-erp-btn
                            admin-btn-gray
                        "
                    >
                        Reset
                    </a>

                </div>

            </div>

        </form>


        {{-- ==========================================================
             SELECTED INFORMATION
        =========================================================== --}}

        @if(
            $teacherSubjectAllocation &&
            $exam
        )

            <div class="admin-selected-info">


                {{-- TEACHER --}}

                <span>

                    <span class="admin-selected-item">
                        Teacher:
                    </span>

                    {{
                        optional(
                            optional(
                                $teacherSubjectAllocation
                                    ->allocation
                            )->teacher
                        )->name
                        ?? 'Teacher'
                    }}

                </span>


                <span class="admin-selected-separator">
                    |
                </span>


                {{-- SUBJECT --}}

                <span>

                    <span class="admin-selected-item">
                        Subject:
                    </span>

                    {{
                        optional(
                            $teacherSubjectAllocation
                                ->subject
                        )->subject_name
                        ?? 'Subject'
                    }}

                </span>


                <span class="admin-selected-separator">
                    |
                </span>


                {{-- CLASS --}}

                <span>

                    <span class="admin-selected-item">
                        Class:
                    </span>

                    {{
                        optional(
                            optional(
                                $teacherSubjectAllocation
                                    ->allocation
                            )->standard
                        )->standard_name
                        ?? ''
                    }}

                    @if(
                        optional(
                            optional(
                                $teacherSubjectAllocation
                                    ->allocation
                            )->division
                        )->division_name
                    )

                        -
                        {{
                            optional(
                                optional(
                                    $teacherSubjectAllocation
                                        ->allocation
                                )->division
                            )->division_name
                        }}

                    @endif

                </span>


                <span class="admin-selected-separator">
                    |
                </span>


                {{-- EXAM --}}

                <span>

                    <span class="admin-selected-item">
                        Exam:
                    </span>

                    {{
                        $exam->display_exam_name
                        ?? $exam->exam_name
                    }}

                </span>


                <span class="admin-selected-separator">
                    |
                </span>


                {{-- STATUS --}}

                @php

                    $selectedAssignmentRecord =
                        $assignments->firstWhere(
                            'id',
                            $selectedTsaId
                        );

                    $currentStatus =
                        strtoupper(
                            trim(
                                (string)(
                                    optional(
                                        $selectedAssignmentRecord
                                    )->resolved_status
                                    ?? 'PENDING'
                                )
                            )
                        );

                @endphp


                <span>

                    <span class="admin-selected-item">
                        Status:
                    </span>


                    @if(
                        $currentStatus === 'COMPLETED'
                    )

                        <span
                            class="
                                admin-status-badge
                                admin-status-completed
                            "
                        >
                            COMPLETED
                        </span>

                    @elseif(
                        $currentStatus === 'LOCKED'
                    )

                        <span
                            class="
                                admin-status-badge
                                admin-status-locked
                            "
                        >
                            LOCKED
                        </span>

                    @elseif(
                        $currentStatus === 'PENDING'
                    )

                        <span
                            class="
                                admin-status-badge
                                admin-status-pending
                            "
                        >
                            PENDING
                        </span>

                    @else

                        <span
                            class="
                                admin-status-badge
                                admin-status-default
                            "
                        >
                            {{ $currentStatus }}
                        </span>

                    @endif

                </span>


                <span class="admin-selected-separator">
                    |
                </span>


                {{-- STUDENTS --}}

                <span>

                    <span class="admin-selected-item">
                        Students:
                    </span>

                    {{ $studentCount }}

                </span>

            </div>


            {{-- ======================================================
                 LAST MODIFIED
            ======================================================= --}}

            @if($lastModifiedAt)

                <div class="admin-modified-info">

                    <span class="admin-modified-title">
                        Last Modified By:
                    </span>

                    <span class="admin-modified-value">
                        {{ $lastModifiedByName }}
                    </span>


                    <span class="admin-modified-separator">
                        |
                    </span>


                    <span class="admin-modified-title">
                        Last Modified On:
                    </span>

                    <span class="admin-modified-value">

                        {{
                            \Carbon\Carbon::parse(
                                $lastModifiedAt
                            )->format(
                                'd-m-Y H:i:s'
                            )
                        }}

                    </span>

                </div>

            @else

                <div class="admin-modified-info">

                    <span class="admin-modified-title">
                        Last Modified:
                    </span>

                    <span>
                        No marks have been modified yet.
                    </span>

                </div>

            @endif


            {{-- ======================================================
                 ADMIN MESSAGE
            ======================================================= --}}

            @if(!empty($message))

                <div
                    class="admin-info-box admin-warning-box"
                    style="margin-top:10px;"
                >

                    {{ $message }}

                </div>

            @endif

        @endif

    </div>


    {{-- ==============================================================
         MARKS CARD
    ============================================================== --}}

    @if(
        $teacherSubjectAllocation &&
        $studentCount > 0
    )

        <div class="erp-card admin-marks-card">


            {{-- ==================================================
                 HEADER
            =================================================== --}}

            <div class="admin-marks-header">

                <div>

                    <div class="admin-marks-header-title">
                        EDIT MARKS
                    </div>

                    <div class="admin-marks-header-subtitle">

                        Subject:

                        <strong>
                            {{ $selectedSubjectName }}
                        </strong>

                        &nbsp; | &nbsp;

                        Teacher:

                        <strong>
                            {{
                                optional(
                                    optional(
                                        $teacherSubjectAllocation
                                            ->allocation
                                    )->teacher
                                )->name
                                ?? ''
                            }}
                        </strong>

                        &nbsp; | &nbsp;

                        Class:

                        <strong>

                            {{
                                optional(
                                    optional(
                                        $teacherSubjectAllocation
                                            ->allocation
                                    )->standard
                                )->standard_name
                                ?? ''
                            }}

                            @if(
                                optional(
                                    optional(
                                        $teacherSubjectAllocation
                                            ->allocation
                                    )->division
                                )->division_name
                            )

                                -
                                {{
                                    optional(
                                        optional(
                                            $teacherSubjectAllocation
                                                ->allocation
                                        )->division
                                    )->division_name
                                }}

                            @endif

                        </strong>

                    </div>

                </div>


                <div class="admin-student-count">

                    {{ $studentCount }}
                    Students

                </div>

            </div>


            {{-- ==================================================
                 UPDATE FORM
            =================================================== --}}

            <form
                method="POST"
                action="{{ route('admin-marks.update') }}"
                id="adminMarksForm"
            >

                @csrf

                @method('PUT')


                <input
                    type="hidden"
                    name="teacher_subject_allocation_id"
                    value="{{ $teacherSubjectAllocation->id }}"
                >


                <input
                    type="hidden"
                    name="exam_master_id"
                    value="{{ $exam->id }}"
                >


                <div class="admin-marks-table-wrapper">

                    <table class="admin-marks-table">

                        <thead>

                            <tr>

                                <th>
                                    GR No
                                </th>

                                <th>
                                    Roll No
                                </th>

                                <th>
                                    Student Name
                                </th>

                                <th>
                                    Attendance
                                </th>


                                @if($showTheory)

                                    <th>
                                        Theory Max
                                    </th>

                                    <th>
                                        Theory Pass
                                    </th>

                                    <th>
                                        Theory Obtained
                                    </th>

                                @endif


                                @if($showOral)

                                    <th>
                                        Oral Max
                                    </th>

                                    <th>
                                        Oral Pass
                                    </th>

                                    <th>
                                        Oral Obtained
                                    </th>

                                @endif


                                @if($showPractical)

                                    <th>
                                        Practical Max
                                    </th>

                                    <th>
                                        Practical Pass
                                    </th>

                                    <th>
                                        Practical Obtained
                                    </th>

                                @endif


                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        @foreach($students as $record)

                            @php

                                $studentId =
                                    $record->Studentid
                                    ??
                                    $record->student_id
                                    ??
                                    $record->id;


                                $studentMark =
                                    $existingMarks->get(
                                        $studentId
                                    );


                                $isAbsent =
                                    $studentMark &&
                                    (
                                        (int)
                                        $studentMark
                                            ->is_absent
                                        ===
                                        1
                                    );


                                $fatherName =
                                    $record->fathername
                                    ??
                                    $record->father_name
                                    ??
                                    $record->father
                                    ??
                                    '';


                                $studentFullName =
                                    trim(
                                        (
                                            $record
                                                ->studname
                                            ?? ''
                                        )
                                        . ' '
                                        .
                                        $fatherName
                                    );


                                $theoryValue =
                                    $studentMark
                                        ? $studentMark
                                            ->theory_obtained_marks
                                        : null;


                                $oralValue =
                                    $studentMark
                                        ? $studentMark
                                            ->oral_obtained_marks
                                        : null;


                                $practicalValue =
                                    $studentMark
                                        ? $studentMark
                                            ->practical_obtained_marks
                                        : null;


                                $formatIntegerMark =
                                    function ($value) {

                                        if (
                                            $value === null
                                            ||
                                            $value === ''
                                        ) {

                                            return '';

                                        }

                                        return (string)(
                                            (int)
                                            round(
                                                (float)$value
                                            )
                                        );

                                    };

                            @endphp


                            <tr>


                                {{-- GR NO --}}

                                <td class="admin-center">

                                    {{ $record->regno ?? '-' }}

                                    <input
                                        type="hidden"
                                        name="student_ids[]"
                                        value="{{ $studentId }}"
                                    >

                                </td>


                                {{-- ROLL NO --}}

                                <td class="admin-center">

                                    {{ $record->rollno ?? '-' }}

                                </td>


                                {{-- STUDENT NAME --}}

                                <td class="admin-student-name">

                                    <strong>
                                        {{ $studentFullName ?: '-' }}
                                    </strong>

                                </td>


                                {{-- ATTENDANCE --}}

                                <td class="admin-center">

                                    <input
                                        type="hidden"
                                        name="is_absent[{{ $studentId }}]"
                                        id="admin_absent_{{ $studentId }}"
                                        value="{{
                                            $isAbsent
                                                ? 1
                                                : 0
                                        }}"
                                    >


                                    <button
                                        type="button"
                                        id="admin_attendance_btn_{{ $studentId }}"
                                        class="
                                            admin-attendance-btn
                                            {{
                                                $isAbsent
                                                    ? 'admin-absent-btn'
                                                    : 'admin-present-btn'
                                            }}
                                        "
                                        onclick="
                                            toggleAdminAttendance(
                                                '{{ $studentId }}'
                                            )
                                        "
                                    >

                                        {{
                                            $isAbsent
                                                ? 'ABSENT'
                                                : 'PRESENT'
                                        }}

                                    </button>

                                </td>


                                {{-- THEORY --}}

                                @if($showTheory)

                                    <td class="admin-center">
                                        {{ (int)$theoryMaxMarks }}
                                    </td>

                                    <td class="admin-center">
                                        {{ (int)$theoryPassingMarks }}
                                    </td>

                                    <td class="admin-center">

                                        <input
                                            type="text"
                                            name="theory_marks[{{ $studentId }}]"
                                            value="{{
                                                old(
                                                    'theory_marks.'
                                                    . $studentId,

                                                    $formatIntegerMark(
                                                        $theoryValue
                                                    )
                                                )
                                            }}"
                                            inputmode="numeric"
                                            pattern="[0-9]*"
                                            autocomplete="off"
                                            maxlength="4"
                                            data-max="{{
                                                (int)$theoryMaxMarks
                                            }}"
                                            class="
                                                admin-mark-input
                                                admin-mark-input-{{ $studentId }}
                                                {{
                                                    $isAbsent
                                                        ? 'admin-absent-input'
                                                        : ''
                                                }}
                                            "
                                            data-student="{{ $studentId }}"
                                            {{
                                                $isAbsent
                                                    ? 'readonly'
                                                    : ''
                                            }}
                                        >

                                    </td>

                                @endif


                                {{-- ORAL --}}

                                @if($showOral)

                                    <td class="admin-center">
                                        {{ (int)$oralMaxMarks }}
                                    </td>

                                    <td class="admin-center">
                                        {{ (int)$oralPassingMarks }}
                                    </td>

                                    <td class="admin-center">

                                        <input
                                            type="text"
                                            name="oral_marks[{{ $studentId }}]"
                                            value="{{
                                                old(
                                                    'oral_marks.'
                                                    . $studentId,

                                                    $formatIntegerMark(
                                                        $oralValue
                                                    )
                                                )
                                            }}"
                                            inputmode="numeric"
                                            pattern="[0-9]*"
                                            autocomplete="off"
                                            maxlength="4"
                                            data-max="{{
                                                (int)$oralMaxMarks
                                            }}"
                                            class="
                                                admin-mark-input
                                                admin-mark-input-{{ $studentId }}
                                                {{
                                                    $isAbsent
                                                        ? 'admin-absent-input'
                                                        : ''
                                                }}
                                            "
                                            data-student="{{ $studentId }}"
                                            {{
                                                $isAbsent
                                                    ? 'readonly'
                                                    : ''
                                            }}
                                        >

                                    </td>

                                @endif


                                {{-- PRACTICAL --}}

                                @if($showPractical)

                                    <td class="admin-center">
                                        {{ (int)$practicalMaxMarks }}
                                    </td>

                                    <td class="admin-center">
                                        {{ (int)$practicalPassingMarks }}
                                    </td>

                                    <td class="admin-center">

                                        <input
                                            type="text"
                                            name="practical_marks[{{ $studentId }}]"
                                            value="{{
                                                old(
                                                    'practical_marks.'
                                                    . $studentId,

                                                    $formatIntegerMark(
                                                        $practicalValue
                                                    )
                                                )
                                            }}"
                                            inputmode="numeric"
                                            pattern="[0-9]*"
                                            autocomplete="off"
                                            maxlength="4"
                                            data-max="{{
                                                (int)$practicalMaxMarks
                                            }}"
                                            class="
                                                admin-mark-input
                                                admin-mark-input-{{ $studentId }}
                                                {{
                                                    $isAbsent
                                                        ? 'admin-absent-input'
                                                        : ''
                                                }}
                                            "
                                            data-student="{{ $studentId }}"
                                            {{
                                                $isAbsent
                                                    ? 'readonly'
                                                    : ''
                                            }}
                                        >

                                    </td>

                                @endif


                                {{-- STATUS --}}

                                <td class="admin-center">

                                    <span
                                        id="admin_status_{{ $studentId }}"
                                        class="
                                            {{
                                                $isAbsent
                                                    ? 'admin-status-absent'
                                                    : 'admin-status-present'
                                            }}
                                        "
                                    >

                                        {{
                                            $isAbsent
                                                ? 'ABSENT'
                                                : 'PRESENT'
                                        }}

                                    </span>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- ==================================================
                     UPDATE BUTTON
                =================================================== --}}

                <div class="admin-action-row">

                    <button
                        type="submit"
                        class="admin-erp-btn admin-btn-blue"
                        id="adminUpdateMarksButton"
                    >
                        Update Marks
                    </button>


                    <span class="admin-student-count">

                        {{ $studentCount }}
                        Students

                    </span>


                    @if($existingMarks->count() === 0)

                        <span class="admin-action-note">

                            No saved marks existed.
                            Update Marks will create the mark records.

                        </span>

                    @else

                        <span class="admin-action-note">

                            Existing marks can be corrected by Administrator.

                        </span>

                    @endif

                </div>

            </form>


            {{-- ==================================================
                 ADMINISTRATOR NOTE
            =================================================== --}}

            <div
                class="admin-info-box admin-warning-box"
                style="
                    margin-top:12px;
                    margin-bottom:0;
                "
            >

                <strong>
                    Administrator Access:
                </strong>

                Marks can be entered or corrected for

                <strong>PENDING</strong> and
                <strong>COMPLETED</strong> assignments.

                Administrator changes do not change the current
                teaching assignment status.

            </div>

        </div>


    @elseif(
        $teacherSubjectAllocation &&
        $studentCount === 0
    )

        <div
            class="erp-card"
            style="
                margin-top:12px;
                padding:12px;
                background:#fef2f2;
                border:1px solid #ef4444;
                color:#991b1b;
            "
        >

            No students were found for the selected class/division
            in the Old ERP student source.

        </div>

    @endif

</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        /*
        |--------------------------------------------------------------------------
        | FILTER ELEMENTS
        |--------------------------------------------------------------------------
        */

        const academicYear =
            document.getElementById(
                'admin_academic_year_id'
            );


        const exam =
            document.getElementById(
                'admin_exam_master_id'
            );


        const assignment =
            document.getElementById(
                'admin_teacher_subject_allocation_id'
            );


        const loadButton =
            document.getElementById(
                'adminLoadMarksButton'
            );


        /*
        |--------------------------------------------------------------------------
        | LOAD BUTTON
        |--------------------------------------------------------------------------
        */

        function updateLoadButton()
        {

            if (!loadButton) {
                return;
            }


            loadButton.disabled =
                !(
                    exam &&
                    exam.value &&
                    assignment &&
                    assignment.value
                );

        }


        /*
        |--------------------------------------------------------------------------
        | ORIGINAL ASSIGNMENT OPTIONS
        |--------------------------------------------------------------------------
        */

        const originalAssignments =
            assignment
                ? Array.from(
                    assignment.options
                ).map(
                    function(option) {

                        return {

                            value:
                                option.value,

                            text:
                                option.textContent.trim(),

                            academicYearId:
                                option.dataset
                                    .academicYearId
                                || '',

                            examId:
                                option.dataset
                                    .examId
                                || ''

                        };

                    }
                )
                : [];


        /*
        |--------------------------------------------------------------------------
        | FILTER ASSIGNMENTS
        |--------------------------------------------------------------------------
        */

        function filterAssignments()
        {

            if (!assignment) {
                return;
            }


            const selectedYear =
                academicYear
                    ? academicYear.value
                    : '';


            const selectedExam =
                exam
                    ? exam.value
                    : '';


            const currentAssignment =
                assignment.value;


            assignment.innerHTML =
                '<option value="">Select Teaching Assignment</option>';


            if (!selectedExam) {

                assignment.disabled =
                    true;

                updateLoadButton();

                return;
            }


            let count =
                0;


            originalAssignments.forEach(
                function(item) {

                    if (!item.value) {
                        return;
                    }


                    const examMatch =
                        String(
                            item.examId
                        ) ===
                        String(
                            selectedExam
                        );


                    const yearMatch =
                        !selectedYear
                        ||
                        String(
                            item.academicYearId
                        ) ===
                        String(
                            selectedYear
                        );


                    if (
                        examMatch &&
                        yearMatch
                    ) {

                        const option =
                            document.createElement(
                                'option'
                            );


                        option.value =
                            item.value;


                        option.textContent =
                            item.text;


                        option.dataset
                            .academicYearId =
                                item.academicYearId;


                        option.dataset
                            .examId =
                                item.examId;


                        if (
                            String(
                                item.value
                            ) ===
                            String(
                                currentAssignment
                            )
                        ) {

                            option.selected =
                                true;

                        }


                        assignment.appendChild(
                            option
                        );


                        count++;
                    }

                }
            );


            assignment.disabled =
                count === 0;


            if (
                count === 0
            ) {

                assignment.innerHTML =
                    '<option value="">No Teaching Assignment</option>';

            }


            updateLoadButton();

        }


        /*
        |--------------------------------------------------------------------------
        | ACADEMIC YEAR CHANGE
        |--------------------------------------------------------------------------
        */

        if (academicYear) {

            academicYear.addEventListener(
                'change',
                function() {

                    if (
                        exam &&
                        exam.value
                    ) {

                        document
                            .getElementById(
                                'adminMarksFilterForm'
                            )
                            .submit();

                    } else {

                        if (assignment) {

                            assignment.value =
                                '';

                            assignment.disabled =
                                true;

                        }

                        updateLoadButton();

                    }

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | EXAM CHANGE
        |--------------------------------------------------------------------------
        */

        if (exam) {

            exam.addEventListener(
                'change',
                function() {

                    document
                        .getElementById(
                            'adminMarksFilterForm'
                        )
                        .submit();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | ASSIGNMENT CHANGE
        |--------------------------------------------------------------------------
        */

        if (assignment) {

            assignment.addEventListener(
                'change',
                function() {

                    updateLoadButton();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | INITIAL STATE
        |--------------------------------------------------------------------------
        */

        if (
            exam &&
            exam.value
        ) {

            filterAssignments();

        } else if (assignment) {

            assignment.disabled =
                true;

            updateLoadButton();

        }


        /*
        |--------------------------------------------------------------------------
        | INTEGER-ONLY MARK INPUT
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.admin-mark-input'
            )
            .forEach(
                function(input) {


                    /*
                    |----------------------------------------------------------------------
                    | TYPING
                    |----------------------------------------------------------------------
                    */

                    input.addEventListener(
                        'input',
                        function() {

                            this.value =
                                this.value.replace(
                                    /[^0-9]/g,
                                    ''
                                );


                            const max =
                                parseInt(
                                    this.dataset.max
                                    || '0',
                                    10
                                );


                            if (
                                this.value !== ''
                                &&
                                max > 0
                                &&
                                parseInt(
                                    this.value,
                                    10
                                ) > max
                            ) {

                                this.value =
                                    String(max);

                            }


                            if (
                                this.value === ''
                            ) {

                                this.style.border =
                                    '1px solid #9ca3af';

                                return;

                            }


                            this.style.border =
                                '1px solid #16a34a';

                        }
                    );


                    /*
                    |----------------------------------------------------------------------
                    | KEYBOARD
                    |----------------------------------------------------------------------
                    */

                    input.addEventListener(
                        'keydown',
                        function(event) {

                            const allowedKeys = [

                                'Backspace',
                                'Delete',
                                'Tab',
                                'ArrowLeft',
                                'ArrowRight',
                                'Home',
                                'End'

                            ];


                            if (
                                allowedKeys.includes(
                                    event.key
                                )
                            ) {

                                return;

                            }


                            if (
                                !/^[0-9]$/.test(
                                    event.key
                                )
                            ) {

                                event.preventDefault();

                            }

                        }
                    );


                    /*
                    |----------------------------------------------------------------------
                    | PASTE
                    |----------------------------------------------------------------------
                    */

                    input.addEventListener(
                        'paste',
                        function(event) {

                            event.preventDefault();


                            const pastedText =
                                (
                                    event.clipboardData
                                    ||
                                    window.clipboardData
                                )
                                .getData('text');


                            const cleaned =
                                pastedText.replace(
                                    /[^0-9]/g,
                                    ''
                                );


                            this.value =
                                cleaned;


                            this.dispatchEvent(
                                new Event(
                                    'input',
                                    {
                                        bubbles: true
                                    }
                                )
                            );

                        }
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | UPDATE FORM
        |--------------------------------------------------------------------------
        */

        const adminMarksForm =
            document.getElementById(
                'adminMarksForm'
            );


        if (
            adminMarksForm
        ) {

            adminMarksForm.addEventListener(
                'submit',
                function(event) {

                    event.preventDefault();


                    let hasError =
                        false;


                    document
                        .querySelectorAll(
                            '.admin-mark-input'
                        )
                        .forEach(
                            function(input) {

                                if (
                                    input.readOnly
                                ) {

                                    return;

                                }


                                const value =
                                    input.value.trim();


                                if (
                                    value === ''
                                ) {

                                    return;

                                }


                                if (
                                    !/^\d+$/.test(
                                        value
                                    )
                                ) {

                                    hasError =
                                        true;

                                    input.style.border =
                                        '2px solid #dc2626';

                                    return;

                                }


                                const max =
                                    parseInt(
                                        input.dataset.max
                                        || '0',
                                        10
                                    );


                                const numericValue =
                                    parseInt(
                                        value,
                                        10
                                    );


                                if (
                                    max > 0
                                    &&
                                    numericValue > max
                                ) {

                                    hasError =
                                        true;

                                    input.style.border =
                                        '2px solid #dc2626';

                                }

                            }
                        );


                    if (
                        hasError
                    ) {

                        Swal.fire({

                            icon:
                                'error',

                            title:
                                'Validation Error',

                            text:
                                'Please enter valid whole-number marks only.'

                        });

                        return;

                    }


                    Swal.fire({

                        icon:
                            'warning',

                        title:
                            'Update Marks',

                        text:
                            'Are you sure you want to update these marks?',

                        showCancelButton:
                            true,

                        confirmButtonText:
                            'Yes, Update Marks',

                        cancelButtonText:
                            'Cancel'

                    }).then(
                        function(result) {

                            if (
                                result.isConfirmed
                            ) {

                                adminMarksForm.submit();

                            }

                        }
                    );

                }
            );

        }

    }
);


/*
|--------------------------------------------------------------------------
| PRESENT / ABSENT
|--------------------------------------------------------------------------
*/

function toggleAdminAttendance(
    studentId
) {

    const hidden =
        document.getElementById(
            'admin_absent_' + studentId
        );


    const button =
        document.getElementById(
            'admin_attendance_btn_' + studentId
        );


    const status =
        document.getElementById(
            'admin_status_' + studentId
        );


    const inputs =
        document.querySelectorAll(
            '.admin-mark-input-' + studentId
        );


    if (
        !hidden ||
        !button ||
        !status
    ) {

        return;

    }


    const currentlyAbsent =
        hidden.value === '1';


    /*
    |--------------------------------------------------------------------------
    | ABSENT -> PRESENT
    |--------------------------------------------------------------------------
    */

    if (
        currentlyAbsent
    ) {

        hidden.value =
            '0';


        button.textContent =
            'PRESENT';


        button.classList.remove(
            'admin-absent-btn'
        );


        button.classList.add(
            'admin-present-btn'
        );


        status.textContent =
            'PRESENT';


        status.classList.remove(
            'admin-status-absent'
        );


        status.classList.add(
            'admin-status-present'
        );


        inputs.forEach(
            function(input) {

                input.readOnly =
                    false;


                input.classList.remove(
                    'admin-absent-input'
                );

            }
        );


        return;
    }


    /*
    |--------------------------------------------------------------------------
    | PRESENT -> ABSENT
    |--------------------------------------------------------------------------
    */

    hidden.value =
        '1';


    button.textContent =
        'ABSENT';


    button.classList.remove(
        'admin-present-btn'
    );


    button.classList.add(
        'admin-absent-btn'
    );


    status.textContent =
        'ABSENT';


    status.classList.remove(
        'admin-status-present'
    );


    status.classList.add(
        'admin-status-absent'
    );


    inputs.forEach(
        function(input) {

            input.value =
                '0';


            input.readOnly =
                true;


            input.classList.add(
                'admin-absent-input'
            );

        }
    );

}

</script>


</x-app-layout>