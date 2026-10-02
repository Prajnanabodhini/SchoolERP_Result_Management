<x-app-layout>

<style>
/* =========================================================
   PAGE
========================================================= */
.admin-marks-page,
.admin-marks-page * {
    box-sizing: border-box;
    font-family: Arial, sans-serif !important;
}

.admin-marks-page h2 {
    margin: 0 0 12px;
    font-size: 18px !important;
    font-weight: 700 !important;
    color: #1d4ed8 !important;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* =========================================================
   CARD
========================================================= */
.admin-marks-card-outer {
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 14px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* =========================================================
   MESSAGES
========================================================= */
.admin-info-box {
    margin-bottom: 10px;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}
.admin-error-box {
    background: #fef2f2;
    border: 1px solid #ef4444;
    color: #991b1b;
}
.admin-success-box {
    background: #ecfdf5;
    border: 1px solid #10b981;
    color: #065f46;
}
.admin-error-box ul {
    margin: 0;
    padding-left: 18px;
}

/* =========================================================
   FILTER ROW
========================================================= */
.admin-filter-row {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 10px;
}

.admin-filter-group {
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 0 0 auto;
}

.admin-filter-label {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.admin-filter-wrapper {
    position: relative;
    display: inline-block;
}

.admin-filter-select {
    height: 32px;
    padding: 4px 26px 4px 10px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    background: #ffffff;
    color: #111827;
    font-size: 12px;
    font-weight: 600;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
}

.admin-filter-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

.admin-filter-select:disabled {
    background: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
}

.admin-dropdown-arrow {
    position: absolute;
    right: 9px;
    top: 50%;
    transform: translateY(-50%);
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 5px solid #6b7280;
    pointer-events: none;
}

/* Widths */
.admin-select-year       { width: 160px; }
.admin-select-exam       { width: 220px; }
.admin-select-teacher    { min-width: 260px; max-width: 320px; }
.admin-select-subject    { min-width: 200px; max-width: 260px; }

/* =========================================================
   BUTTONS
========================================================= */
.admin-filter-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
}

.admin-erp-btn {
    height: 32px;
    padding: 4px 16px;
    border: 0;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s;
    border: 1px solid transparent;
}

.admin-btn-blue {
    background: #2563eb;
    color: #ffffff;
    border-color: #1d4ed8;
}
.admin-btn-blue:hover { background: #1d4ed8; }

.admin-btn-gray {
    background: #6b7280;
    color: #ffffff;
    border-color: #4b5563;
}
.admin-btn-gray:hover { background: #4b5563; }

.admin-erp-btn:disabled {
    opacity: .65;
    cursor: not-allowed;
}

/* =========================================================
   RESPONSIVE
========================================================= */
@media (max-width: 900px) {
    .admin-filter-row {
        flex-direction: column;
        align-items: stretch;
    }

    .admin-filter-group,
    .admin-select-year,
    .admin-select-exam,
    .admin-select-teacher,
    .admin-select-subject {
        width: 100% !important;
        min-width: 0 !important;
        max-width: none !important;
    }

    .admin-filter-actions {
        width: 100%;
    }

    .admin-filter-actions .admin-erp-btn {
        flex: 1;
    }
}
</style>


<div class="erp-page admin-marks-page">

    <div class="admin-marks-card-outer">

        {{-- =====================================================
             PAGE TITLE
        ====================================================== --}}
        <h2>Edit Examination Marks</h2>


        {{-- =====================================================
             VALIDATION ERRORS
        ====================================================== --}}
        @if ($errors->any())
            <div class="admin-info-box admin-error-box">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- =====================================================
             SUCCESS
        ====================================================== --}}
        @if(session('success'))
            <div class="admin-info-box admin-success-box">
                {{ session('success') }}
            </div>
        @endif


        {{-- =====================================================
             ERROR
        ====================================================== --}}
        @if(session('error'))
            <div class="admin-info-box admin-error-box">
                {{ session('error') }}
            </div>
        @endif


        {{-- =====================================================
             SEARCH FORM
        ====================================================== --}}
        <form method="GET"
              action="{{ route('result-generation.admin-marks.edit') }}"
              id="marksSearchForm">

            <div class="admin-filter-row">

                {{-- Academic Year --}}
                <div class="admin-filter-group">
                    <label for="academic_year_id" class="admin-filter-label">
                        Academic Year
                    </label>
                    <div class="admin-filter-wrapper">
                        <select name="academic_year_id"
                                id="academic_year_id"
                                class="admin-filter-select admin-select-year"
                                required>
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->year_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="admin-dropdown-arrow"></span>
                    </div>
                </div>


                {{-- Exam --}}
                <div class="admin-filter-group">
                    <label for="exam_master_id" class="admin-filter-label">
                        Exam
                    </label>
                    <div class="admin-filter-wrapper">
                        <select name="exam_master_id"
                                id="exam_master_id"
                                class="admin-filter-select admin-select-exam"
                                required>
                            <option value="">Select Exam</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}"
                                    {{ request('exam_master_id') == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="admin-dropdown-arrow"></span>
                    </div>
                </div>


                {{-- Teacher Class --}}
                <div class="admin-filter-group">
                    <label for="allocation" class="admin-filter-label">
                        Teacher Class
                    </label>
                    <div class="admin-filter-wrapper">
                        <select id="allocation"
                                name="teacher_class_allocation_id"
                                class="admin-filter-select admin-select-teacher"
                                required>
                            <option value="">Select Teacher Class</option>
                            @foreach($classAllocations as $row)
                                <option value="{{ $row->id }}"
                                    {{ request('teacher_class_allocation_id') == $row->id ? 'selected' : '' }}>
                                    {{ $row->teacher->name ?? 'Teacher' }}
                                    -
                                    {{ $row->standard->standard_name ?? 'Standard' }}
                                    -
                                    {{ $row->division->division_name ?? 'Division' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="admin-dropdown-arrow"></span>
                    </div>
                </div>


                {{-- Subject --}}
                <div class="admin-filter-group">
                    <label for="subject" class="admin-filter-label">
                        Subject
                    </label>
                    <div class="admin-filter-wrapper">
                        <select id="subject"
                                name="subject_id"
                                class="admin-filter-select admin-select-subject"
                                required>
                            <option value="">Select Subject</option>
                        </select>
                        <span class="admin-dropdown-arrow"></span>
                    </div>
                </div>


                {{-- Buttons --}}
                <div class="admin-filter-actions">
                    <button type="submit"
                            class="admin-erp-btn admin-btn-blue"
                            id="loadMarksBtn">
                        Load Marks
                    </button>

                    <a href="{{ route('result-generation.admin-marks.edit') }}"
                       class="admin-erp-btn admin-btn-gray">
                        Reset
                    </a>
                </div>

            </div>

        </form>

    </div>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const allocation =
        document.getElementById('allocation');

    const subject =
        document.getElementById('subject');

    const academicYear =
        document.getElementById('academic_year_id');

    const exam =
        document.getElementById('exam_master_id');

    const oldSubjectId =
        @json(request('subject_id'));


    /*
    |--------------------------------------------------------------------------
    | LOAD SUBJECTS
    |--------------------------------------------------------------------------
    */

    function loadSubjects(allocationId, selectedSubjectId = null)
    {

        subject.innerHTML = `
            <option value="">
                Loading subjects...
            </option>
        `;

        subject.disabled = true;


        if (!allocationId) {

            subject.innerHTML = `
                <option value="">
                    Select Subject
                </option>
            `;

            subject.disabled = false;

            return;
        }


        fetch(
            "{{ route('admin-marks.subjects') }}"
            + "?allocation_id="
            + encodeURIComponent(allocationId)
        )

        .then(function(response) {

            if (!response.ok) {

                throw new Error(
                    'Unable to load subjects.'
                );

            }

            return response.json();

        })

        .then(function(data) {

            subject.innerHTML = `
                <option value="">
                    Select Subject
                </option>
            `;


            if (!Array.isArray(data) || data.length === 0) {

                subject.innerHTML = `
                    <option value="">
                        No Subjects Found
                    </option>
                `;

                subject.disabled = false;

                return;
            }


            data.forEach(function(item) {

                const option =
                    document.createElement('option');

                option.value =
                    item.id;

                option.textContent =
                    item.subject_name;


                if (
                    selectedSubjectId &&
                    String(item.id) ===
                    String(selectedSubjectId)
                ) {

                    option.selected = true;

                }


                subject.appendChild(option);

            });


            subject.disabled = false;

        })

        .catch(function(error) {

            console.error(
                'Subject loading error:',
                error
            );


            subject.innerHTML = `
                <option value="">
                    Unable to load subjects
                </option>
            `;

            subject.disabled = false;

        });

    }


    /*
    |--------------------------------------------------------------------------
    | TEACHER CLASS CHANGE
    |--------------------------------------------------------------------------
    */

    allocation.addEventListener(
        'change',
        function () {

            loadSubjects(
                this.value
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | INITIAL PAGE LOAD
    |--------------------------------------------------------------------------
    |
    | If the page was submitted with filters already selected,
    | reload the subject list and preserve the selected subject.
    |
    */

    if (allocation.value) {

        loadSubjects(
            allocation.value,
            oldSubjectId
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FORM VALIDATION
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('marksSearchForm')
        .addEventListener('submit', function(event) {

            if (!academicYear.value) {

                alert(
                    'Please select Academic Year.'
                );

                event.preventDefault();

                return;
            }


            if (!exam.value) {

                alert(
                    'Please select Exam.'
                );

                event.preventDefault();

                return;
            }


            if (!allocation.value) {

                alert(
                    'Please select Teacher Class.'
                );

                event.preventDefault();

                return;
            }


            if (!subject.value) {

                alert(
                    'Please select Subject.'
                );

                event.preventDefault();

                return;
            }

        });

});

</script>

</x-app-layout>