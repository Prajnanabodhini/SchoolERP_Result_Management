<x-app-layout>
    <div class="w-full">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">

            <!-- Header -->
            <div class="mb-4">
                <h2 class="text-lg font-bold text-blue-700 uppercase">
                    SKILL SUBJECT MARKS ENTRY
                </h2>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-3 rounded mb-4 text-xs">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Filter Form -->
            <form method="GET" action="{{ route('student-skill-marks.index') }}">
                <div class="flex flex-wrap items-end gap-2 mb-4">

                    <!-- Academic Year -->
                    <div class="w-32">
                        <label class="block text-sm font-bold text-gray-800 mb-1">
                            Academic Year <span class="text-red-500">*</span>
                        </label>
                        <select name="academic_year_id" class="w-full border border-gray-300 rounded px-2 h-8 text-xs focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Exam -->
                    <div class="w-44">
                        <label class="block text-sm font-bold text-gray-800 mb-1">
                            Exam <span class="text-red-500">*</span>
                        </label>
                        <select name="exam_master_id" class="w-full border border-gray-300 rounded px-2 h-8 text-xs focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Exam</option>
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}" {{ request('exam_master_id') == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Division -->
                    <div class="w-20">
                        <label class="block text-sm font-bold text-gray-800 mb-1">
                            Division <span class="text-red-500">*</span>
                        </label>
                        <select name="division_id" class="w-full border border-gray-300 rounded px-2 h-8 text-xs focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                    {{ $division->division_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Skill Subject -->
                    <div class="w-40">
                        <label class="block text-sm font-bold text-gray-800 mb-1">
                            Skill Subject <span class="text-red-500">*</span>
                        </label>
                        <select name="subject_id" class="w-full border border-gray-300 rounded px-2 h-8 text-xs focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select</option>
                            @foreach($skillSubjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->subject_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Max Marks -->
                    <div class="w-16">
                        <label class="block text-sm font-bold text-gray-800 mb-1">
                            Max <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               name="max_marks"
                               id="filter_max_marks"
                               value="{{ (int) $maxMarks }}"
                               class="no-spinner w-full border border-gray-300 rounded px-1 h-8 text-xs text-center focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Passing Marks -->
                    <div class="w-16">
                        <label class="block text-sm font-bold text-gray-800 mb-1">
                            Pass <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               inputmode="numeric"
                               pattern="[0-9]*"
                               name="passing_marks"
                               id="filter_passing_marks"
                               value="{{ (int) $passingMarks }}"
                               class="no-spinner w-full border border-gray-300 rounded px-1 h-8 text-xs text-center focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Load Button -->
                    <div class="w-32">
                        <label class="block text-sm font-bold text-transparent mb-1 select-none">&nbsp;</label>
                        <button type="submit" class="w-32 h-8 bg-green-600 hover:bg-green-700 text-white font-semibold rounded text-xs transition">
                            Load Students
                        </button>
                    </div>

                    <!-- Reset Button -->
                    <div class="w-32">
                        <label class="block text-sm font-bold text-transparent mb-1 select-none">&nbsp;</label>
                        <a href="{{ route('student-skill-marks.index') }}"
                           class="w-32 h-8 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded text-xs transition flex items-center justify-center">
                            Reset
                        </a>
                    </div>

                </div>
            </form>

            <!-- Marks Entry Table -->
            @if(count($students))
                <form method="POST" action="{{ route('student-skill-marks.save') }}" id="marksForm">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">
                    <input type="hidden" name="exam_master_id" value="{{ request('exam_master_id') }}">
                    <input type="hidden" name="division_id" value="{{ request('division_id') }}">
                    <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                    <input type="hidden" name="max_marks" value="{{ (int) $maxMarks }}">
                    <input type="hidden" name="passing_marks" value="{{ (int) $passingMarks }}">

                    <!-- Info banner -->
                    <div class="bg-blue-50 border border-blue-200 rounded p-2 mb-3 text-xs">
                        <span class="font-semibold text-blue-700">Showing {{ count($students) }} students</span>
                        <span class="text-gray-700"> | {{ $examStandard->standard_name ?? '' }} - {{ request('division_id') ? \App\Models\Division::find(request('division_id'))->division_name : '' }}</span>
                        <span class="text-gray-700"> | Subject: <strong>{{ $skillSubjects->firstWhere('id', request('subject_id'))->subject_name ?? '' }}</strong></span>
                        <span class="text-gray-700"> | Max: <strong>{{ (int) $maxMarks }}</strong> | Passing: <strong>{{ (int) $passingMarks }}</strong></span>
                    </div>

                    <div class="border border-gray-200 rounded overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-blue-100">
                                    <tr>
                                        <th class="px-2 py-1.5 text-left font-bold text-gray-800 uppercase text-xs">GR NO</th>
                                        <th class="px-2 py-1.5 text-left font-bold text-gray-800 uppercase text-xs">ROLL NO</th>
                                        <th class="px-2 py-1.5 text-left font-bold text-gray-800 uppercase text-xs">STUDENT NAME</th>
                                        <th class="px-2 py-1.5 text-center font-bold text-gray-800 uppercase text-xs">MAX</th>
                                        <th class="px-2 py-1.5 text-center font-bold text-gray-800 uppercase text-xs">PASS</th>
                                        <th class="px-2 py-1.5 text-center font-bold text-gray-800 uppercase text-xs">OBTAINED</th>
                                        <th class="px-2 py-1.5 text-center font-bold text-gray-800 uppercase text-xs">GRADE</th>
                                        <th class="px-2 py-1.5 text-center font-bold text-gray-800 uppercase text-xs">P / A</th>
                                        <th class="px-2 py-1.5 text-center font-bold text-gray-800 uppercase text-xs">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($students as $student)
                                        @php
                                            $isAbsent = (int) ($student->is_absent ?? 0);
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-2 py-1 text-gray-700 whitespace-nowrap text-xs">{{ $student->regno ?? '-' }}</td>
                                            <td class="px-2 py-1 text-gray-700 whitespace-nowrap text-xs">{{ $student->rollno ?? '-' }}</td>
                                            <td class="px-2 py-1 text-gray-700 text-xs">{{ trim(($student->studname ?? '') . ' ' . ($student->fathername ?? '')) }}</td>

                                            <td class="px-2 py-1 text-center text-gray-700 font-medium text-xs">{{ (int) $maxMarks }}</td>
                                            <td class="px-2 py-1 text-center text-gray-700 font-medium text-xs">{{ (int) $passingMarks }}</td>

                                            <!-- Obtained Mark (digits only) -->
                                            <td class="px-2 py-1 text-center">
                                                <input type="text"
                                                       inputmode="numeric"
                                                       pattern="[0-9]*"
                                                       name="marks[{{ $student->Studentid }}][obtained]"
                                                       data-max="{{ (int) $maxMarks }}"
                                                       data-passing="{{ (int) $passingMarks }}"
                                                       value="{{ $student->marks_obtained !== null ? (int) $student->marks_obtained : '' }}"
                                                       class="no-spinner marks-input w-14 border border-gray-300 rounded px-1 py-0.5 text-xs text-center focus:ring-blue-500 focus:border-blue-500"
                                                       placeholder="0"
                                                       {{ $isAbsent ? 'disabled' : '' }}>
                                            </td>

                                            <!-- Grade -->
                                            <td class="px-2 py-1 text-center">
                                                <input type="text"
                                                       name="marks[{{ $student->Studentid }}][grade]"
                                                       value="{{ $student->grade ?? '' }}"
                                                       class="grade-input w-12 border border-gray-200 bg-gray-100 rounded px-1 py-0.5 text-xs text-center"
                                                       readonly>
                                            </td>

                                            <!-- Present / Absent Flip Button -->
                                            <td class="px-2 py-1 text-center">
                                                <button type="button"
                                                        data-student="{{ $student->Studentid }}"
                                                        class="toggle-status-btn skill-toggle-btn {{ $isAbsent ? 'skill-absent-btn' : 'skill-present-btn' }}">
                                                    <span class="dot"></span>
                                                    <span class="btn-label">{{ $isAbsent ? 'ABSENT' : 'PRESENT' }}</span>
                                                </button>
                                                <input type="hidden"
                                                       name="marks[{{ $student->Studentid }}][is_absent]"
                                                       value="{{ $isAbsent ? 1 : 0 }}"
                                                       class="absent-hidden">
                                            </td>

                                            <!-- Status -->
                                            <td class="px-2 py-1 text-center whitespace-nowrap status-cell font-semibold text-xs {{ $isAbsent ? 'text-red-600' : 'text-green-600' }}">
                                                {{ $isAbsent ? 'ABSENT' : 'PRESENT' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Save Marks Button -->
                    <div class="mt-3 flex justify-end">
                        <button type="submit" class="w-32 h-8 bg-green-600 hover:bg-green-700 text-white font-semibold rounded text-xs transition">
                            Save Marks
                        </button>
                    </div>
                </form>
            @elseif(request()->anyFilled(['academic_year_id', 'exam_master_id', 'division_id', 'subject_id']))
                <div class="mt-4 text-center py-8 bg-gray-50 rounded border border-dashed border-gray-300">
                    <h3 class="text-xs font-medium text-gray-900">No students found</h3>
                    <p class="text-xs text-gray-500 mt-1">No students from this class are allocated to this skill subject.</p>
                </div>
            @endif

        </div>
    </div>

    <!-- ============================================================
         CUSTOM STYLES
         ============================================================ -->
    <style>
        /* Hide number input spinners */
        .no-spinner::-webkit-outer-spin-button,
        .no-spinner::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .no-spinner {
            -moz-appearance: textfield;
            appearance: textfield;
        }

        /* Present / Absent Flip Button — always visible */
        .skill-toggle-btn {
            width: 128px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            border: 0;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.15s ease;
            white-space: nowrap;
            line-height: 1.2;
            padding: 0;
        }

        .skill-present-btn {
            background-color: #16a34a;
        }
        .skill-present-btn:hover {
            background-color: #15803d;
        }

        .skill-absent-btn {
            background-color: #dc2626;
        }
        .skill-absent-btn:hover {
            background-color: #b91c1c;
        }

        .skill-toggle-btn .dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #ffffff;
            opacity: 0.9;
            flex-shrink: 0;
        }
    </style>

    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // =========================================================
        // STRIP DECIMALS FROM ALL PRE-LOADED OBTAINED VALUES
        // =========================================================
        document.querySelectorAll('.marks-input').forEach(function (input) {
            if (input.value && input.value.includes('.')) {
                input.value = input.value.split('.')[0];
            }
        });

        // ---------------------------------------------------------
        // Grade calculator
        // ---------------------------------------------------------
        function gradeFromPercentage(p) {
            if (p >= 91) return 'A1';
            if (p >= 81) return 'A2';
            if (p >= 71) return 'B1';
            if (p >= 61) return 'B2';
            if (p >= 51) return 'C1';
            if (p >= 41) return 'C2';
            if (p >= 33) return 'D';
            if (p >= 21) return 'E1';
            if (p >= 1)  return 'E2';
            return 'F';
        }

        // Strip decimals FIRST, then strip any remaining non-digit
        function sanitize(input) {
            let val = input.value;
            if (val.includes('.')) {
                val = val.split('.')[0];
            }
            input.value = val.replace(/[^0-9]/g, '');
        }

        // ---------------------------------------------------------
        // Update row (grade + status)
        // ---------------------------------------------------------
        function updateRow(input) {
            const row       = input.closest('tr');
            const gradeEl   = row.querySelector('.grade-input');
            const statusEl  = row.querySelector('.status-cell');
            const hiddenAbs = row.querySelector('.absent-hidden');
            const max       = parseInt(input.dataset.max) || 0;
            const passing   = parseInt(input.dataset.passing) || 0;
            const isAbsent  = hiddenAbs.value === '1';

            if (isAbsent) {
                gradeEl.value = 'AB';
                statusEl.textContent = 'ABSENT';
                statusEl.className = 'px-2 py-1 text-center whitespace-nowrap status-cell font-semibold text-xs text-red-600';
                return;
            }

            const val = input.value.trim();

            if (val === '' || isNaN(val)) {
                gradeEl.value = '';
                statusEl.textContent = 'PRESENT';
                statusEl.className = 'px-2 py-1 text-center whitespace-nowrap status-cell font-semibold text-xs text-green-600';
                return;
            }

            const marks = parseInt(val);
            const pct   = max > 0 ? (marks / max) * 100 : 0;
            gradeEl.value = gradeFromPercentage(pct);

            if (marks >= passing) {
                statusEl.textContent = 'PASS';
                statusEl.className = 'px-2 py-1 text-center whitespace-nowrap status-cell font-semibold text-xs text-green-600';
            } else {
                statusEl.textContent = 'FAIL';
                statusEl.className = 'px-2 py-1 text-center whitespace-nowrap status-cell font-semibold text-xs text-red-600';
            }
        }

        // ---------------------------------------------------------
        // Bind marks inputs
        // ---------------------------------------------------------
        document.querySelectorAll('.marks-input').forEach(function (input) {
            input.addEventListener('input', function () {
                sanitize(this);
                updateRow(this);
            });
            input.addEventListener('blur', function () {
                sanitize(this);
                const max = parseInt(this.dataset.max) || 0;
                const val = parseInt(this.value);
                if (!isNaN(val) && max > 0 && val > max) {
                    this.value = max;
                }
                updateRow(this);
            });
            updateRow(input);
        });

        // ---------------------------------------------------------
        // Filter inputs — digits only
        // ---------------------------------------------------------
        document.querySelectorAll('#filter_max_marks, #filter_passing_marks').forEach(function (input) {
            input.addEventListener('input', function () {
                sanitize(this);
            });
        });

        // ---------------------------------------------------------
        // Toggle Present / Absent (custom CSS classes)
        // ---------------------------------------------------------
        document.querySelectorAll('.toggle-status-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {

                const row       = this.closest('tr');
                const hiddenEl  = row.querySelector('.absent-hidden');
                const input     = row.querySelector('.marks-input');
                const labelEl   = this.querySelector('.btn-label');
                const isPresent = hiddenEl.value === '0';

                if (isPresent) {
                    // ---------- Present → Absent ----------
                    Swal.fire({
                        title: 'Mark as Absent?',
                        text: 'Are you sure this student is absent for this exam?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, Absent',
                        cancelButtonText: 'Cancel',
                        customClass: { popup: 'text-sm' }
                    }).then((result) => {
                        if (result.isConfirmed) {

                            hiddenEl.value = '1';
                            input.value = '';
                            input.disabled = true;

                            btn.classList.remove('skill-present-btn');
                            btn.classList.add('skill-absent-btn');
                            labelEl.textContent = 'ABSENT';

                            updateRow(input);

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Marked as Absent',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    });
                } else {
                    // ---------- Absent → Present ----------
                    Swal.fire({
                        title: 'Mark as Present?',
                        text: 'Change status back to Present?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#16a34a',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, Present',
                        cancelButtonText: 'Cancel',
                        customClass: { popup: 'text-sm' }
                    }).then((result) => {
                        if (result.isConfirmed) {

                            hiddenEl.value = '0';
                            input.disabled = false;

                            btn.classList.remove('skill-absent-btn');
                            btn.classList.add('skill-present-btn');
                            labelEl.textContent = 'PRESENT';

                            updateRow(input);

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Marked as Present',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    });
                }

            });
        });

    });
    </script>
</x-app-layout>