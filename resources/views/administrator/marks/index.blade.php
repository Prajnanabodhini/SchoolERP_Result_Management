<x-app-layout>

<style>
/* =========================================================
   PAGE
========================================================= */
.admin-marks-page,
.admin-marks-page * {
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif !important;
}

.admin-marks-page {
    background: #f0f0f0;
    padding: 20px;
}

/* =========================================================
   CARD
========================================================= */
.admin-marks-card-outer {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    padding: 18px 20px;
    max-width: 100%;
}

.admin-marks-page h2 {
    margin: 0 0 14px;
    font-size: 21px !important;
    font-weight: 700 !important;
    color: #1D4ED8 !important;
    letter-spacing: 0.4px;
    text-transform: none;
}

/* =========================================================
   MESSAGES
   ========================================================= */
.admin-info-box {
    margin-top: 14px;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.5;
}

.admin-error-box {
    background: #fee2e2;
    border: 1px solid #fca5a5;
    color: #991b1b;
}
.admin-success-box {
    background: #dcfce7;
    border: 1px solid #86efac;
    color: #166534;
}
.admin-error-box ul {
    margin: 0;
    padding-left: 20px;
}
.admin-info-box:first-of-type {
    margin-top: 0;
}

/* =========================================================
   FILTER ROW
========================================================= */
.admin-filter-row {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 6px;
}

.admin-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
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
    padding: 4px 28px 4px 10px;
    border: 1px solid #9CA3AF;
    border-radius: 6px;
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
    border-color: #2563EB;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

.admin-filter-select:disabled {
    background: #F3F4F6;
    color: #6B7280;
    cursor: not-allowed;
}

.admin-dropdown-arrow {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 6px solid #6b7280;
    pointer-events: none;
}

/* Widths */
.admin-select-year       { width: 170px; }
.admin-select-exam       { width: 230px; }
.admin-select-teacher    { min-width: 270px; max-width: 330px; }
.admin-select-subject    { min-width: 210px; max-width: 270px; }

/* =========================================================
   BUTTONS
========================================================= */
.admin-filter-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 0 0 auto;
}

.admin-erp-btn {
    height: 32px;
    min-width: 140px;
    padding: 0 16px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s, border-color .15s;
    border: 1px solid transparent;
}

.admin-btn-blue {
    background: #16A34A;
    color: #ffffff;
    border-color: #15803D;
}
.admin-btn-blue:hover {
    background: #15803D;
    border-color: #166534;
}

.admin-btn-gray {
    background: #6B7280;
    color: #ffffff;
    border-color: #4B5563;
}
.admin-btn-gray:hover {
    background: #4B5563;
    border-color: #374151;
}

.admin-erp-btn:disabled {
    opacity: .65;
    cursor: not-allowed;
}

/* =========================================================
   RESPONSIVE
========================================================= */
@media (max-width: 900px) {
    .admin-marks-page { padding: 12px; }
    .admin-marks-card-outer { padding: 14px; }

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
        min-width: 0;
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