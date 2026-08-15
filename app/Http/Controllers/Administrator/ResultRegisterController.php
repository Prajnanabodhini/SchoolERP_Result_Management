<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;

class ResultRegisterController extends Controller
{
    public function index()
    {
        $exams = ExamMaster::all();

        $standards = Standard::all();

        $divisions = Division::all();

        $results = collect();
// dd($results->count(), $results->take(5));
        return view(
            'administrator.result-register.index',
            compact(
                'exams',
                'standards',
                'divisions',
                'results'
            )
        );
    }

    public function search(Request $request)
    {
        $exams = ExamMaster::all();

        $standards = Standard::all();

        $divisions = Division::all();
$results =
    \DB::table('student_results as sr')

    ->join(
        'substudentmst as ss',
        'ss.Studentid',
        '=',
        'sr.student_id'
    )

    ->join(
        'feemststudent as fs',
        'fs.Studentid',
        '=',
        'sr.student_id'
    )

    ->where(
        'sr.exam_master_id',
        $request->exam_master_id
    )

    ->where(
        'sr.standard_id',
        $request->standard_id
    )

    ->where(
        'sr.division_id',
        $request->division_id
    )

    ->orderBy('sr.rank')

    ->select(
        'sr.*',
        'ss.Studentid',
        'fs.studname'
    )

    ->get();
    
        // $results =
        //     \DB::table('student_results as sr')
        //     ->join(
        //         'substudentmst as s',
        //         's.Studentid',
        //         '=',
        //         'sr.student_id'
        //     )
        //     ->where(
        //         'sr.exam_master_id',
        //         $request->exam_master_id
        //     )
        //     ->where(
        //         'sr.standard_id',
        //         $request->standard_id
        //     )
        //     ->where(
        //         'sr.division_id',
        //         $request->division_id
        //     )
        //     ->orderBy('sr.rank')
        //     // ->select(
        //     //     'sr.*',
        //     //     's.student_name',
        //     //     's.admission_no'
        //     // )
        //     ->select(
//     'sr.*',
//     's.regno',
//     's.Studentid'
// )
//             ->get();

        return view(
            'administrator.result-register.index',
            compact(
                'exams',
                'standards',
                'divisions',
                'results'
            )
        );
    }
}