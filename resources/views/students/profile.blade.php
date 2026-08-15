<x-app-layout>

<div class="max-w-7xl mx-auto py-4 px-4">

    <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">

        <h2 class="text-2xl font-bold text-blue-600 mb-6">
            Student Complete Profile
        </h2>

        <form method="GET"
              action="{{ route('student-profile.index') }}">

            <div class="flex items-center gap-3 mb-6">

                <label class="font-semibold whitespace-nowrap">
                    Search Student
                </label>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="student_name / admission_no / registration_no / father_mobile"
                       class="border rounded px-3 py-2 w-96">

                <button type="submit"
                        class="erp-btn erp-btn-save">
                    Search
                </button>

            </div>

        </form>

        @if($student)

        <div class="grid grid-cols-2 gap-6">

            {{-- Left Column --}}
            <div>

                <table class="w-full border border-gray-300">

                    <tbody>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            old_student_id
                        </td>
                        <td class="border p-2">
                            {{ $student->old_student_id }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            registration_no
                        </td>
                        <td class="border p-2">
                            {{ $student->registration_no }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            admission_no
                        </td>
                        <td class="border p-2">
                            {{ $student->admission_no }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            student_name
                        </td>
                        <td class="border p-2">
                            {{ $student->student_name }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            gender
                        </td>
                        <td class="border p-2">
                            {{ $student->gender }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            date_of_birth
                        </td>
                        <td class="border p-2">
                            {{ $student->date_of_birth }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            aadhaar_no
                        </td>
                        <td class="border p-2">
                            {{ $student->aadhaar_no }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            religion
                        </td>
                        <td class="border p-2">
                            {{ $student->religion }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            nationality
                        </td>
                        <td class="border p-2">
                            {{ $student->nationality }}
                        </td>
                    </tr>

                    </tbody>

                </table>

            </div>

            {{-- Right Column --}}
            <div>

                <table class="w-full border border-gray-300">

                    <tbody>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            father_name
                        </td>
                        <td class="border p-2">
                            {{ $student->father_name }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            mother_name
                        </td>
                        <td class="border p-2">
                            {{ $student->mother_name }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            father_mobile
                        </td>
                        <td class="border p-2">
                            {{ $student->father_mobile }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            mother_mobile
                        </td>
                        <td class="border p-2">
                            {{ $student->mother_mobile }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            admission_date
                        </td>
                        <td class="border p-2">
                            {{ $student->admission_date }}
                        </td>
                    </tr>

                    <tr>
                        <td class="border p-2 font-semibold bg-gray-100">
                            saral_id
                        </td>
                        <td class="border p-2">
                            {{ $student->saral_id }}
                        </td>
                    </tr>

                    </tbody>

                </table>

            </div>

        </div>

        {{-- Address Section --}}

        <div class="mt-6">

            <table class="w-full border border-gray-300">

                <tr>

                    <td class="border p-2 font-semibold bg-gray-100 w-48">
                        address
                    </td>

                    <td class="border p-2">
                        {{ $student->address }}
                    </td>

                </tr>

            </table>

        </div>

        @elseif(request('search'))

            <div class="bg-red-100 text-red-700 p-3 rounded">
                Student Not Found
            </div>

        @else

            <div class="bg-blue-100 text-blue-700 p-3 rounded">
                Enter student_name, admission_no, registration_no or father_mobile and click Search.
            </div>

        @endif

    </div>

</div>

</x-app-layout>