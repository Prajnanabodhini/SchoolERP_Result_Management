<x-app-layout>

<style>

.admin-marks-page,
.admin-marks-page * {
    font-family: Arial, sans-serif !important;
    font-size: 13px !important;
}

.admin-marks-page h2 {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #1d4ed8 !important;
}

.admin-marks-page label {
    font-size: 13px !important;
    font-weight: 600 !important;
}

.admin-marks-page select {
    height: 36px !important;
    min-width: 180px;
    padding: 5px 8px !important;
    font-size: 13px !important;
}

.admin-marks-page .erp-btn {
    font-size: 13px !important;
    padding: 6px 14px !important;
}

.filter-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

.subject-loading {
    color: #6b7280;
    font-style: italic;
}

.subject-error {
    color: #dc2626;
    font-weight: 600;
}

</style>


<div class="erp-page admin-marks-page">

    <div class="erp-card">

        {{-- =====================================================
             PAGE TITLE
        ====================================================== --}}

        <h2 style="
            margin-bottom:15px;
        ">
            EDIT EXAMINATION MARKS
        </h2>


        {{-- =====================================================
             VALIDATION ERRORS
        ====================================================== --}}

        @if ($errors->any())

            <div style="
                background:#FEE2E2;
                border:1px solid #EF4444;
                color:#B91C1C;
                padding:10px;
                border-radius:5px;
                margin-bottom:15px;
                font-weight:600;
            ">

                <ul style="margin:0;padding-left:20px;">

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- =====================================================
             SUCCESS
        ====================================================== --}}

        @if(session('success'))

            <div style="
                background:#DCFCE7;
                border:1px solid #22C55E;
                color:#15803D;
                padding:10px;
                border-radius:5px;
                margin-bottom:15px;
                font-weight:600;
            ">

                {{ session('success') }}

            </div>

        @endif


        {{-- =====================================================
             ERROR
        ====================================================== --}}

        @if(session('error'))

            <div style="
                background:#FEE2E2;
                border:1px solid #EF4444;
                color:#B91C1C;
                padding:10px;
                border-radius:5px;
                margin-bottom:15px;
                font-weight:600;
            ">

                {{ session('error') }}

            </div>

        @endif


        {{-- =====================================================
             SEARCH FORM
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('result-generation.admin-marks.edit') }}"
            id="marksSearchForm"
        >

            <div class="filter-row">


                {{-- =================================================
                     ACADEMIC YEAR
                ================================================== --}}

                <div class="filter-group">

                    <label for="academic_year_id">
                        Academic Year
                    </label>

                    <select
                        name="academic_year_id"
                        id="academic_year_id"
                        class="erp-filter-select"
                        required
                    >

                        <option value="">
                            Select Academic Year
                        </option>

                        @foreach($academicYears as $year)

                            <option
                                value="{{ $year->id }}"
                                {{ request('academic_year_id') == $year->id ? 'selected' : '' }}
                            >
                                {{ $year->year_name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =================================================
                     EXAM
                ================================================== --}}

                <div class="filter-group">

                    <label for="exam_master_id">
                        Exam
                    </label>

                    <select
                        name="exam_master_id"
                        id="exam_master_id"
                        class="erp-filter-select"
                        required
                    >

                        <option value="">
                            Select Exam
                        </option>

                        @foreach($exams as $exam)

                            <option
                                value="{{ $exam->id }}"
                                {{ request('exam_master_id') == $exam->id ? 'selected' : '' }}
                            >

                                {{ $exam->exam_name }}

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =================================================
                     TEACHER CLASS
                ================================================== --}}

                <div class="filter-group">

                    <label for="allocation">
                        Teacher Class
                    </label>

                    <select
                        id="allocation"
                        name="teacher_class_allocation_id"
                        class="erp-filter-select"
                        required
                    >

                        <option value="">
                            Select Teacher Class
                        </option>

                        @foreach($classAllocations as $row)

                            <option
                                value="{{ $row->id }}"
                                {{ request('teacher_class_allocation_id') == $row->id ? 'selected' : '' }}
                            >

                                {{ $row->teacher->name ?? 'Teacher' }}

                                -

                                {{ $row->standard->standard_name ?? 'Standard' }}

                                -

                                {{ $row->division->division_name ?? 'Division' }}

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =================================================
                     SUBJECT
                ================================================== --}}

                <div class="filter-group">

                    <label for="subject">
                        Subject
                    </label>

                    <select
                        id="subject"
                        name="subject_id"
                        class="erp-filter-select"
                        required
                    >

                        <option value="">
                            Select Subject
                        </option>

                    </select>

                </div>


                {{-- =================================================
                     LOAD BUTTON
                ================================================== --}}

                <button
                    type="submit"
                    class="erp-btn erp-btn-save"
                    id="loadMarksBtn"
                >
                    Load Marks
                </button>


                {{-- =================================================
                     RESET
                ================================================== --}}

                <a
                    href="{{ route('result-generation.admin-marks.edit') }}"
                    class="erp-btn erp-btn-cancel"
                >
                    Reset
                </a>

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