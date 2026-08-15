<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;
use App\Models\AcademicYear;

class ResultGenerationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Result Generation Index
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $exams = ExamMaster::orderBy('display_order')->get();

        $standards = Standard::orderBy('display_order')->get();

        $divisions = Division::orderBy('display_order')->get();

        $academicYears = AcademicYear::orderByDesc('id')->get();

        return view(
            'administrator.result-generation.index',
            compact(
                'exams',
                'standards',
                'divisions',
                'academicYears'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Results
    |--------------------------------------------------------------------------
    */

    public function generate(Request $request)
    {
        $academicYearId = $request->academic_year_id;
        $examMasterId   = $request->exam_master_id;
        $standardId     = $request->standard_id;
        $divisionId     = $request->division_id;


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        if (!$academicYearId) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Please select Academic Year.'
                );
        }


        if (
            !$examMasterId ||
            !$standardId ||
            !$divisionId
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Please select Exam, Standard and Division.'
                );
        }


        try {

            DB::transaction(function () use (
                $academicYearId,
                $examMasterId,
                $standardId,
                $divisionId
            ) {

                /*
                |--------------------------------------------------------------------------
                | STEP 1
                | Check Locked Marks
                |--------------------------------------------------------------------------
                |
                | Only finally submitted / locked marks are used.
                |
                */

                $lockedMarksQuery = DB::table('student_marks')
                    ->where(
                        'academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'exam_master_id',
                        $examMasterId
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'division_id',
                        $divisionId
                    )
                    ->where(
                        'is_locked',
                        1
                    );


                if (!$lockedMarksQuery->exists()) {

                    throw new \Exception(
                        'No finally submitted marks found for the selected Exam, Standard and Division.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 2
                | Delete Previously Generated Results
                |--------------------------------------------------------------------------
                |
                | Results are regenerated from the latest locked marks.
                |
                */

                $existingResultIds = DB::table('student_results')
                    ->where(
                        'academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'exam_master_id',
                        $examMasterId
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'division_id',
                        $divisionId
                    )
                    ->pluck('id');


                if ($existingResultIds->isNotEmpty()) {

                    DB::table('student_result_details')
                        ->whereIn(
                            'student_result_id',
                            $existingResultIds
                        )
                        ->delete();


                    DB::table('student_results')
                        ->whereIn(
                            'id',
                            $existingResultIds
                        )
                        ->delete();
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 3
                | Get Students Having Locked Marks
                |--------------------------------------------------------------------------
                */

                $students = DB::table('student_marks')
                    ->where(
                        'academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'exam_master_id',
                        $examMasterId
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'division_id',
                        $divisionId
                    )
                    ->where(
                        'is_locked',
                        1
                    )
                    ->select('student_id')
                    ->distinct()
                    ->get();


                if ($students->isEmpty()) {

                    throw new \Exception(
                        'No locked marks found for selected Exam, Standard and Division.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 4
                | Generate Result Student by Student
                |--------------------------------------------------------------------------
                */

                foreach ($students as $student) {

                    $marks = DB::table('student_marks')
                        ->where(
                            'academic_year_id',
                            $academicYearId
                        )
                        ->where(
                            'exam_master_id',
                            $examMasterId
                        )
                        ->where(
                            'standard_id',
                            $standardId
                        )
                        ->where(
                            'division_id',
                            $divisionId
                        )
                        ->where(
                            'student_id',
                            $student->student_id
                        )
                        ->where(
                            'is_locked',
                            1
                        )
                        ->get();


                    if ($marks->isEmpty()) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 5
                    | Calculate Total Maximum Marks
                    |--------------------------------------------------------------------------
                    |
                    | These values were saved when marks were entered.
                    |
                    */

                    $totalMax = $marks->sum(function ($mark) {

                        return
                            (float) ($mark->theory_max_marks ?? 0)
                            +
                            (float) ($mark->oral_max_marks ?? 0)
                            +
                            (float) ($mark->practical_max_marks ?? 0);
                    });


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 6
                    | Calculate Total Obtained Marks
                    |--------------------------------------------------------------------------
                    |
                    | ABSENT student receives 0 obtained marks.
                    |
                    */

                    $totalObtained = $marks->sum(function ($mark) {

                        if ((int) $mark->is_absent === 1) {
                            return 0;
                        }


                        return
                            (float) ($mark->theory_obtained_marks ?? 0)
                            +
                            (float) ($mark->oral_obtained_marks ?? 0)
                            +
                            (float) ($mark->practical_obtained_marks ?? 0);
                    });


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 7
                    | Calculate Percentage
                    |--------------------------------------------------------------------------
                    */

                    $percentage = $totalMax > 0
                        ? round(
                            ($totalObtained / $totalMax) * 100,
                            2
                        )
                        : 0;


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 8
                    | Count Failed / Absent Subjects
                    |--------------------------------------------------------------------------
                    */

                    $failedSubjects = $marks
                        ->filter(function ($mark) {

                            /*
                            |----------------------------------------------------------
                            | ABSENT = FAIL
                            |----------------------------------------------------------
                            */

                            if ((int) $mark->is_absent === 1) {
                                return true;
                            }


                            $obtained =
                                (float) ($mark->theory_obtained_marks ?? 0)
                                +
                                (float) ($mark->oral_obtained_marks ?? 0)
                                +
                                (float) ($mark->practical_obtained_marks ?? 0);


                            $passing =
                                (float) ($mark->theory_passing_marks ?? 0)
                                +
                                (float) ($mark->oral_passing_marks ?? 0)
                                +
                                (float) ($mark->practical_passing_marks ?? 0);


                            return $obtained < $passing;

                        })
                        ->count();


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 9
                    | Overall Result
                    |--------------------------------------------------------------------------
                    */

                    $result = $failedSubjects > 0
                        ? 'FAIL'
                        : 'PASS';


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 10
                    | Overall Grade
                    |--------------------------------------------------------------------------
                    */

                    $grade = $this->calculateOverallGrade(
                        $percentage
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 11
                    | Insert Student Result
                    |--------------------------------------------------------------------------
                    */

                    $resultId = DB::table('student_results')
                        ->insertGetId([

                            'academic_year_id' =>
                                $academicYearId,

                            'exam_master_id' =>
                                $examMasterId,

                            'standard_id' =>
                                $standardId,

                            'division_id' =>
                                $divisionId,

                            'student_id' =>
                                $student->student_id,

                            'total_max_marks' =>
                                $totalMax,

                            'total_obtained_marks' =>
                                $totalObtained,

                            'percentage' =>
                                $percentage,

                            'grade' =>
                                $grade,

                            'result' =>
                                $result,

                            'rank' =>
                                null,

                            'generated_at' =>
                                now(),

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 12
                    | Generate Subject-Wise Result
                    |--------------------------------------------------------------------------
                    */

                    foreach ($marks as $mark) {

                        /*
                        |--------------------------------------------------------------------------
                        | Subject Maximum Marks
                        |--------------------------------------------------------------------------
                        */

                        $maxMarks =
                            (float) ($mark->theory_max_marks ?? 0)
                            +
                            (float) ($mark->oral_max_marks ?? 0)
                            +
                            (float) ($mark->practical_max_marks ?? 0);


                        /*
                        |--------------------------------------------------------------------------
                        | Subject Passing Marks
                        |--------------------------------------------------------------------------
                        */

                        $passingMarks =
                            (float) ($mark->theory_passing_marks ?? 0)
                            +
                            (float) ($mark->oral_passing_marks ?? 0)
                            +
                            (float) ($mark->practical_passing_marks ?? 0);


                        /*
                        |--------------------------------------------------------------------------
                        | ABSENT
                        |--------------------------------------------------------------------------
                        */

                        if ((int) $mark->is_absent === 1) {

                            $obtained = 0;

                            $subjectResult = 'ABSENT';

                            $subjectGrade = 'AB';

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | Obtained Marks
                            |--------------------------------------------------------------------------
                            */

                            $obtained =
                                (float) ($mark->theory_obtained_marks ?? 0)
                                +
                                (float) ($mark->oral_obtained_marks ?? 0)
                                +
                                (float) ($mark->practical_obtained_marks ?? 0);


                            /*
                            |--------------------------------------------------------------------------
                            | Subject Pass / Fail
                            |--------------------------------------------------------------------------
                            */

                            $subjectResult =
                                $obtained >= $passingMarks
                                    ? 'PASS'
                                    : 'FAIL';


                            /*
                            |--------------------------------------------------------------------------
                            | Subject Grade
                            |--------------------------------------------------------------------------
                            */

                            $subjectGrade =
                                $this->calculateGrade(
                                    $obtained,
                                    $maxMarks,
                                    $subjectResult
                                );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Insert Subject Result
                        |--------------------------------------------------------------------------
                        */

                        DB::table('student_result_details')
                            ->insert([

                                'student_result_id' =>
                                    $resultId,

                                'subject_id' =>
                                    $mark->subject_id,

                                'max_marks' =>
                                    $maxMarks,

                                'obtained_marks' =>
                                    $obtained,

                                'grade' =>
                                    $subjectGrade,

                                'passing_marks' =>
                                    $passingMarks,

                                'subject_result' =>
                                    $subjectResult,

                                'created_at' =>
                                    now(),

                                'updated_at' =>
                                    now(),
                            ]);
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 13
                | Calculate Rank
                |--------------------------------------------------------------------------
                |
                | Only PASS students receive rank.
                |
                | Example:
                |
                | 1
                | 2
                | 2
                | 4
                |
                */

                $passStudents = DB::table('student_results')
                    ->where(
                        'academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'exam_master_id',
                        $examMasterId
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'division_id',
                        $divisionId
                    )
                    ->where(
                        'result',
                        'PASS'
                    )
                    ->orderByDesc('percentage')
                    ->orderByDesc('total_obtained_marks')
                    ->get();


                $rank = 0;
                $position = 0;

                $previousPercentage = null;
                $previousObtained = null;


                foreach ($passStudents as $studentResult) {

                    $position++;


                    if (
                        $previousPercentage !==
                            $studentResult->percentage
                        ||
                        $previousObtained !==
                            $studentResult->total_obtained_marks
                    ) {

                        $rank = $position;
                    }


                    DB::table('student_results')
                        ->where(
                            'id',
                            $studentResult->id
                        )
                        ->update([
                            'rank' => $rank
                        ]);


                    $previousPercentage =
                        $studentResult->percentage;

                    $previousObtained =
                        $studentResult->total_obtained_marks;
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 14
                | Failed Students Have No Rank
                |--------------------------------------------------------------------------
                */

                DB::table('student_results')
                    ->where(
                        'academic_year_id',
                        $academicYearId
                    )
                    ->where(
                        'exam_master_id',
                        $examMasterId
                    )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'division_id',
                        $divisionId
                    )
                    ->where(
                        'result',
                        'FAIL'
                    )
                    ->update([
                        'rank' => null
                    ]);
            });


            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            return back()->with(
                'success',
                'Results Generated Successfully.'
            );


        } catch (\Throwable $e) {

            report($e);


            return back()
                ->withInput()
                ->with(
                    'error',
                    'Result generation failed: '
                    . $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Subject Grade
    |--------------------------------------------------------------------------
    */

    private function calculateGrade(
        $obtainedMarks,
        $maxMarks,
        $subjectResult = 'PASS'
    ) {

        if ($subjectResult === 'ABSENT') {
            return 'AB';
        }


        if ($subjectResult === 'LEFT') {
            return 'LEFT';
        }


        if ($subjectResult === 'FAIL') {
            return 'F';
        }


        if ($maxMarks <= 0) {
            return '';
        }


        $percentage =
            ($obtainedMarks / $maxMarks) * 100;


        if ($percentage >= 91) {
            return 'A1';
        }


        if ($percentage >= 81) {
            return 'A2';
        }


        if ($percentage >= 71) {
            return 'B1';
        }


        if ($percentage >= 61) {
            return 'B2';
        }


        if ($percentage >= 51) {
            return 'C1';
        }


        if ($percentage >= 41) {
            return 'C2';
        }


        return 'D';
    }


    /*
    |--------------------------------------------------------------------------
    | Overall Grade
    |--------------------------------------------------------------------------
    */

    private function calculateOverallGrade($percentage)
    {
        if ($percentage >= 91) {
            return 'A1';
        }


        if ($percentage >= 81) {
            return 'A2';
        }


        if ($percentage >= 71) {
            return 'B1';
        }


        if ($percentage >= 61) {
            return 'B2';
        }


        if ($percentage >= 51) {
            return 'C1';
        }


        if ($percentage >= 41) {
            return 'C2';
        }


        return 'D';
    }
}

