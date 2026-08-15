<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\ErpStudentMaster;
use Carbon\Carbon;

class ErpSyncController extends Controller
{

    public function syncStudents($year=2026)
    {

        /*
        MSSQL Connection
        sqlsrv_olderp
        */

        $students = DB::connection('sqlsrv_olderp')
        ->select("

        SELECT

        ss.yearid AS academic_year_id,

        ss.Studentid AS old_student_id,

        ss.regno AS gr_no,

        ss.rollno AS roll_no,

        fs.studname AS student_name,

        fs.fathername AS father_name,

        fs.fathermobile AS mobile_no,

        ss.standardid AS old_standard_id,

        ss.divisionid AS old_division_id,

        sm.standard AS standard_code,

        sm.name AS standard_name,

        d.division AS division_name


        FROM SubStudentMst ss


        INNER JOIN FeeMstStudent fs

        ON fs.Studentid = ss.Studentid


        INNER JOIN StandardMst sm

        ON sm.standardid = ss.standardid

        AND sm.yearid = ss.yearid


        INNER JOIN DivisionMst d

        ON d.divisionid = ss.divisionid

        AND d.standardid = ss.standardid

        AND d.yearid = ss.yearid


        WHERE ss.yearid = ?

        ",[$year]);


        foreach($students as $student)
        {


            ErpStudentMaster::updateOrCreate(

            [

                'academic_year_id'=>$student->academic_year_id,

                'old_student_id'=>$student->old_student_id

            ],

            [

                'gr_no'=>$student->gr_no,

                'roll_no'=>$student->roll_no,

                'student_name'=>$student->student_name,

                'father_name'=>$student->father_name,

                'mobile_no'=>$student->mobile_no,


                'old_standard_id'=>$student->old_standard_id,

                'old_division_id'=>$student->old_division_id,


                'standard_code'=>$student->standard_code,

                'standard_name'=>$student->standard_name,

                'division_name'=>$student->division_name,


                'sync_date'=>Carbon::now()

            ]);

        }


        return count($students)." Students Synced Successfully";


    }

}