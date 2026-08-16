<x-app-layout>

    {{-- =========================================================
         VALIDATION ERRORS
    ========================================================== --}}

    @if ($errors->any())
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif


    <div style="
        max-width:1200px;
        margin:auto;
        padding:15px;
    ">

        <div style="
            background:white;
            border-radius:10px;
            padding:18px;
            border:1px solid #d1d5db;
            box-shadow:0 3px 8px rgba(0,0,0,.12);
        ">

            {{-- =====================================================
                 TITLE
            ====================================================== --}}

            <h2 style="
                text-align:center;
                color:#16a34a;
                font-size:22px;
                font-weight:bold;
                margin-bottom:18px;
            ">
                Teacher Bulk Allocation
            </h2>


            {{-- =====================================================
                 SUCCESS MESSAGE
            ====================================================== --}}

            @if(session('success'))
                <div style="
                    background:#dcfce7;
                    color:#166534;
                    padding:9px 12px;
                    margin-bottom:12px;
                    border-radius:5px;
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
                    padding:9px 12px;
                    margin-bottom:12px;
                    border-radius:5px;
                ">
                    {{ session('error') }}
                </div>
            @endif


            {{-- =====================================================
                 FORM
            ====================================================== --}}

            <form
                id="allocationForm"
                method="POST"
                action="{{ route('teacher-bulk-allocation.store') }}"
            >

                @csrf


                {{-- =================================================
                     TEACHER | ACADEMIC YEAR | EXAM
                ================================================== --}}

                <div style="
                    display:grid;
                    grid-template-columns:1fr 1fr 1fr;
                    gap:12px;
                    margin-bottom:15px;
                ">


                    {{-- TEACHER --}}

                    <div>

                        <label style="
                            display:block;
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                            Teacher
                        </label>

                        <select
                            name="user_id"
                            id="user_id"
                            class="form-select"
                            style="width:100%;"
                            required
                        >

                            <option value="">
                                Select Teacher
                            </option>

                            @foreach($teachers as $teacher)

                                <option
                                    value="{{ $teacher->id }}"
                                    {{ old('user_id') == $teacher->id ? 'selected' : '' }}
                                >
                                    {{ $teacher->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- ACADEMIC YEAR --}}

                    <div>

                        <label style="
                            display:block;
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                            Academic Year
                        </label>

                        <select
                            name="academic_year_id"
                            id="academic_year_id"
                            class="form-select"
                            style="width:100%;"
                            required
                        >

                            <option value="">
                                Select Academic Year
                            </option>

                            @foreach($academicYears as $year)

                                <option
                                    value="{{ $year->id }}"
                                    {{ old('academic_year_id') == $year->id ? 'selected' : '' }}
                                >
                                    {{ $year->year_name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- EXAM --}}

                    <div>

                        <label style="
                            display:block;
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                            Exam
                        </label>

                        <select
                            name="exam_master_id"
                            id="exam_master_id"
                            class="form-select"
                            style="width:100%;"
                            required
                        >

                            <option value="">
                                Select Exam
                            </option>

                            @foreach($exams as $exam)

                                <option
                                    value="{{ $exam->id }}"
                                    data-standard-id="{{ $exam->standard_id }}"
                                    data-standard-name="{{ $exam->standard->standard_name ?? '' }}"
                                    data-section-id="{{ $exam->standard->section_id ?? '' }}"
                                    {{ old('exam_master_id') == $exam->id ? 'selected' : '' }}
                                >
                                    {{ $exam->exam_name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>


                {{-- =================================================
                     STANDARD / DIVISIONS / SUBJECTS
                ================================================== --}}

                <div style="
                    border:1px solid #d1d5db;
                    border-radius:7px;
                    padding:12px;
                    background:#f9fafb;
                ">


                    {{-- STANDARD + DIVISIONS --}}

                    <div style="
                        display:grid;
                        grid-template-columns:260px 1fr;
                        gap:15px;
                        align-items:start;
                    ">


                        {{-- STANDARD --}}

                        <div>

                            <label style="
                                display:block;
                                font-weight:bold;
                                margin-bottom:5px;
                            ">
                                Standard
                            </label>

                            <input
                                type="text"
                                id="standard_name"
                                value=""
                                readonly
                                placeholder="Select Exam First"
                                style="
                                    width:100%;
                                    height:38px;
                                    padding:7px 10px;
                                    border:1px solid #d1d5db;
                                    border-radius:5px;
                                    background:#e5e7eb;
                                    color:#374151;
                                    font-weight:bold;
                                "
                            >

                            {{-- Standard ID sent to store() --}}

                            <input
                                type="hidden"
                                name="rows[0][standards][]"
                                id="standard_id"
                                value=""
                            >

                            {{-- Section ID --}}

                            <input
                                type="hidden"
                                name="rows[0][section_id]"
                                id="section_id"
                                value=""
                            >

                        </div>


                        {{-- DIVISIONS --}}

                        <div>

                            <label style="
                                display:block;
                                font-weight:bold;
                                margin-bottom:5px;
                            ">
                                Divisions
                            </label>

                            <div
                                id="divisions-container"
                                style="
                                    min-height:38px;
                                    padding:8px 10px;
                                    border:1px solid #d1d5db;
                                    background:white;
                                    border-radius:5px;
                                "
                            >
                                Select Exam First
                            </div>

                        </div>

                    </div>


                    {{-- SUBJECTS --}}

                    <div style="margin-top:12px;">

                        <label style="
                            display:block;
                            font-weight:bold;
                            margin-bottom:5px;
                        ">
                            Subjects
                        </label>

                        <div
                            id="subjects-container"
                            style="
                                min-height:42px;
                                padding:8px 10px;
                                border:1px solid #d1d5db;
                                background:white;
                                border-radius:5px;
                            "
                        >
                            Select Exam First
                        </div>

                    </div>

                </div>


                {{-- =================================================
                     BUTTONS
                ================================================== --}}

                <div style="
                    margin-top:15px;
                    display:flex;
                    gap:8px;
                ">

                    <button
                        type="submit"
                        id="saveButton"
                        style="
                            background:#16a34a;
                            color:white;
                            border:none;
                            padding:8px 22px;
                            border-radius:5px;
                            cursor:pointer;
                            font-weight:bold;
                        "
                    >
                        Save Allocation
                    </button>


                    <a
                        href="{{ route('teacher-bulk-allocation.index') }}"
                        style="
                            background:#6b7280;
                            color:white;
                            padding:8px 22px;
                            text-decoration:none;
                            border-radius:5px;
                        "
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>


    {{-- =============================================================
         SWEETALERT
    ============================================================= --}}

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const exam =
                document.getElementById('exam_master_id');

            const standardName =
                document.getElementById('standard_name');

            const standardId =
                document.getElementById('standard_id');

            const sectionId =
                document.getElementById('section_id');

            const divisionsContainer =
                document.getElementById('divisions-container');

            const subjectsContainer =
                document.getElementById('subjects-container');

            const allocationForm =
                document.getElementById('allocationForm');


            /*
            |--------------------------------------------------------------------------
            | DIVISIONS
            |--------------------------------------------------------------------------
            */

            const divisions =
                @json($divisions);


            /*
            |--------------------------------------------------------------------------
            | RESET
            |--------------------------------------------------------------------------
            */

            function resetFields()
            {
                standardName.value = '';
                standardId.value = '';
                sectionId.value = '';

                divisionsContainer.innerHTML =
                    'Select Exam First';

                subjectsContainer.innerHTML =
                    'Select Exam First';
            }


            /*
            |--------------------------------------------------------------------------
            | LOAD DIVISIONS
            |--------------------------------------------------------------------------
            */

            function loadDivisions()
            {
                divisionsContainer.innerHTML = '';

                if (
                    !Array.isArray(divisions) ||
                    divisions.length === 0
                ) {

                    divisionsContainer.innerHTML = `
                        <span style="color:red;">
                            No divisions found.
                        </span>
                    `;

                    return;
                }


                let html = '';


                divisions.forEach(function (division) {

                    html += `
                        <label style="
                            display:inline-flex;
                            align-items:center;
                            gap:5px;
                            margin-right:15px;
                            margin-bottom:5px;
                            white-space:nowrap;
                            cursor:pointer;
                        ">

                            <input
                                type="checkbox"
                                name="rows[0][divisions][]"
                                value="${division.id}"
                            >

                            <span>
                                ${escapeHtml(division.division_name)}
                            </span>

                        </label>
                    `;
                });


                divisionsContainer.innerHTML =
                    html;
            }


            /*
            |--------------------------------------------------------------------------
            | ESCAPE HTML
            |--------------------------------------------------------------------------
            */

            function escapeHtml(value)
            {
                if (value === null || value === undefined) {
                    return '';
                }

                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }


            /*
            |--------------------------------------------------------------------------
            | LOAD SUBJECTS
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | Route is POST.
            |
            | We send only:
            |
            | exam_master_id
            |
            | Controller gets Standard from Exam Master.
            |
            */

            async function loadSubjects(examMasterId)
            {

                subjectsContainer.innerHTML = `
                    <span style="color:#2563eb;">
                        Loading subjects...
                    </span>
                `;


                if (!examMasterId) {

                    subjectsContainer.innerHTML =
                        'Select Exam First';

                    return;
                }


                try {

                    const url =
                        '{{ route("teacher-bulk-allocation.subjects") }}';


                    const response =
                        await fetch(
                            url,
                            {
                                method: 'POST',

                                headers: {

                                    'Content-Type':
                                        'application/json',

                                    'Accept':
                                        'application/json',

                                    'X-Requested-With':
                                        'XMLHttpRequest',

                                    'X-CSRF-TOKEN':
                                        '{{ csrf_token() }}'
                                },

                                body:
                                    JSON.stringify({
                                        exam_master_id:
                                            examMasterId
                                    })
                            }
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | READ RESPONSE
                    |--------------------------------------------------------------------------
                    */

                    const responseText =
                        await response.text();


                    console.log(
                        'Subject API Status:',
                        response.status
                    );


                    console.log(
                        'Subject API Response:',
                        responseText
                    );


                    if (!response.ok) {

                        throw new Error(
                            'HTTP ' +
                            response.status +
                            ': ' +
                            responseText
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | PARSE JSON
                    |--------------------------------------------------------------------------
                    */

                    let data;

                    try {

                        data =
                            JSON.parse(
                                responseText
                            );

                    } catch (jsonError) {

                        console.error(
                            'Invalid JSON:',
                            responseText
                        );

                        throw new Error(
                            'Server returned invalid JSON.'
                        );
                    }


                    console.log(
                        'Parsed subject data:',
                        data
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | API FAILURE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !data ||
                        data.success !== true
                    ) {

                        subjectsContainer.innerHTML = `
                            <span style="color:red;">
                                ${escapeHtml(
                                    data?.message
                                    ||
                                    'No subjects found for this Exam.'
                                )}
                            </span>
                        `;

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SUBJECT ARRAY
                    |--------------------------------------------------------------------------
                    */

                    const subjects =
                        Array.isArray(
                            data.subjects
                        )
                        ? data.subjects
                        : [];


                    if (
                        subjects.length === 0
                    ) {

                        subjectsContainer.innerHTML = `
                            <span style="color:red;">
                                No subjects found for this Exam.
                            </span>
                        `;

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | BUILD SUBJECT CHECKBOXES
                    |--------------------------------------------------------------------------
                    */

                    let html = '';


                    subjects.forEach(
                        function (subject) {

                            const subjectId =
                                Number(
                                    subject.subject_id
                                );


                            const subjectName =
                                subject.subject_name
                                || '-';


                            const optionalText =
                                Number(
                                    subject.is_optional
                                ) === 1
                                ? ' (Optional)'
                                : '';


                            html += `
                                <label style="
                                    display:inline-flex;
                                    align-items:center;
                                    gap:6px;
                                    margin-right:10px;
                                    margin-bottom:6px;
                                    padding:6px 9px;
                                    border:1px solid #d1d5db;
                                    border-radius:5px;
                                    background:white;
                                    cursor:pointer;
                                ">

                                    <input
                                        type="checkbox"
                                        name="rows[0][subjects][]"
                                        value="${subjectId}"
                                    >

                                    <span>
                                        ${escapeHtml(
                                            subjectName
                                        )}
                                        ${escapeHtml(
                                            optionalText
                                        )}
                                    </span>

                                </label>
                            `;
                        }
                    );


                    subjectsContainer.innerHTML =
                        html;

                }


                catch (error)
                {

                    console.error(
                        'Subject loading error:',
                        error
                    );


                    subjectsContainer.innerHTML = `
                        <span style="color:red;">
                            Error loading subjects.
                        </span>
                    `;

                }

            }


            /*
            |--------------------------------------------------------------------------
            | EXAM CHANGE
            |--------------------------------------------------------------------------
            */

            exam.addEventListener(
                'change',
                async function ()
                {

                    const examMasterId =
                        this.value;


                    /*
                    |--------------------------------------------------------------------------
                    | RESET
                    |--------------------------------------------------------------------------
                    */

                    if (!examMasterId) {

                        resetFields();

                        return;
                    }


                    const selected =
                        this.options[
                            this.selectedIndex
                        ];


                    /*
                    |--------------------------------------------------------------------------
                    | STANDARD
                    |--------------------------------------------------------------------------
                    */

                    const selectedStandardId =
                        selected.dataset.standardId
                        || '';


                    const selectedStandardName =
                        selected.dataset.standardName
                        || '';


                    /*
                    |--------------------------------------------------------------------------
                    | SECTION
                    |--------------------------------------------------------------------------
                    */

                    const selectedSectionId =
                        selected.dataset.sectionId
                        || '';


                    /*
                    |--------------------------------------------------------------------------
                    | SHOW STANDARD
                    |--------------------------------------------------------------------------
                    */

                    standardName.value =
                        selectedStandardName;


                    standardId.value =
                        selectedStandardId;


                    sectionId.value =
                        selectedSectionId;


                    console.log(
                        'Selected Exam:',
                        examMasterId
                    );


                    console.log(
                        'Selected Standard:',
                        selectedStandardId
                    );


                    console.log(
                        'Selected Standard Name:',
                        selectedStandardName
                    );


                    console.log(
                        'Selected Section:',
                        selectedSectionId
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | STANDARD CHECK
                    |--------------------------------------------------------------------------
                    */

                    if (!selectedStandardId) {

                        divisionsContainer.innerHTML = `
                            <span style="color:red;">
                                Standard not assigned to Exam.
                            </span>
                        `;

                        subjectsContainer.innerHTML = `
                            <span style="color:red;">
                                Cannot load subjects.
                            </span>
                        `;

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SECTION CHECK
                    |--------------------------------------------------------------------------
                    */

                    if (!selectedSectionId) {

                        divisionsContainer.innerHTML = `
                            <span style="color:red;">
                                Section not assigned to selected Standard.
                            </span>
                        `;

                        subjectsContainer.innerHTML = `
                            <span style="color:red;">
                                Cannot load subjects.
                            </span>
                        `;

                        Swal.fire({
                            icon: 'error',
                            title: 'Section Not Found',
                            text: 'The selected Standard is not linked to a Section.'
                        });

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | LOAD DIVISIONS
                    |--------------------------------------------------------------------------
                    */

                    loadDivisions();


                    /*
                    |--------------------------------------------------------------------------
                    | LOAD SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    await loadSubjects(
                        examMasterId
                    );

                }
            );


            /*
            |--------------------------------------------------------------------------
            | FORM VALIDATION
            |--------------------------------------------------------------------------
            */

            allocationForm.addEventListener(
                'submit',
                function (e)
                {

                    const teacher =
                        document.getElementById(
                            'user_id'
                        ).value;


                    const year =
                        document.getElementById(
                            'academic_year_id'
                        ).value;


                    const examValue =
                        document.getElementById(
                            'exam_master_id'
                        ).value;


                    const standardValue =
                        document.getElementById(
                            'standard_id'
                        ).value;


                    const divisionsChecked =
                        document.querySelectorAll(
                            'input[name="rows[0][divisions][]"]:checked'
                        );


                    const subjectsChecked =
                        document.querySelectorAll(
                            'input[name="rows[0][subjects][]"]:checked'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | TEACHER
                    |--------------------------------------------------------------------------
                    */

                    if (!teacher) {

                        e.preventDefault();

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: 'Please select Teacher.'
                        });

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ACADEMIC YEAR
                    |--------------------------------------------------------------------------
                    */

                    if (!year) {

                        e.preventDefault();

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: 'Please select Academic Year.'
                        });

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | EXAM
                    |--------------------------------------------------------------------------
                    */

                    if (!examValue) {

                        e.preventDefault();

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: 'Please select Exam.'
                        });

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STANDARD
                    |--------------------------------------------------------------------------
                    */

                    if (!standardValue) {

                        e.preventDefault();

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text:
                                'Standard could not be determined from the selected Exam.'
                        });

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | DIVISIONS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        divisionsChecked.length === 0
                    ) {

                        e.preventDefault();

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text:
                                'Please select at least one Division.'
                        });

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SUBJECTS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        subjectsChecked.length === 0
                    ) {

                        e.preventDefault();

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text:
                                'Please select at least one Subject.'
                        });

                        return;
                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | RESTORE EXAM AFTER VALIDATION ERROR
            |--------------------------------------------------------------------------
            */

            const initialExam =
                exam.value;


            if (initialExam) {

                setTimeout(
                    function () {

                        exam.dispatchEvent(
                            new Event('change')
                        );

                    },
                    100
                );

            }

        });

    </script>

</x-app-layout>