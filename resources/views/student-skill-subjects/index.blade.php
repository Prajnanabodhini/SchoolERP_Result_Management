<x-app-layout>
    <div class="w-full">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">

            <div class="mb-6">
                <h2 class="text-xl font-bold text-blue-700 uppercase">
                    STUDENT SKILL SUBJECT ALLOCATION
                </h2>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded mb-6 text-base">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Filter Form -->
            <form method="GET" action="{{ route('student-skill-subject-allocation.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

                    <!-- Academic Year -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-1">
                            Academic Year <span class="text-red-500">*</span>
                        </label>
                        <select name="academic_year_id" class="w-full border border-gray-300 rounded px-3 h-10 text-base focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Year</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Standards (Multi-Checkbox) -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-1">
                            Standards <span class="text-red-500">*</span>
                        </label>
                        <div class="flex flex-wrap gap-3 border border-gray-300 rounded px-3 h-10 items-center">
                            @foreach($standards as $standard)
                                <label class="flex items-center text-base">
                                    <input type="checkbox" name="standard_ids[]" value="{{ $standard->id }}"
                                        {{ in_array($standard->id, request('standard_ids', [])) ? 'checked' : '' }}
                                        class="mr-1">
                                    {{ $standard->standard_name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Divisions (Multi-Checkbox) -->
                    <div>
                        <label class="block text-base font-medium text-gray-700 mb-1">
                            Divisions <span class="text-red-500">*</span>
                        </label>
                        <div class="flex flex-wrap gap-3 border border-gray-300 rounded px-3 h-10 items-center">
                            @foreach($divisions as $division)
                                <label class="flex items-center text-base">
                                    <input type="checkbox" name="division_ids[]" value="{{ $division->id }}"
                                        {{ in_array($division->id, request('division_ids', [])) ? 'checked' : '' }}
                                        class="mr-1">
                                    {{ $division->division_name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                </div>

                <div class="flex gap-2 mb-6">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 h-10 rounded text-base transition">
                        Load Students
                    </button>
                    <a href="{{ route('student-skill-subject-allocation.index') }}"
                       class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-6 h-10 rounded text-base transition flex items-center">
                        Reset
                    </a>
                </div>
            </form>

            @if(count($students))

                <!-- BULK ALLOCATION PANEL -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <form method="POST" action="{{ route('student-skill-subject-allocation.bulk-allocate') }}" class="flex flex-wrap items-end gap-4">
                        @csrf
                        <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">
                        @foreach($students as $student)
                            <input type="hidden" name="student_ids[]" value="{{ $student->Studentid }}">
                        @endforeach

                        <div class="flex-1 min-w-[300px]">
                            <label class="block text-base font-medium text-gray-700 mb-1">
                                Bulk Assign a Skill Subject to All {{ count($students) }} Students
                            </label>
                            <select name="subject_id" required
                                    class="w-full border border-gray-300 rounded px-3 h-10 text-base focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Skill Subject</option>
                                @foreach($skillSubjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 h-10 rounded text-base transition"
                                onclick="return confirm('Assign this subject to ALL {{ count($students) }} students?')">
                            Apply to All
                        </button>
                    </form>
                </div>

                <!-- Individual Assignment Table -->
                <form method="POST" action="{{ route('student-skill-subject-allocation.save') }}">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">

                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-full divide-y divide-gray-200 text-base">
                                <thead class="bg-blue-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-800 uppercase">GR NO</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-800 uppercase">ROLL NO</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-800 uppercase">STUDENT NAME</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-800 uppercase">CLASS</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-800 uppercase">SKILL SUBJECT</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($students as $student)
                                        <tr class="hover:bg-gray-50 transition duration-150">
                                            <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $student->regno }}</td>
                                            <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $student->rollno }}</td>
                                            <td class="px-4 py-3 text-gray-700">
                                                {{ trim(($student->studname ?? '') . ' ' . ($student->fathername ?? '')) }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $student->class_label }}</td>
                                            <td class="px-4 py-3 text-gray-700">
                                                <select name="skill_subject[{{ $student->Studentid }}]"
                                                        class="w-full border border-gray-300 rounded px-3 py-1.5 text-base focus:ring-blue-500 focus:border-blue-500 max-w-md">
                                                    <option value="">Select Skill Subject</option>
                                                    @foreach($skillSubjects as $subject)
                                                        <option value="{{ $subject->id }}" {{ $student->selected_subject == $subject->id ? 'selected' : '' }}>
                                                            {{ $subject->subject_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 h-10 rounded text-base transition">
                            Save Allocation
                        </button>
                    </div>
                </form>

            @elseif(request()->anyFilled(['academic_year_id', 'standard_ids', 'division_ids']))
                <div class="mt-6 text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <h3 class="mt-2 text-base font-medium text-gray-900">No students found</h3>
                    <p class="mt-1 text-base text-gray-500">Try adjusting your filters.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>