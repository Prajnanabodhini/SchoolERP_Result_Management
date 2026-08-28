<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentMark;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use App\Helpers\StudentHelper;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Dropdown Data
        |--------------------------------------------------------------------------
        */

        $academicYears = AcademicYear::orderByDesc('year_name')->get();
        $exams = ExamMaster::orderBy('display_order')->get();
        $standards = Standard::orderBy('display_order')->get();
        $divisions = Division::orderBy('display_order')->get();

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $yearId     = $request->academic_year_id;
        $standardId = $request->standard_id;
        $divisionId = $request->division_id;
        $examId     = $request->exam_master_id;

        /*
        |--------------------------------------------------------------------------
        | Base Query – with conditional academic‑subject filter
        |--------------------------------------------------------------------------
        */

        $query = StudentMark::query();

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        }
        if ($examId) {
            $query->where('exam_master_id', $examId);
        }
        if ($standardId) {
            $query->where('standard_id', $standardId);
        }
        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }

        /*
        |--------------------------------------------------------------------------
        | 🔥 Apply academic‑subject filter ONLY when NO exam is selected
        |--------------------------------------------------------------------------
        */

        if (empty($examId)) {
            $query->join('standard_wise_subjects as sws', function ($join) {
                $join->on('sws.standard_id', '=', 'student_marks.standard_id')
                     ->where('sws.is_active', 1)
                     ->where(function ($q) {
                         $q->whereColumn('sws.subject_id', '=', 'student_marks.subject_id')
                           ->orWhereColumn('sws.id', '=', 'student_marks.subject_id');
                     });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Summary Cards
        |--------------------------------------------------------------------------
        */

        $totalStudents = (clone $query)
            ->distinct('student_id')
            ->count('student_id');

        $averageMarks = (clone $query)
            ->selectRaw("
                AVG(
                    COALESCE(theory_obtained_marks,0)
                    +
                    COALESCE(oral_obtained_marks,0)
                    +
                    COALESCE(practical_obtained_marks,0)
                ) as avg_marks
            ")
            ->value('avg_marks') ?? 0;

        $highestMarks = (clone $query)
            ->selectRaw("
                MAX(
                    COALESCE(theory_obtained_marks,0)
                    +
                    COALESCE(oral_obtained_marks,0)
                    +
                    COALESCE(practical_obtained_marks,0)
                ) as max_marks
            ")
            ->value('max_marks') ?? 0;

        $lowestMarks = (clone $query)
            ->selectRaw("
                MIN(
                    COALESCE(theory_obtained_marks,0)
                    +
                    COALESCE(oral_obtained_marks,0)
                    +
                    COALESCE(practical_obtained_marks,0)
                ) as min_marks
            ")
            ->value('min_marks') ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Subject Analysis
        |--------------------------------------------------------------------------
        */

        $subjectAnalysis = (clone $query)
            ->join('subjects', 'subjects.id', '=', 'student_marks.subject_id')
            ->leftJoin('subject_types', 'subject_types.id', '=', 'subjects.subject_type_id')
            ->select(
                DB::raw("
                    CASE
                        WHEN subject_types.type_name = 'SKILL'
                        THEN 'SKILL SUBJECT'
                        ELSE subjects.subject_name
                    END as display_subject
                "),
                DB::raw("
                    AVG(
                        COALESCE(theory_obtained_marks,0)
                        +
                        COALESCE(oral_obtained_marks,0)
                        +
                        COALESCE(practical_obtained_marks,0)
                    ) as avg_marks
                ")
            )
            ->groupBy('display_subject')
            ->orderBy('display_subject')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Pass / Fail
        |--------------------------------------------------------------------------
        */

        $passStudents = (clone $query)
            ->select('student_id')
            ->groupBy('student_id')
            ->havingRaw("
                (
                    SUM(
                        COALESCE(theory_obtained_marks,0)
                        +
                        COALESCE(oral_obtained_marks,0)
                        +
                        COALESCE(practical_obtained_marks,0)
                    )
                    /
                    NULLIF(
                        SUM(
                            COALESCE(theory_max_marks,0)
                            +
                            COALESCE(oral_max_marks,0)
                            +
                            COALESCE(practical_max_marks,0)
                        ),
                        0
                    )
                ) * 100 >= 35
            ")
            ->get()
            ->count();

        $passPercentage = $totalStudents > 0 ? ($passStudents / $totalStudents) * 100 : 0;
        $failPercentage = 100 - $passPercentage;

        /*
        |--------------------------------------------------------------------------
        | Top Students
        |--------------------------------------------------------------------------
        */

        $topStudents = (clone $query)
            ->select(
                'student_id',
                DB::raw("
                    SUM(
                        COALESCE(theory_obtained_marks,0)
                        +
                        COALESCE(oral_obtained_marks,0)
                        +
                        COALESCE(practical_obtained_marks,0)
                    ) as total_marks
                ")
            )
            ->groupBy('student_id')
            ->orderByDesc('total_marks')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Risk Students
        |--------------------------------------------------------------------------
        */

        $riskStudents = (clone $query)
            ->select(
                'student_id',
                DB::raw("
                    AVG(
                        COALESCE(theory_obtained_marks,0)
                        +
                        COALESCE(oral_obtained_marks,0)
                        +
                        COALESCE(practical_obtained_marks,0)
                    ) as avg_marks
                ")
            )
            ->groupBy('student_id')
            ->havingRaw('avg_marks < 40')
            ->orderBy('avg_marks')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ERP Student Names
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('academic_year_id') &&
            $request->filled('standard_id') &&
            $request->filled('division_id')
        ) {
            $students = StudentHelper::getStudentsDirectERP(
                $request->academic_year_id,
                $request->standard_id,
                $request->division_id
            );

            $studentMap = $students->keyBy('Studentid');

            $topStudents = $topStudents->map(function ($row) use ($studentMap) {
                $student = $studentMap->get($row->student_id);
                $row->student_name = $student ? $student->full_name : 'Unknown Student';
                return $row;
            });

            $riskStudents = $riskStudents->map(function ($row) use ($studentMap) {
                $student = $studentMap->get($row->student_id);
                $row->student_name = $student ? $student->full_name : 'Unknown Student';
                return $row;
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Grade Distribution
        |--------------------------------------------------------------------------
        */

        $gradeCounts = [
            'A1' => 0, 'A2' => 0, 'B1' => 0, 'B2' => 0,
            'C1' => 0, 'C2' => 0, 'D'  => 0, 'E'  => 0,
            'Absent' => 0, 'Left' => 0,
        ];

        $studentGrades = (clone $query)
            ->select(
                'student_id',
                DB::raw("MAX(COALESCE(is_absent,0)) as is_absent"),
                DB::raw("
                    (
                        SUM(
                            COALESCE(theory_obtained_marks,0)
                            +
                            COALESCE(oral_obtained_marks,0)
                            +
                            COALESCE(practical_obtained_marks,0)
                        )
                        /
                        NULLIF(
                            SUM(
                                COALESCE(theory_max_marks,0)
                                +
                                COALESCE(oral_max_marks,0)
                                +
                                COALESCE(practical_max_marks,0)
                            ),
                            0
                        )
                    ) * 100 as percentage
                ")
            )
            ->groupBy('student_id')
            ->get();

        foreach ($studentGrades as $student) {
            if (($student->is_absent ?? 0) == 1) {
                $gradeCounts['Absent']++;
                continue;
            }

            $avg = $student->percentage;

            if ($avg >= 91)      $gradeCounts['A1']++;
            elseif ($avg >= 81)  $gradeCounts['A2']++;
            elseif ($avg >= 71)  $gradeCounts['B1']++;
            elseif ($avg >= 61)  $gradeCounts['B2']++;
            elseif ($avg >= 51)  $gradeCounts['C1']++;
            elseif ($avg >= 41)  $gradeCounts['C2']++;
            elseif ($avg >= 35)  $gradeCounts['D']++;
            else                 $gradeCounts['E']++;
        }

        // Chart data
        $subjectLabels = $subjectAnalysis->pluck('display_subject')->toArray();
        $subjectMarks = $subjectAnalysis->pluck('avg_marks')->map(fn($m) => round($m, 2))->toArray();

        $topStudentNames = $topStudents->pluck('student_name')->toArray();
        $topStudentMarks = $topStudents->pluck('total_marks')->map(fn($m) => round($m, 2))->toArray();

        return view(
            'analytics.index',
            compact(
                'gradeCounts',
                'academicYears',
                'exams',
                'examId',
                'standards',
                'divisions',
                'standardId',
                'divisionId',
                'totalStudents',
                'averageMarks',
                'highestMarks',
                'lowestMarks',
                'passPercentage',
                'failPercentage',
                'subjectAnalysis',
                'topStudents',
                'riskStudents',
                'subjectLabels',
                'subjectMarks',
                'topStudentNames',
                'topStudentMarks',
            )
        );
    }
}