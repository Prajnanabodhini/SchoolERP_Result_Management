<x-app-layout>

@php
$currentFilters = request()->query();
@endphp

<style>
.filter-row{
    display:flex;
    align-items:center;
    gap:6px;
    flex-wrap:nowrap;
}
.tabs-container{
    display:flex;
    border-bottom:2px solid #2563EB;
    margin-bottom:20px;
}

.active-tab{
    background:#2563EB;
    color:white !important;
    padding:10px 20px;
    text-decoration:none;
    border-radius:6px 6px 0 0;
    margin-right:4px;
    font-weight:bold;
}

.inactive-tab{
    background:#E5E7EB;
    color:#111827 !important;
    padding:10px 20px;
    text-decoration:none;
    border-radius:6px 6px 0 0;
    margin-right:4px;
    font-weight:bold;
}


.mark-input{
    width:70px;
    height:28px;
    padding:2px 4px;
    font-size:13px;
    text-align:center;
}
</style>

<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

    <h2 class="text-xl font-bold text-blue-600 mb-4">
    Examination Marks
</h2>

    {{-- Tabs --}}
    <div class="tabs-container">

<a href="{{ route('marks-entry.index', request()->query()) }}"
   class="
   {{ request()->routeIs('marks-entry.index')
      ? 'active-tab'
      : 'inactive-tab' }}">
   Marks Entry
</a>

<a href="{{ route('marks-entry.view', request()->query()) }}"
   class="
   {{ request()->routeIs('marks-entry.view')
      ? 'active-tab'
      : 'inactive-tab' }}">
   View / Edit Marks
</a>

</div>


@if(session('error'))

<div style="
    margin-top:10px;
    margin-bottom:15px;
    padding:10px;
    background:#FEE2E2;
    border:1px solid #EF4444;
    border-radius:5px;
    color:#DC2626;
    font-weight:bold;
">
    {{ session('error') }}
</div>

@endif

@if(session('success'))

<div style="
    margin-top:10px;
    margin-bottom:15px;
    padding:10px;
    background:#DCFCE7;
    border:1px solid #22C55E;
    border-radius:5px;
    color:#15803D;
    font-weight:bold;
">
    {{ session('success') }}
</div>

@endif
    @if(!empty($error))

    <div style="
        margin-bottom:15px;
        padding:10px;
        background:#FEE2E2;
        border:1px solid #EF4444;
        border-radius:4px;
    ">
        <span style="
            color:#DC2626;
            font-weight:bold;
        ">
            Error :-
        </span>

        <span style="color:#DC2626;">
            {{ $error }}
        </span>
    </div>

    @endif

    <form method="GET"
           action="{{ route('marks-entry.edit') }}">

        <div class="mb-4 flex items-center gap-3 flex-wrap">

            <label class="font-semibold">
                Exam
            </label>

            <select name="exam_master_id"
                    class="border rounded"
style="font-size:12px;height:30px;padding:2px 6px; width:100px"
                    style="min-width:150px;"
                     id="exam_master_id"
                    class="form-control"
                    onchange="this.form.submit()">

                <option value="">
                    Select
                </option>

                @foreach($exams as $examItem)

                <option value="{{ $examItem->id }}"
                    {{ request('exam_master_id') == $examItem->id ? 'selected' : '' }}>

                    {{ $examItem->exam_name }}

                </option>

                @endforeach

            </select>

            <label class="font-semibold">
                Teaching Assignment
            </label>

            <select
    name="teacher_subject_allocation_id"
    id="teacher_subject_allocation_id"
    class="border rounded"
    style="font-size:12px;height:30px;padding:2px 6px; width:300px"
    onchange="this.form.submit()">

                <option value="">
                    Select
                </option>

                @foreach($assignments as $assignment)

<option value="{{ $assignment->id }}">

    ID={{ $assignment->id }}

    {{ $assignment->subject->subject_name }}

</option>

@endforeach

            </select>

            <button type="submit"
                    class="erp-btn erp-btn-save">
                Load Students
            </button>

            @if(count($students) > 0)

                @php
                    $selectedAssignment = $assignments->where(
                        'id',
                        request('teacher_subject_allocation_id')
                    )->first();
                @endphp

                <span style="
                    background:#DBEAFE;
                    color:#1E40AF;
                    padding:6px 12px;
                    border-radius:4px;
                    font-weight:600;
                    font-size:13px;
                ">

                    {{ count($students) }} Students

                    @if($selectedAssignment)

                        |

                        {{ $selectedAssignment->subject->subject_name }}

                        -

                        {{ $selectedAssignment->allocation->standard->standard_name }}

                        {{ $selectedAssignment->allocation->division->division_name }}

                    @endif

                </span>

            @endif

        </div>

    </form>
    
@if($marksLocked)

<div style="
    margin-bottom:15px;
    padding:12px;
    background:#FEF3C7;
    border:1px solid #F59E0B;
    border-radius:5px;
">

    <strong style="color:#92400E;">
        Marks already submitted and locked.
    </strong>

    <br>

    <span style="color:#92400E;">
        Contact Admin for modification.
    </span>

</div>

@endif
<pre>
Students Loaded : {{ count($students) }}
</pre>
    @if(count($students))

    <form method="POST"
      <form method="POST"
      action="{{ route('marks-entry.update') }}">

        @csrf

        <input type="hidden"
               name="exam_master_id"
               value="{{ request('exam_master_id') }}">

        <input type="hidden"
               name="teacher_subject_allocation_id"
               value="{{ request('teacher_subject_allocation_id') }}">

               
        <table class="w-full border border-gray-300 text-sm bg-white">

            <thead class="bg-blue-100">

            <tr>

                <th class="border p-2">
                    GR No
                </th>

                <th class="border p-2">
                    Roll No
                </th>

                <th class="border p-2">
                    Student Name
                </th>

                @if($showTheory)

                    <th class="border p-2">
                        Theory Max
                    </th>

                    <th class="border p-2">
                        Theory Pass
                    </th>

                    <th class="border p-2">
                        Theory Obtained
                    </th>

                @endif

                @if($showOral)

                    <th class="border p-2">
                        Oral Max
                    </th>

                    <th class="border p-2">
                        Oral Pass
                    </th>

                    <th class="border p-2">
                        Oral Obtained
                    </th>

                @endif

                @if($showPractical)

                    <th class="border p-2">
                        Practical Max
                    </th>

                    <th class="border p-2">
                        Practical Pass
                    </th>

                    <th class="border p-2">
                        Practical Obtained
                    </th>

                @endif

            </tr>

            </thead>

            <tbody>

            @foreach($students as $record)

            <tr>

                <td class="border p-2">
                    {{ $record->regno }}
                </td>

                <td class="border p-2">
                    {{ $record->rollno }}
                </td>

                <td class="border p-2">
                    {{ $record->studname }}
                </td>

                <input type="hidden"
       name="mark_ids[]"
       value="{{ $record->mark_id }}">

                @if($showTheory)

                <td class="border p-2 text-center">
                    {{ (int)$theoryMaxMarks }}
                </td>

                <td class="border p-2 text-center">
                    {{ (int)$theoryPassingMarks }}
                </td>

<td class="border p-2">
    <input type="number"
        name="theory_marks[{{ $record->mark_id }}]"
        value="{{ $record->theory_obtained_marks }}"
        min="0"
        max="{{ $exam->theory_max_marks }}"
        required
        class="mark-input">

    @error('theory_marks.'.$record->Studentid)
        <div style="color:red;font-size:11px;">
            {{ $message }}
        </div>
    @enderror
</td>
                @endif

                @if($showOral)

                <td class="border p-2 text-center">
                    {{ (int)$oralMaxMarks }}
                </td>

                <td class="border p-2 text-center">
                    {{ (int)$oralPassingMarks }}
                </td>

 <td class="border p-2">
    <input type="number"
        name="oral_marks[{{ $record->mark_id }}]"
        value="{{ $record->oral_obtained_marks }}"
        min="0"
        max="{{ $exam->oral_max_marks }}">

    @error('oral_marks.'.$record->Studentid)
        <div style="color:red;font-size:11px;">
            {{ $message }}
        </div>
    @enderror
</td>

</td>

                @endif

                @if($showPractical)

                <td class="border p-2 text-center">
                    {{ (int)$practicalMaxMarks }}
                </td>

                <td class="border p-2 text-center">
                    {{ (int)$practicalPassingMarks }}
                </td>

<td class="border p-2">
    <input type="number"
        name="practical_marks[{{ $record->mark_id }}]"
        value="{{ $record->practical_obtained_marks }}"
        min="0"
        max="{{ $exam->practical_max_marks }}">

    @error('practical_marks.'.$record->Studentid)
        <div style="color:red;font-size:11px;">
            {{ $message }}
        </div>
    @enderror
</td>
                @endif

            </tr>

            @endforeach

            </tbody>

        </table>

        <div class="mt-4">

            {{-- <button type="submit"
                    class="erp-btn erp-btn-save">
                Save Marks
            </button> --}}
            @if(true)

            <button type="submit"
                    class="erp-btn erp-btn-save">
                Update Marks
            </button>

            @endif

        </div>

    </form>

    @endif

</div>
@if(session('force_section_error'))

<div
    class="modal fade"
    id="sectionErrorModal"
    tabindex="-1"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
>
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    Wrong Section Selected
                </h5>
            </div>

            <div class="modal-body">
                {{ session('force_section_error') }}
            </div>

            <div class="modal-footer">
                <a
                    href="{{ route('logout') }}"
                    class="btn btn-danger"
                >
                    Login Again
                </a>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function()
    {
        new bootstrap.Modal(
            document.getElementById(
                'sectionErrorModal'
            )
        ).show();
    }
);
</script>

@endif
@if(session('force_section_error'))
<script>
    alert(
        "{{ session('force_section_error') }}"
    );

    window.location.href =
        "{{ route('logout') }}";
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.mark-input').forEach(function (textbox) {

        let errorDiv = document.createElement('div');
        errorDiv.style.color = 'red';
        errorDiv.style.fontSize = '11px';
        errorDiv.style.marginTop = '2px';

        textbox.parentNode.appendChild(errorDiv);

        textbox.addEventListener('input', function () {

            let max = parseFloat(this.max);
            let value = this.value;

            this.style.border = '';
            errorDiv.innerHTML = '';

            // Blank
            if (value === '') {
                this.style.border = '2px solid red';
                errorDiv.innerHTML = 'Marks required';
                return;
            }

            value = parseFloat(value);

            // Less than zero
            if (value < 0) {
                this.style.border = '2px solid red';
                errorDiv.innerHTML = 'Marks cannot be negative';
                return;
            }

            // Greater than max
            if (value > max) {
                this.style.border = '2px solid red';
                errorDiv.innerHTML =
                    'Maximum allowed marks is ' + max;
                return;
            }

            // Valid
            this.style.border = '2px solid green';
        });

    });

});
</script>
</x-app-layout>

