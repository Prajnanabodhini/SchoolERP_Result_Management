@extends('layouts.app')

@section('content')

<div class="p-4">

    <div class="bg-white shadow rounded p-4">

        <h2 class="text-xl font-bold text-amber-800">
            Result Generation
        </h2>


        {{-- SUCCESS --}}
        @if(session('success'))

            <div class="mt-3 p-3 bg-green-100 border border-green-400 text-green-800 rounded">

                {{ session('success') }}

            </div>

        @endif


        {{-- ERROR --}}
        @if(session('error'))

            <div class="mt-3 p-3 bg-red-100 border border-red-400 text-red-800 rounded">

                {{ session('error') }}

            </div>

        @endif


        {{-- VALIDATION ERRORS --}}
        @if($errors->any())

            <div class="mt-3 p-3 bg-red-100 border border-red-400 text-red-800 rounded">

                <ul style="margin:0;padding-left:18px;">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

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
                flex-wrap:nowrap;
                width:100%;
            ">


                {{-- =====================================================
                     ACADEMIC YEAR
                ====================================================== --}}

                <div style="flex:0 0 auto;">

                    <label style="
                        font-size:12px;
                        font-weight:600;
                        display:block;
                        margin-bottom:4px;
                    ">
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

                        <option value="">
                            Select
                        </option>

                        @foreach($academicYears as $year)

                            <option
                                value="{{ $year->id }}"
                                {{ old('academic_year_id') == $year->id ? 'selected' : '' }}
                            >
                                {{ $year->year_name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =====================================================
                     EXAM
                ====================================================== --}}

                <div style="flex:0 0 auto;">

                    <label style="
                        font-size:12px;
                        font-weight:600;
                        display:block;
                        margin-bottom:4px;
                    ">
                        Exam
                    </label>

                    <select
                        name="exam_master_id"
                        required
                        style="
                            width:300px;
                            height:32px;
                            font-size:13px;
                            padding:2px 4px;
                        "
                    >

                        <option value="">
                            Select Exam
                        </option>

                        @foreach($exams as $exam)

                            <option
                                value="{{ $exam->id }}"
                                {{ old('exam_master_id') == $exam->id ? 'selected' : '' }}
                            >
                                {{ $exam->exam_name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =====================================================
                     DIVISION
                ====================================================== --}}

                <div style="flex:0 0 auto;">

                    <label style="
                        font-size:12px;
                        font-weight:600;
                        display:block;
                        margin-bottom:4px;
                    ">
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

                        <option value="">
                            Select
                        </option>

                        @foreach($divisions as $division)

                            <option
                                value="{{ $division->id }}"
                                {{ old('division_id') == $division->id ? 'selected' : '' }}
                            >
                                {{ $division->division_name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- =====================================================
                     GENERATE
                ====================================================== --}}

                <div style="flex:0 0 auto;">

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