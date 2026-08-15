@extends('layouts.app')

@section('content')

<div class="erp-card">

    <h2 class="text-xl font-bold mb-4">
        Marks Audit Trail
    </h2>

    <form method="GET">

        <div class="grid grid-cols-3 gap-3 mb-4">

            <select
                name="exam_master_id"
                class="form-control">

                <option value="">
                    All Exams
                </option>

                @foreach($exams as $exam)

                    <option
                        value="{{ $exam->id }}"
                        {{ request('exam_master_id') == $exam->id ? 'selected' : '' }}>

                        {{ $exam->exam_name }}

                    </option>

                @endforeach

            </select>

            <select
                name="subject_id"
                class="form-control">

                <option value="">
                    All Subjects
                </option>

                @foreach($subjects as $subject)

                    <option
                        value="{{ $subject->id }}"
                        {{ request('subject_id') == $subject->id ? 'selected' : '' }}>

                        {{ $subject->subject_name }}

                    </option>

                @endforeach

            </select>

            <button
                class="btn btn-primary">

                Search

            </button>

        </div>

    </form>

    <table class="table-auto w-full border">

        <thead>

            <tr>

                <th>ID</th>
                <th>Student</th>
                <th>Exam</th>
                <th>Subject</th>
                <th>Action</th>
                <th>Old</th>
                <th>New</th>
                <th>User</th>
                <th>Date</th>

            </tr>

        </thead>

        <tbody>

            @foreach($logs as $log)

            <tr>

                <td>{{ $log->id }}</td>

                <td>{{ $log->student_id }}</td>

                <td>{{ $log->exam_master_id }}</td>

                <td>{{ $log->subject_id }}</td>

                <td>{{ $log->action }}</td>

                <td>
                    T:{{ $log->old_theory_marks }}
                    O:{{ $log->old_oral_marks }}
                    P:{{ $log->old_practical_marks }}
                </td>

                <td>
                    T:{{ $log->new_theory_marks }}
                    O:{{ $log->new_oral_marks }}
                    P:{{ $log->new_practical_marks }}
                </td>

                <td>{{ $log->teacher_id }}</td>

                <td>{{ $log->created_at }}</td>

            </tr>

            @endforeach

        </tbody>

    </table>

    <div class="mt-3">

        {{ $logs->links() }}

    </div>

</div>

@endsection