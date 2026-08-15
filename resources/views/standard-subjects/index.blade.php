<x-app-layout>

<div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold text-blue-600">
        Standard Subject Allocation
    </h2>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-700 p-2 mb-4 rounded">
        {{ session('success') }}
    </div>
@endif

<form method="GET"
      action="{{ route('standard-subject-allocation.index') }}">

    <div class="mb-4 flex items-center gap-3">

        <label class="font-semibold">
            Standard
        </label>

        <select name="standard_id"
                class="border rounded px-2 py-1 w-48 text-sm">

            <option value="">
                Select Standard
            </option>

            @foreach($standards as $standard)

                <option value="{{ $standard->id }}"
                    {{ $selectedStandard == $standard->id ? 'selected' : '' }}>

                    {{ $standard->standard_name }}

                </option>

            @endforeach

        </select>

        <button type="submit"
                class="erp-btn erp-btn-save">
            Load Subjects
        </button>

    </div>

</form>

@if($selectedStandard)

<form method="POST"
      action="{{ route('standard-subject-allocation.save') }}">

    @csrf

    <input type="hidden"
           name="standard_id"
           value="{{ $selectedStandard }}">

    @php

    $maxRows = max(
        $academicSubjects->count(),
        $skillSubjects->count(),
        $coScholasticSubjects->count()
    );

    @endphp

    <table class="w-full border border-gray-300 text-sm">

        <thead class="bg-blue-100">

            <tr>

                <th class="border p-2">

                    <label class="flex items-center gap-2">

                        <input type="checkbox"
                               id="academic-select-all">

                        <span>
                            Academic Subjects
                        </span>

                    </label>

                </th>

                <th class="border p-2">

                    <label class="flex items-center gap-2">

                        <input type="checkbox"
                               id="skill-select-all">

                        <span>
                            Skill Subjects
                        </span>

                    </label>

                </th>

                <th class="border p-2">

                    <label class="flex items-center gap-2">

                        <input type="checkbox"
                               id="co-select-all">

                        <span>
                            Co-Scholastic Subjects
                        </span>

                    </label>

                </th>

            </tr>

        </thead>

        <tbody>

        @for($i = 0; $i < $maxRows; $i++)

            <tr>

                <!-- Academic -->

                <td class="border p-2">

                    @if(isset($academicSubjects[$i]))

                        <label>

                            <input type="checkbox"
                                   class="academic-checkbox"
                                   name="subjects[]"
                                   value="{{ $academicSubjects[$i]->id }}"

                            {{ in_array(
                                $academicSubjects[$i]->id,
                                $allocatedSubjects
                            ) ? 'checked' : '' }}>

                            {{ $academicSubjects[$i]->subject_name }}

                        </label>

                    @endif

                </td>

                <!-- Skill -->

                <td class="border p-2">

                    @if(isset($skillSubjects[$i]))

                        <label>

                            <input type="checkbox"
                                   class="skill-checkbox"
                                   name="subjects[]"
                                   value="{{ $skillSubjects[$i]->id }}"

                            {{ in_array(
                                $skillSubjects[$i]->id,
                                $allocatedSubjects
                            ) ? 'checked' : '' }}>

                            {{ $skillSubjects[$i]->subject_name }}

                        </label>

                    @endif

                </td>

                <!-- Co-Scholastic -->

                <td class="border p-2">

                    @if(isset($coScholasticSubjects[$i]))

                        <label>

                            <input type="checkbox"
                                   class="co-checkbox"
                                   name="subjects[]"
                                   value="{{ $coScholasticSubjects[$i]->id }}"

                            {{ in_array(
                                $coScholasticSubjects[$i]->id,
                                $allocatedSubjects
                            ) ? 'checked' : '' }}>

                            {{ $coScholasticSubjects[$i]->subject_name }}

                        </label>

                    @endif

                </td>

            </tr>

        @endfor

        </tbody>

    </table>

    <div class="mt-4">

        <button type="submit"
                class="erp-btn erp-btn-save">

            Save Allocation

        </button>

    </div>

</form>

@endif

</div>

<script>

document.addEventListener('DOMContentLoaded', function(){

    let academicHeader =
        document.getElementById(
            'academic-select-all'
        );

    if(academicHeader)
    {
        academicHeader.addEventListener(
            'change',
            function(){

                document
                .querySelectorAll(
                    '.academic-checkbox'
                )
                .forEach(function(cb){

                    cb.checked =
                        academicHeader.checked;

                });

            }
        );
    }

    let skillHeader =
        document.getElementById(
            'skill-select-all'
        );

    if(skillHeader)
    {
        skillHeader.addEventListener(
            'change',
            function(){

                document
                .querySelectorAll(
                    '.skill-checkbox'
                )
                .forEach(function(cb){

                    cb.checked =
                        skillHeader.checked;

                });

            }
        );
    }

    let coHeader =
        document.getElementById(
            'co-select-all'
        );

    if(coHeader)
    {
        coHeader.addEventListener(
            'change',
            function(){

                document
                .querySelectorAll(
                    '.co-checkbox'
                )
                .forEach(function(cb){

                    cb.checked =
                        coHeader.checked;

                });

            }
        );
    }

});

</script>

</x-app-layout>