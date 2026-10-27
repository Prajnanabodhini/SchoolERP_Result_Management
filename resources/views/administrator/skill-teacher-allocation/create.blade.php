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

    <div style="max-width:1200px; margin:auto; padding:15px;">

        <div style="background:white; border-radius:10px; padding:15px; border:1px solid #d1d5db; box-shadow:0 3px 8px rgba(0,0,0,.12);">

            {{-- TITLE --}}
            <h2 style="text-align:center; color:#16a34a; font-size:18px; font-weight:bold; margin-bottom:12px;">
                Skill Teacher Allocation
            </h2>

            {{-- SUCCESS / ERROR MESSAGES --}}
            @if(session('success'))
                <div style="background:#dcfce7; color:#166534; padding:6px 10px; font-size:13px; margin-bottom:10px; border-radius:5px;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background:#fee2e2; color:#991b1b; padding:6px 10px; font-size:13px; margin-bottom:10px; border-radius:5px;">
                    {{ session('error') }}
                </div>
            @endif

            {{-- FORM --}}
            <form id="allocationForm" method="POST" action="{{ route('skill-teacher-allocation.store') }}">
                @csrf

                {{-- TEACHER | ACADEMIC YEAR | EXAM --}}
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:12px;">

                    {{-- TEACHER --}}
                    <div>
                        <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Teacher</label>
                        <select name="user_id" id="user_id" class="form-select" style="width:100%; font-size:13px; height:32px; padding:4px 8px;" required>
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('user_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ACADEMIC YEAR --}}
                    <div>
                        <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Academic Year</label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select" style="width:100%; font-size:13px; height:32px; padding:4px 8px;" required>
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- EXAM --}}
                    <div>
                        <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Exam</label>
                        <select name="exam_master_id" id="exam_master_id" class="form-select" style="width:100%; font-size:13px; height:32px; padding:4px 8px;" required>
                            <option value="">Select Exam</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}"
                                        data-standard-id="{{ $exam->standard_id }}"
                                        data-standard-name="{{ $exam->standard->standard_name ?? '' }}"
                                        data-section-id="{{ $exam->standard->section_id ?? '' }}"
                                        {{ old('exam_master_id') == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- STANDARD / DIVISIONS / SUBJECTS --}}
                <div style="border:1px solid #d1d5db; border-radius:7px; padding:10px; background:#f9fafb;">

                    {{-- STANDARD + DIVISIONS --}}
                    <div style="display:grid; grid-template-columns:260px 1fr; gap:12px; align-items:start;">

                        <div>
                            <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Standard</label>
                            <input type="text" id="standard_name" value="" readonly placeholder="Select Exam First"
                                   style="width:100%; height:32px; padding:4px 8px; font-size:13px; border:1px solid #d1d5db; border-radius:5px; background:#e5e7eb; color:#374151; font-weight:bold;">
                            <input type="hidden" name="rows[0][standards][]" id="standard_id" value="">
                            <input type="hidden" name="rows[0][section_id]" id="section_id" value="">
                        </div>

                        <div>
                            <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Divisions</label>
                            <div id="divisions-container" style="min-height:32px; padding:6px 8px; font-size:13px; border:1px solid #d1d5db; background:white; border-radius:5px;">
                                Select Exam First
                            </div>
                        </div>
                    </div>

                    {{-- SKILL SUBJECTS --}}
                    <div style="margin-top:10px;">
                        <label style="display:block; font-weight:bold; font-size:13px; margin-bottom:4px;">Skill Subjects</label>
                        <div id="subjects-container" style="min-height:32px; padding:6px 8px; font-size:13px; border:1px solid #d1d5db; background:white; border-radius:5px;">
                            Select Exam First
                        </div>
                    </div>
                </div>

                {{-- BUTTONS --}}
                <div style="margin-top:12px; display:flex; gap:8px;">
                    <button type="submit" id="saveButton"
                            style="background:#16a34a; color:white; border:none; padding:6px 16px; font-size:13px; border-radius:5px; cursor:pointer; font-weight:bold;">
                        Save Allocation
                    </button>
                    <a href="{{ route('skill-teacher-allocation.index') }}"
                       style="background:#6b7280; color:white; padding:6px 16px; font-size:13px; text-decoration:none; border-radius:5px;">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const exam = document.getElementById('exam_master_id');
            const standardName = document.getElementById('standard_name');
            const standardId = document.getElementById('standard_id');
            const sectionId = document.getElementById('section_id');
            const divisionsContainer = document.getElementById('divisions-container');
            const subjectsContainer = document.getElementById('subjects-container');
            const allocationForm = document.getElementById('allocationForm');

            const divisions = @json($divisions);
            const skillSubjectsByStandard = @json($skillSubjectsByStandard ?? []);

            function escapeHtml(value) {
                if (value === null || value === undefined) return '';
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function resetFields() {
                standardName.value = '';
                standardId.value = '';
                sectionId.value = '';
                divisionsContainer.innerHTML = 'Select Exam First';
                subjectsContainer.innerHTML = 'Select Exam First';
            }

            function loadDivisions() {
                divisionsContainer.innerHTML = '';

                if (!Array.isArray(divisions) || divisions.length === 0) {
                    divisionsContainer.innerHTML = `<span style="color:red;">No divisions found.</span>`;
                    return;
                }

                let html = '';
                divisions.forEach(function (division) {
                    html += `
                        <label style="display:inline-flex; align-items:center; gap:5px; font-size:12px; margin-right:10px; margin-bottom:4px; white-space:nowrap; cursor:pointer;">
                            <input type="checkbox" name="rows[0][divisions][]" value="${division.id}">
                            <span>${escapeHtml(division.division_name)}</span>
                        </label>
                    `;
                });
                divisionsContainer.innerHTML = html;
            }

            function loadSkillSubjects(stdId) {
                subjectsContainer.innerHTML = '';

                const subjects = skillSubjectsByStandard[stdId] || [];

                if (subjects.length === 0) {
                    subjectsContainer.innerHTML = `<span style="color:red;">No skill subjects found for this Standard.</span>`;
                    return;
                }

                let html = '';
                subjects.forEach(function (subject) {
                    html += `
                        <label style="display:inline-flex; align-items:center; gap:6px; padding:4px 6px; font-size:12px; margin-right:8px; margin-bottom:4px; border:1px solid #d1d5db; border-radius:5px; background:white; cursor:pointer;">
                            <input type="checkbox" name="rows[0][subjects][]" value="${subject.id}">
                            <span>${escapeHtml(subject.subject_name)}</span>
                        </label>
                    `;
                });
                subjectsContainer.innerHTML = html;
            }

            exam.addEventListener('change', function () {
                const examMasterId = this.value;

                if (!examMasterId) {
                    resetFields();
                    return;
                }

                const selected = this.options[this.selectedIndex];
                const selectedStandardId = selected.dataset.standardId || '';
                const selectedStandardName = selected.dataset.standardName || '';
                const selectedSectionId = selected.dataset.sectionId || '';

                standardName.value = selectedStandardName;
                standardId.value = selectedStandardId;
                sectionId.value = selectedSectionId;

                if (!selectedStandardId) {
                    divisionsContainer.innerHTML = `<span style="color:red;">Standard not assigned to Exam.</span>`;
                    subjectsContainer.innerHTML = `<span style="color:red;">Cannot load subjects.</span>`;
                    return;
                }

                if (!selectedSectionId) {
                    divisionsContainer.innerHTML = `<span style="color:red;">Section not assigned to selected Standard.</span>`;
                    subjectsContainer.innerHTML = `<span style="color:red;">Cannot load subjects.</span>`;
                    Swal.fire({ icon: 'error', title: 'Section Not Found', text: 'The selected Standard is not linked to a Section.' });
                    return;
                }

                loadDivisions();
                loadSkillSubjects(selectedStandardId);
            });

            allocationForm.addEventListener('submit', function (e) {
                const teacher = document.getElementById('user_id').value;
                const year = document.getElementById('academic_year_id').value;
                const examValue = document.getElementById('exam_master_id').value;
                const standardValue = document.getElementById('standard_id').value;
                const divisionsChecked = document.querySelectorAll('input[name="rows[0][divisions][]"]:checked');
                const subjectsChecked = document.querySelectorAll('input[name="rows[0][subjects][]"]:checked');

                if (!teacher) {
                    e.preventDefault();
                    Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please select Teacher.' });
                    return;
                }
                if (!year) {
                    e.preventDefault();
                    Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please select Academic Year.' });
                    return;
                }
                if (!examValue) {
                    e.preventDefault();
                    Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please select Exam.' });
                    return;
                }
                if (!standardValue) {
                    e.preventDefault();
                    Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Standard could not be determined from the selected Exam.' });
                    return;
                }
                if (divisionsChecked.length === 0) {
                    e.preventDefault();
                    Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please select at least one Division.' });
                    return;
                }
                if (subjectsChecked.length === 0) {
                    e.preventDefault();
                    Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please select at least one Skill Subject.' });
                    return;
                }
            });

            // Restore exam state after validation error
            const initialExam = exam.value;
            if (initialExam) {
                setTimeout(function () {
                    exam.dispatchEvent(new Event('change'));
                }, 100);
            }
        });
    </script>

</x-app-layout>