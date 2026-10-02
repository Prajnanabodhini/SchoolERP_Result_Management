<x-app-layout>

    <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

        <h2 class="text-xl font-bold text-blue-600 mb-4">
            Student Master
        </h2>

        {{-- ============================= --}}
        {{-- Filters                        --}}
        {{-- ============================= --}}
        <form method="GET" action="{{ route('students.index') }}">

            <div class="flex flex-wrap items-end gap-3 mb-4">

                <div class="flex items-center gap-2">
                    <label class="font-semibold whitespace-nowrap">
                        Academic Year
                    </label>
                    <select name="academic_year_id"
                            class="border rounded px-2 py-1 w-40">
                        <option value="">Select Year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}"
                                {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->year_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="font-semibold whitespace-nowrap">
                        Standard
                    </label>
                    <select name="standard_id"
                            class="border rounded px-2 py-1 w-48">
                        <option value="">Select Standard</option>
                        @foreach($standards as $standard)
                            <option value="{{ $standard->id }}"
                                {{ request('standard_id') == $standard->id ? 'selected' : '' }}>
                                {{ $standard->standard_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="font-semibold whitespace-nowrap">
                        Division
                    </label>
                    <select name="division_id"
                            class="border rounded px-2 py-1 w-32">
                        <option value="">Select Division</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}"
                                {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                {{ $division->division_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="erp-btn erp-btn-save">
                    Search
                </button>

                @if($students->count())
                    <span class="text-blue-600 font-semibold ml-auto">
                        Total Students : {{ $students->count() }}
                    </span>
                @endif

            </div>

        </form>

        {{-- ============================= --}}
        {{-- Student Table                  --}}
        {{-- ============================= --}}
        <table class="w-full border border-gray-300 text-sm">

            <thead class="bg-blue-100">
                <tr>
                    <th class="border p-2">Admission No</th>
                    <th class="border p-2">Roll No</th>
                    <th class="border p-2">Student Name</th>
                    <th class="border p-2">Caste</th>
                    <th class="border p-2">Mobile</th>
                    <th class="border p-2">Date of Birth</th>
                    <th class="border p-2">Admission Date</th>
                    <th class="border p-2">Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($students as $student)

                    <tr class="hover:bg-gray-50">

                        <td class="border p-2">
                            {{ $student->regno }}
                        </td>

                        <td class="border p-2">
                            {{ $student->rollno }}
                        </td>

                        <td class="border p-2">
                            {{ $student->full_name }}
                        </td>

                        {{-- Caste --}}
                        <td class="border p-2">
                            {{ trim($student->caste ?? '') !== '' ? trim($student->caste) : '-' }}
                        </td>

                        <td class="border p-2">
                            {{ $student->fathermobile ?? '-' }}
                        </td>

                        {{-- Date of Birth --}}
                        <td class="border p-2 text-center">
                            @php
                                $dob = trim($student->birthdate ?? '');
                                $dobFormatted = $dob;

                                try {
                                    if (str_contains($dob, '/')) {
                                        $dobFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $dob)->format('d-m-Y');
                                    } elseif (str_contains($dob, '-')) {
                                        $dobFormatted = \Carbon\Carbon::createFromFormat('d-m-Y', $dob)->format('d-m-Y');
                                    }
                                } catch (\Exception $e) {
                                    $dobFormatted = $dob;
                                }
                            @endphp
                            {{ $dobFormatted }}
                        </td>

                        {{-- Admission Date --}}
                        <td class="border p-2 text-center">
                            @if(!empty($student->admitdate))
                                @php
                                    $admit = trim($student->admitdate);
                                    $admitFormatted = $admit;

                                    try {
                                        if (str_contains($admit, '/')) {
                                            $admitFormatted = \Carbon\Carbon::createFromFormat('d/m/Y', $admit)->format('d-m-Y');
                                        } elseif (str_contains($admit, '-')) {
                                            $admitFormatted = \Carbon\Carbon::createFromFormat('d-m-Y', $admit)->format('d-m-Y');
                                        }
                                    } catch (\Exception $e) {
                                        $admitFormatted = $admit;
                                    }
                                @endphp
                                {{ $admitFormatted }}
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="border p-2 text-center">
                            @if(strtoupper(trim($student->status ?? 'ACTIVE')) === 'ACTIVE')
                                <span class="text-green-600 font-bold">Active</span>
                            @else
                                <span class="text-red-600 font-bold">Left</span>
                            @endif
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="border p-4 text-center text-gray-500">
                            No Students Found
                        </td>
                    </tr>

                @endforelse
            </tbody>

        </table>

    </div>

</x-app-layout>