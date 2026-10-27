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

    @php
        $existingExamId = $allocation->subjectAllocations->first()->exam_master_id ?? '';
    @endphp

    <div style="max-width:1200px; margin:auto; padding:15px;">
        <div style="background:white; border-radius:10px; padding:15px; border:1px solid #d1d5db; box-shadow:0 3px 8px rgba(0,0,0,.12);">

            <h2 style="text-align:center; color:#2563eb; font-size:18px; font-weight:bold; margin-bottom:12px;">
                Edit Skill Teacher Allocation
            </h2>

            @if(session('error'))
                <div style="background:#fee2e2; color:#991b1b; padding:6px 10px; font-size:13px; margin-bottom:10px; border-radius:5px;">
                    {{ session('error') }}
                </div>
            @endif

            <form id="allocationForm" method="POST" action="{{ route('skill-teacher-allocation.update', $allocation->id) }}">
                @csrf
                @method('PUT')

                {{-- TEACHER | ACADEMIC YEAR | EXAM --}}
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:12px;">

                    <div>
                        <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Teacher</label>
                        <select name="user_id" id="user_id" class="form-select" style="width:100%; font-size:13px; height:32px; padding:4px 8px;" required>
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('user_id', $allocation->user_id) == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Academic Year</label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select" style="width:100%; font-size:13px; height:32px; padding:4px 8px;" required>
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $allocation->academic_year_id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Exam</label>
                        <select name="exam_master_id" id="exam_master_id" class="form-select" style="width:100%; font-size:13px; height:32px; padding:4px 8px;" required>
                            <option value="">Select Exam</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}"
                                        data-standard-id="{{ $exam->standard_id }}"
                                        data-standard-name="{{ $exam->standard->standard_name ?? '' }}"
                                        data-section-id="{{ $exam->standard->section_id ?? '' }}"
                                        {{ old('exam_master_id', $existingExamId) == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Hidden standard/section values --}}
                <input type="hidden" name="standard_id" id="standard_id_input" value="{{ $allocation->standard_id }}">
                <input type="hidden" name="section_id" id="section_id_input" value="{{ $allocation->section_id }}">

                {{-- STANDARD / DIVISIONS / SUBJECTS --}}
                <div style="border:1px solid #d1d5db; border-radius:7px; padding:10px; background:#f9fafb;">

                    <div style="display:grid; grid-template-columns:260px 1fr; gap:12px; align-items:start;">

                        <div>
                            <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Standard</label>
                            <input type="text" id="standard_name"
                                   value="{{ $allocation->standard->standard_name ?? '' }}"
                                   readonly
                                   style="width:100%; height:32px; padding:4px 8px; font-size:13px; border:1px solid #d1d5db; border-radius:5px; background:#e5e7eb; color:#374151; font-weight:bold;">
                            <input type="hidden" id="standard_id" value="{{ $allocation->standard_id }}">
                            <input type="hidden" id="section_id" value="{{ $allocation->section_id }}">
                        </div>

                        <div>
                            <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Divisions</label>
                            <div id="divisions-container" style="min-height:32px; padding:6px 8px; font-size:13px; border:1px solid #d1d5db; background:white; border-radius:5px;"></div>
                        </div>
                    </div>

                    <div style="margin-top:10px;">
                        <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Skill Subjects</label>
                        <div id="subjects-container" style="min-height:32px; padding:6px 8px; font-size:13px; border:1px solid #d1d5db; background:white; border-radius:5px;"></div>
                    </div>
                </div>

                {{-- BUTTONS --}}
                <div style="margin-top:12px; display:flex; gap:8px;">
                    <button type="submit"
                            style="background:#2563eb; color:white; border:none; height:32px; padding:0 16px; border-radius:5px; cursor:pointer; font-weight:bold; font-size:13px;">
                        Update Allocation
                    </button>
                    <a href="{{ route('skill-teacher-allocation.index') }}"
                       style="background:#6b7280; color:white; height:32px; padding:0 16px; text-decoration:none; border-radius:5px; display:inline-flex; align-items:center; font-weight:bold; font-size:13px;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const standardIdInput    = document.getElementById('standard_id_input');
            const sectionIdInput     = document.getElementById('section_id_input');
            const standardIdHidden   = document.getElementById('standard_id');
            const sectionIdHidden    = document.getElementById('section_id');
            const divisionsContainer = document.getElementById('divisions-container');
            const subjectsContainer  = document.getElementById('subjects-container');
            const allocationForm     = document.getElementById('allocationForm');

            const divisions = @json($divisions);
            const skillSubjectsByStandard = @json($skillSubjectsByStandard ?? []);

            const existingStandardId  = "{{ $allocation->standard_id }}";
            const existingDivisionIds = @json($existingDivisionIds ?? []);
            const existingSubjectIds  = @json($existingSubjectIds ?? []);

            function escapeHtml(value) {
                if (value === null || value === undefined) return '';
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderDivisions() {
                divisionsContainer.innerHTML = '';

                if (!Array.isArray(divisions) || divisions.length === 0) {
                    divisionsContainer.innerHTML = `<span style="color:red;">No divisions found.</span>`;
                    return;
                }

                let html = '';
                divisions.forEach(function (division) {
                    const isChecked = existingDivisionIds.map(String).includes(String(division.id));
                    const checked = isChecked ? 'checked' : '';
                    html += `
                        <label style="display:inline-flex; align-items:center; gap:5px; font-size:12px; margin-right:10px; margin-bottom:4px; white-space:nowrap; cursor:pointer;">
                            <input type="checkbox" name="divisions[]" value="${division.id}" ${checked}>
                            <span>${escapeHtml(division.division_name)}</span>
                        </label>
                    `;
                });
                divisionsContainer.innerHTML = html;
            }

            function renderSubjects() {
                subjectsContainer.innerHTML = '';

                const subjects = skillSubjectsByStandard[existingStandardId] || [];

                if (subjects.length === 0) {
                    subjectsContainer.innerHTML = `<span style="color:red;">No skill subjects found for this Standard.</span>`;
                    return;
                }

                let html = '';
                subjects.forEach(function (subject) {
                    const isChecked = existingSubjectIds.map(String).includes(String(subject.id));
                    const checked = isChecked ? 'checked' : '';
                    html += `
                        <label style="display:inline-flex; align-items:center; gap:6px; padding:4px 6px; font-size:12px; margin-right:8px; margin-bottom:4px; border:1px solid #d1d5db; border-radius:5px; background:white; cursor:pointer;">
                            <input type="checkbox" name="subjects[]" value="${subject.id}" ${checked}>
                            <span>${escapeHtml(subject.subject_name)}</span>
                        </label>
                    `;
                });
                subjectsContainer.innerHTML = html;
            }

            // Initial render
            renderDivisions();
            renderSubjects();

            // Form validation
            allocationForm.addEventListener('submit', function (e) {
                const teacher = document.getElementById('user_id').value;
                const year    = document.getElementById('academic_year_id').value;
                const exam    = document.getElementById('exam_master_id').value;
                const divisionsChecked = document.querySelectorAll('input[name="divisions[]"]:checked');
                const subjectsChecked  = document.querySelectorAll('input[name="subjects[]"]:checked');

                if (!teacher) { e.preventDefault(); Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please select Teacher.' }); return; }
                if (!year)    { e.preventDefault(); Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please select Academic Year.' }); return; }
                if (!exam)    { e.preventDefault(); Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please select Exam.' }); return; }
                if (divisionsChecked.length === 0) { e.preventDefault(); Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please select at least one Division.' }); return; }
                if (subjectsChecked.length === 0)  { e.preventDefault(); Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please select at least one Skill Subject.' }); return; }
            });
        });
    </script>

</x-app-layout>