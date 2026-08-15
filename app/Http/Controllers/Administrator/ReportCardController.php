<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
use Illuminate\Support\Facades\DB;
use App\Helpers\StudentHelper;
use App\Models\AcademicYear;

class ReportCardController extends Controller
{
    public function print(
    $studentId,
    $examId,
    $yearId
)
{
    $report =
        DB::table('student_results as sr')
        ->join(
            'feemststudent as fs',
            'fs.Studentid',
            '=',
            'sr.student_id'
        )
        ->leftJoin(
            'substudentmst as ss',
            'ss.Studentid',
            '=',
            'sr.student_id'
        )
        ->join(
            'exam_masters as em',
            'em.id',
            '=',
            'sr.exam_master_id'
        )
        ->join(
            'standards as st',
            'st.id',
            '=',
            'sr.standard_id'
        )
        ->join(
            'divisions as dv',
            'dv.id',
            '=',
            'sr.division_id'
        )
        ->join(
            'academic_years as ay',
            'ay.id',
            '=',
            'sr.academic_year_id'
        )
        ->where('sr.student_id',$studentId)
        ->where('sr.exam_master_id',$examId)
        ->where('sr.academic_year_id',$yearId)
        ->select(
            'sr.*',
            'fs.studname as full_student_name',
            'ss.rollno',
            'em.exam_name',
            'st.standard_name',
            'dv.division_name',
            'ay.year_name'
        )
        ->first();

    if(!$report){
        abort(404);
    }

    $subjects =
        DB::table('student_marks as sm')
        ->join(
            'subjects as s',
            's.id',
            '=',
            'sm.subject_id'
        )
        ->where('sm.student_id',$studentId)
        ->where('sm.exam_master_id',$examId)
        ->select(
            's.subject_name',
            DB::raw('MAX(sm.theory_max_marks) as max_marks'),
            DB::raw('MAX(sm.theory_passing_marks) as passing_marks'),
            DB::raw('MAX(sm.theory_obtained_marks) as obtained_marks')
        )
        ->groupBy('s.subject_name')
        ->orderBy('s.subject_name')
        ->get();

    return view(
        'report-card.print',
        compact(
            'report',
            'subjects'
        )
    );
}
    public function index()
    {
        $exams = ExamMaster::all();

        $standards = Standard::all();

        $divisions = Division::all();

        $students = collect();

        $report = null;

        $subjects = collect();


        $academicYears =
            AcademicYear::orderByDesc('year_name')
            ->get();

        return view(
            'report-card.index',
            compact(
                'academicYears',
                'exams',
                'standards',
                'divisions',
                'students',
                'report',
                'subjects'
            )
        );
    }

    public function search(Request $request)
    {
        $error = null;
        $exams = ExamMaster::all();

        $standards = Standard::all();

        $divisions = Division::all();

        $academicYears =
            AcademicYear::orderByDesc('year_name')
            ->get();

        $students = collect();

        $report = null;

        $subjects = collect();

        if (
            $request->filled('academic_year_id') &&
            $request->filled('standard_id') &&
            $request->filled('division_id')
        ) {

            $academicYear =
                AcademicYear::find(
                    $request->academic_year_id
                );

            if ($academicYear) {

                $yearId =
                    $academicYear->old_year_id
                    ?? substr(
                        $academicYear->year_name,
                        0,
                        4
                    );

                    
                $students =
                    StudentHelper::getStudentsDirectERP(
                        $yearId,
                        $request->standard_id,
                        $request->division_id
                    );
            }
        }

        return view(
            'report-card.index',
            compact(
                'academicYears',
                'exams',
                'standards',
                'divisions',
                'students',
                'report',
                'subjects'
            )
        )->with([
            'academic_year_id' => $request->academic_year_id,
            'exam_master_id'   => $request->exam_master_id,
            'standard_id'      => $request->standard_id,
            'division_id'      => $request->division_id,
        ]);
    }

    public function show(Request $request)
    {
        set_time_limit(300);
        DB::connection()->enableQueryLog();
        $exams = ExamMaster::all();

        $standards = Standard::all();

        $divisions = Division::all();

        $academicYears =
            AcademicYear::orderByDesc('year_name')
            ->get();

        $students = collect();

        if (
            $request->academic_year_id &&
            $request->standard_id &&
            $request->division_id
        ) {

            $academicYear =
                AcademicYear::find(
                    $request->academic_year_id
                );

            if ($academicYear) {

                $yearId =
                    $academicYear->old_year_id
                    ?? substr(
                        $academicYear->year_name,
                        0,
                        4
                    );

                $students =
                    StudentHelper::getStudentsDirectERP(
                        $yearId,
                        $request->standard_id,
                        $request->division_id
                    );
            }
        }

        $report =
            DB::table('student_results as sr')
            ->join(
                'feemststudent as fs',
                'fs.Studentid',
                '=',
                'sr.student_id'
            )
            ->leftJoin(
                'substudentmst as ss',
                'ss.Studentid',
                '=',
                'sr.student_id'
            )
            ->join(
                'exam_masters as em',
                'em.id',
                '=',
                'sr.exam_master_id'
            )
            ->join(
                'standards as st',
                'st.id',
                '=',
                'sr.standard_id'
            )
            ->join(
                'divisions as dv',
                'dv.id',
                '=',
                'sr.division_id'
            )
            ->join(
                'academic_years as ay',
                'ay.id',
                '=',
                'sr.academic_year_id'
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
            ->where(
                'sr.student_id',
                $request->student_id
            )
            ->select(
                'sr.*',
                'fs.studname as full_student_name',
                'ss.rollno',
                'em.exam_name',
                'st.standard_name',
                'dv.division_name',
                'ay.year_name'
            )
            ->first();

            if (!$report) {

    $subjects = collect();

    return view(
        'report-card.index',
        compact(
            'academicYears',
            'exams',
            'standards',
            'divisions',
            'students',
            'report',
            'subjects'
        )
    )->with([
        'error'            => 'Report Card is not generated for the selected student.',
        'academic_year_id' => $request->academic_year_id,
        'exam_master_id'   => $request->exam_master_id,
        'standard_id'      => $request->standard_id,
        'division_id'      => $request->division_id,
    ]);
}

        $subjects = collect();

        if ($report) {

            $subjects =
    DB::table('student_marks as sm')
    ->join(
        'subjects as s',
        's.id',
        '=',
        'sm.subject_id'
    )
    ->where(
        'sm.student_id',
        $request->student_id
    )
    ->where(
        'sm.exam_master_id',
        $request->exam_master_id
    )
    ->select(
        's.subject_name',

        DB::raw(
            'MAX(sm.theory_max_marks) as max_marks'
        ),

        DB::raw(
            'MAX(sm.theory_passing_marks) as passing_marks'
        ),

        DB::raw(
            'MAX(sm.theory_obtained_marks) as obtained_marks'
        ),

        DB::raw("
            CASE
                WHEN MAX(COALESCE(sm.theory_obtained_marks,0))
                     >= MAX(COALESCE(sm.theory_passing_marks,0))
                THEN 'PASS'
                ELSE 'FAIL'
            END as subject_result
        ")
    )
    ->groupBy(
        's.subject_name'
    )
    ->orderByRaw(
        "
        CASE
            WHEN UPPER(s.subject_name) IN
            (
                'COMPUTER',
                'ROBOTICS',
                'PHYSICAL EDUCATION'
            )
            THEN 2
            ELSE 1
        END,
        s.subject_name
        "
    )
    ->get();
        }

        return view(
            'report-card.index',
            compact(
                'academicYears',
                'exams',
                'standards',
                'divisions',
                'students',
                'report',
                'subjects'
            )
        )->with([
            'academic_year_id' => $request->academic_year_id,
            'exam_master_id'   => $request->exam_master_id,
            'standard_id'      => $request->standard_id,
            'division_id'      => $request->division_id,
        ]);
    }
}
