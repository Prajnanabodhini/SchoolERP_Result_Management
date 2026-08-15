@extends('layouts.app')

@section('content')

<div class="erp-page">

    <div class="erp-card">

        <h2 class="text-xl font-bold text-blue-700 mb-4">
            Result Register
        </h2>

        <form method="POST"
              action="{{ route('administrator.result-register.search') }}">

            @csrf

            <div class="flex items-center gap-3 flex-wrap">

                <label class="font-semibold">
                    Exam
                </label>

                <select name="exam_master_id"
                        class="border rounded px-2 py-1" style="width:200px;"
                        required>

                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}">
                            {{ $exam->exam_name }}
                        </option>
                    @endforeach

                </select>

                <label class="font-semibold">
                    Standard
                </label>

                <select name="standard_id"
                        class="border rounded px-2 py-1 w-40"
                        required>

                    @foreach($standards as $standard)
                        <option value="{{ $standard->id }}">
                            {{ $standard->standard_name }}
                        </option>
                    @endforeach

                </select>

                <label class="font-semibold">
                    Division
                </label>

                <select name="division_id"
                        class="border rounded px-2 py-1" style="width:100px;"
                        required>

                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}">
                            {{ $division->division_name }}
                        </option>
                    @endforeach

                </select>

                <button type="submit"
                        class="erp-btn erp-btn-save">
                    Search
                </button>

                @if(count($results))
                    <span class="font-bold text-blue-700">
                        Total Students : {{ count($results) }}
                    </span>
                @endif

            </div>

        </form>

    </div>

    @if(count($results))

    <div class="erp-card mt-4">

        <table class="w-full border border-gray-300 text-sm">

            <thead class="bg-blue-100">

                <tr>

                    <th class="border p-2 text-center">
                        Rank
                    </th>

                    <th class="border p-2 text-center">
                        Student ID
                    </th>

                    <th class="border p-2 text-center">
                        Student Name
                    </th>
                  
                    <th class="border p-2 text-center">
                        Total Marks
                    </th>

                    <th class="border p-2 text-center">
                        Obtained Marks
                    </th>

                    <th class="border p-2 text-center">
                        Percentage
                    </th>

                    <th class="border p-2 text-center">
                        Grade
                    </th>

                    <th class="border p-2 text-center">
                        Result
                    </th>

                </tr>

            </thead>

            <tbody>

                @foreach($results as $row)

                <tr class="{{ $row->rank <= 3 ? 'bg-yellow-100' : 'hover:bg-yellow-50' }}">

                    <td class="border p-2 text-center font-semibold">
                        {{ $row->rank }}
                    </td>

                    <td class="border p-2 text-center">
                        {{ $row->Studentid }}
                    </td>

                    <td class="border p-2">
                        {{ $row->studname }}
                    </td>

                    <td class="border p-2 text-right">
                        {{ number_format($row->total_max_marks, 2) }}
                    </td>

                    <td class="border p-2 text-right">
                        {{ number_format($row->total_obtained_marks, 2) }}
                    </td>

                    <td class="border p-2 text-right font-semibold">
                        {{ number_format($row->percentage, 2) }}
                    </td>

                    <td class="border p-2 text-center font-semibold">
                        {{ $row->grade }}
                    </td>

                    <td class="border p-2 text-center">

                        @if($row->result == 'PASS')

                            <span class="text-green-700 font-bold">
                                PASS
                            </span>

                        @else

                            <span class="text-red-700 font-bold">
                                FAIL
                            </span>

                        @endif

                    </td>

                </tr>

                @endforeach

            </tbody>

        </table>

    </div>

    @endif

</div>

@endsection