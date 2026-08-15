
@extends('layouts.app')

@section('content')

<div class="p-4">


<div class="bg-white shadow rounded p-4">

    <h2 class="text-xl font-bold text-amber-800">
        Result Generation
    </h2>

    @if(session('success'))
        <div class="mt-3 p-3 bg-green-100 border border-green-400 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form
    method="POST"
     action="{{ route('administrator.result-generation.generate') }}"
    class="mt-4"
>
    @csrf

    <div style="
        display:flex;
        align-items:end;
        gap:10px;
        flex-wrap:wrap;
    ">

        <div>
            <label style="font-size:12px;font-weight:600;display:block;">
                Academic Year
            </label>

            <select
                name="academic_year_id"
                required
                style="
                    width:150px;
                    height:32px;
                    font-size:13px;
                    padding:2px 4px;
                "
            >
                <option value="">Select</option>

                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">
                        {{ $year->year_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="font-size:12px;font-weight:600;display:block;">
                Exam
            </label>

            <select
                name="exam_master_id"
                required
                style="
                    width:150px;
                    height:32px;
                    font-size:13px;
                    padding:2px 4px;
                "
            >
                @foreach($exams as $exam)
                    <option value="{{ $exam->id }}">
                        {{ $exam->exam_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="font-size:12px;font-weight:600;display:block;">
                Standard
            </label>

            <select
                name="standard_id"
                required
                style="
                    width:180px;
                    height:32px;
                    font-size:13px;
                    padding:2px 4px;
                "
            >
                @foreach($standards as $standard)
                    <option value="{{ $standard->id }}">
                        {{ $standard->standard_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="font-size:12px;font-weight:600;display:block;">
                Division
            </label>

            <select
                name="division_id"
                required
                style="
                    width:100px;
                    height:32px;
                    font-size:13px;
                    padding:2px 4px;
                "
            >
                @foreach($divisions as $division)
                    <option value="{{ $division->id }}">
                        {{ $division->division_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <button
                type="submit"
                class="erp-btn erp-btn-save"
                style="height:32px;"
            >
                Generate Result
            </button>
        </div>

    </div>

</form>
</div>


</div>

@endsection
