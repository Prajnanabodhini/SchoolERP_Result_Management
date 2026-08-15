<x-app-layout>

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


{{-- =========================================================
     TITLE
========================================================= --}}

<h2 style="
    text-align:center;
    color:#16a34a;
    font-size:22px;
    font-weight:bold;
    margin-bottom:18px;
">
    Teacher Bulk Allocation
</h2>


{{-- =========================================================
     MESSAGES
========================================================= --}}

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


<form
    id="allocationForm"
    method="POST"
    action="{{ route('teacher-bulk-allocation.store') }}"
>

@csrf


{{-- =========================================================
     HEADER ROW
     TEACHER | ACADEMIC YEAR | EXAM
========================================================= --}}

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

<option value="{{ $teacher->id }}">
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

<option value="{{ $year->id }}">
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
>
    {{ $exam->exam_name }}
</option>

@endforeach
</select>

</div>

</div>


{{-- =========================================================
     ALLOCATION ROW
     STANDARD | DIVISIONS
========================================================= --}}

<div style="
    border:1px solid #d1d5db;
    border-radius:7px;
    padding:12px;
    background:#f9fafb;
">


<div style="
    display:grid;
    grid-template-columns:260px 1fr;
    gap:15px;
    align-items:start;
">


{{-- =====================================================
     STANDARD - FROZEN TEXT BOX
===================================================== --}}

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


{{-- Hidden standard ID --}}

<input
    type="hidden"
    name="rows[0][standards][]"
    id="standard_id"
    value=""
>


{{-- Hidden section ID --}}

<input
    type="hidden"
    name="rows[0][section_id]"
    id="section_id"
    value=""
>

</div>


{{-- =====================================================
     DIVISIONS
===================================================== --}}

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


{{-- =====================================================
     SUBJECTS
===================================================== --}}

<div style="
    margin-top:12px;
">

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


{{-- =========================================================
     BUTTONS
========================================================= --}}

<div style="
    margin-top:15px;
    display:flex;
    gap:8px;
">

<button
    type="submit"
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


    /*
    |--------------------------------------------------------------------------
    | EXAM CHANGE
    |--------------------------------------------------------------------------
    */

    exam.addEventListener('change', function () {

    const selected = this.options[this.selectedIndex];

    if (!this.value) {

        standardName.value = '';
        standardId.value = '';
        sectionId.value = '';

        divisionsContainer.innerHTML =
            'Select Exam First';

        subjectsContainer.innerHTML =
            'Select Exam First';

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | GET STANDARD
    |--------------------------------------------------------------------------
    */

    const selectedStandardId =
        selected.dataset.standardId || '';

    const selectedStandardName =
        selected.dataset.standardName || '';

    /*
    |--------------------------------------------------------------------------
    | GET SECTION
    |--------------------------------------------------------------------------
    */

    const selectedSectionId =
        selected.dataset.sectionId || '';

    /*
    |--------------------------------------------------------------------------
    | SET STANDARD
    |--------------------------------------------------------------------------
    */

    standardName.value =
        selectedStandardName;

    standardId.value =
        selectedStandardId;

    /*
    |--------------------------------------------------------------------------
    | SET SECTION
    |--------------------------------------------------------------------------
    */

    sectionId.value =
        selectedSectionId;

    console.log('Exam:', this.value);
    console.log('Standard:', selectedStandardId);
    console.log('Section:', selectedSectionId);

    if (!selectedSectionId) {

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

    loadDivisions(
        selectedStandardId
    );

    /*
    |--------------------------------------------------------------------------
    | LOAD SUBJECTS FOR THIS EXAM + STANDARD
    |--------------------------------------------------------------------------
    */

    loadSubjects(
        selectedStandardId,
        this.value
    );

});

    /*
    |--------------------------------------------------------------------------
    | LOAD DIVISIONS
    |--------------------------------------------------------------------------
    */

    function loadDivisions(standardIdValue)
    {

        divisionsContainer.innerHTML =
            '<span style="color:#2563eb;">Loading divisions...</span>';


        if (!standardIdValue) {

            divisionsContainer.innerHTML =
                '<span style="color:red;">Standard not available.</span>';

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        | This uses the existing divisions available to the page.
        |
        */

        const divisions =
            @json($divisions);


        let html = '';


        if (
            !Array.isArray(divisions) ||
            divisions.length === 0
        ) {

            divisionsContainer.innerHTML =
                '<span style="color:red;">No divisions found.</span>';

            return;

        }


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

                    ${division.division_name}

                </label>

            `;

        });


        divisionsContainer.innerHTML =
            html;

    }


    /*
    |--------------------------------------------------------------------------
    | LOAD SUBJECTS
    |--------------------------------------------------------------------------
    */

    function loadSubjects(
    standardIdValue,
    examMasterId
) {

    if (!standardIdValue || !examMasterId) {

        subjectsContainer.innerHTML =
            'Select Exam First';

        return;
    }

    subjectsContainer.innerHTML =
        '<span style="color:#2563eb;">Loading subjects...</span>';

    fetch(
        '{{ route("teacher-bulk-allocation.subjects") }}',
        {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

                'Accept': 'application/json',

                'X-CSRF-TOKEN':
                    '{{ csrf_token() }}'

            },

            body: JSON.stringify({

                standard_ids: [
                    standardIdValue
                ],

                exam_master_id:
                    examMasterId

            })

        }
    )

    .then(function(response) {

        if (!response.ok) {

            throw new Error(
                'HTTP Error ' + response.status
            );

        }

        return response.json();

    })

    .then(function(data) {

        if (
            !Array.isArray(data) ||
            data.length === 0
        ) {

            subjectsContainer.innerHTML = `
                <span style="color:red;">
                    No subjects found for this Exam.
                </span>
            `;

            return;
        }

        let html = '';

        data.forEach(function(subject) {

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
                        value="${subject.subject_id}"
                    >

                    ${subject.subject_name}

                </label>

            `;

        });

        subjectsContainer.innerHTML =
            html;

    })

    .catch(function(error) {

        console.error(error);

        subjectsContainer.innerHTML = `

            <span style="color:red;">
                Error loading subjects.
            </span>

        `;

    });
}


    /*
    |--------------------------------------------------------------------------
    | FORM VALIDATION
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('allocationForm')
        .addEventListener('submit', function(e){

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


            const divisions =
                document.querySelectorAll(
                    'input[name="rows[0][divisions][]"]:checked'
                );


            const subjects =
                document.querySelectorAll(
                    'input[name="rows[0][subjects][]"]:checked'
                );


            if(!teacher){

                e.preventDefault();

                Swal.fire({
                    icon:'error',
                    title:'Validation Error',
                    text:'Please select Teacher.'
                });

                return;

            }


            if(!year){

                e.preventDefault();

                Swal.fire({
                    icon:'error',
                    title:'Validation Error',
                    text:'Please select Academic Year.'
                });

                return;

            }


            if(!examValue){

                e.preventDefault();

                Swal.fire({
                    icon:'error',
                    title:'Validation Error',
                    text:'Please select Exam.'
                });

                return;

            }


            if(!standardValue){

                e.preventDefault();

                Swal.fire({
                    icon:'error',
                    title:'Validation Error',
                    text:'Standard could not be determined from the selected Exam.'
                });

                return;

            }


            if(divisions.length === 0){

                e.preventDefault();

                Swal.fire({
                    icon:'error',
                    title:'Validation Error',
                    text:'Please select at least one Division.'
                });

                return;

            }


            if(subjects.length === 0){

                e.preventDefault();

                Swal.fire({
                    icon:'error',
                    title:'Validation Error',
                    text:'Please select at least one Subject.'
                });

                return;

            }

        });

});

</script>

</x-app-layout>