<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ExamMaster;
use App\Models\Division;
use App\Models\AcademicYear;

class ResultGenerationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | RESULT GENERATION INDEX
    |--------------------------------------------------------------------------
    |
    | Standard is NOT selected separately.
    | Standard is automatically determined from the selected Exam.
    |
    */

    public function index()
    {
        $exams = ExamMaster::orderBy('display_order')
            ->orderBy('exam_name')
            ->get();

        $divisions = Division::orderBy('display_order')
            ->get();

        $academicYears = AcademicYear::orderByDesc('id')
            ->get();

        return view(
            'administrator.result-generation.index',
            compact(
                'exams',
                'divisions',
                'academicYears'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE RESULTS
    |--------------------------------------------------------------------------
    */

    public function generate(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | REQUEST VALUES
        |--------------------------------------------------------------------------
        */

        $academicYearId = (int) $request->academic_year_id;
        $examMasterId   = (int) $request->exam_master_id;
        $divisionId     = (int) $request->division_id;


        /*
        |--------------------------------------------------------------------------
        | BASIC VALIDATION
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


        if (!$examMasterId || !$divisionId) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Please select Exam and Division.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | GET SELECTED EXAM
        |--------------------------------------------------------------------------
        */

        $exam = ExamMaster::find($examMasterId);


        if (!$exam) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam was not found.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | STANDARD IS DETERMINED FROM EXAM
        |--------------------------------------------------------------------------
        */

        $standardId = (int) (
            $exam->standard_id ?? 0
        );


        if ($standardId <= 0) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected Exam does not have a Standard assigned.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | GENERATE
        |--------------------------------------------------------------------------
        */

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
                | GET ALL LOCKED MARKS
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                |
                | student_marks.subject_id
                | =
                | subjects.id
                |
                */

                $lockedMarks = DB::table('student_marks')
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
                    ->orderByDesc('id')
                    ->get();


                if ($lockedMarks->isEmpty()) {

                    throw new \Exception(
                        'No finally submitted marks found for the selected Exam, Standard and Division.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 2
                | VALIDATE CANONICAL SUBJECT IDS
                |--------------------------------------------------------------------------
                |
                | student_marks.subject_id MUST REFER TO subjects.id
                |
                */

                $subjectIds = $lockedMarks
                    ->pluck('subject_id')
                    ->filter()
                    ->map(
                        fn ($id) => (int) $id
                    )
                    ->unique()
                    ->values();


                if ($subjectIds->isEmpty()) {

                    throw new \Exception(
                        'No valid subject IDs found in locked marks.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | VERIFY SUBJECTS EXIST IN SUBJECT MASTER
                |--------------------------------------------------------------------------
                */

                $existingSubjectIds = DB::table('subjects')
                    ->whereIn(
                        'id',
                        $subjectIds->toArray()
                    )
                    ->pluck('id')
                    ->map(
                        fn ($id) => (int) $id
                    )
                    ->toArray();


                $invalidSubjectIds = $subjectIds
                    ->filter(
                        fn ($id) =>
                            !in_array(
                                (int) $id,
                                $existingSubjectIds,
                                true
                            )
                    )
                    ->values();


                if ($invalidSubjectIds->isNotEmpty()) {

                    throw new \Exception(
                        'Invalid subject IDs found in locked marks: '
                        . $invalidSubjectIds->implode(', ')
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | OPTIONAL:
                | VERIFY SUBJECTS BELONG TO STANDARD
                |--------------------------------------------------------------------------
                |
                | This makes the generation safer.
                |
                */

                $validStandardSubjectIds = DB::table(
                    'standard_wise_subjects'
                )
                    ->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->whereIn(
                        'subject_id',
                        $subjectIds->toArray()
                    )
                    ->pluck('subject_id')
                    ->map(
                        fn ($id) => (int) $id
                    )
                    ->unique()
                    ->values()
                    ->toArray();


                $invalidStandardSubjects = $subjectIds
                    ->filter(
                        fn ($id) =>
                            !in_array(
                                (int) $id,
                                $validStandardSubjectIds,
                                true
                            )
                    )
                    ->values();


                if ($invalidStandardSubjects->isNotEmpty()) {

                    throw new \Exception(
                        'The following subject IDs are not mapped to the selected Standard: '
                        . $invalidStandardSubjects->implode(', ')
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 3
                | KEEP ONLY LATEST MARK PER STUDENT + SUBJECT
                |--------------------------------------------------------------------------
                |
                | This prevents duplicate mark rows from creating duplicate
                | subject result details.
                |
                */

                $uniqueMarks = collect();


                foreach ($lockedMarks as $mark) {

                    $key =
                        (int) $mark->student_id
                        . '_'
                        . (int) $mark->subject_id;


                    if (!$uniqueMarks->has($key)) {

                        $uniqueMarks->put(
                            $key,
                            $mark
                        );
                    }
                }


                $uniqueMarks = $uniqueMarks->values();


                /*
                |--------------------------------------------------------------------------
                | STEP 4
                | DELETE OLD GENERATED RESULTS
                |--------------------------------------------------------------------------
                */

                $existingResultIds = DB::table(
                    'student_results'
                )
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

                    DB::table(
                        'student_result_details'
                    )
                        ->whereIn(
                            'student_result_id',
                            $existingResultIds
                        )
                        ->delete();


                    DB::table(
                        'student_results'
                    )
                        ->whereIn(
                            'id',
                            $existingResultIds
                        )
                        ->delete();
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 5
                | GET STUDENT IDS
                |--------------------------------------------------------------------------
                */

                $studentIds = $uniqueMarks
                    ->pluck('student_id')
                    ->filter()
                    ->map(
                        fn ($id) => (int) $id
                    )
                    ->unique()
                    ->values();


                if ($studentIds->isEmpty()) {

                    throw new \Exception(
                        'No students found in locked marks.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 6
                | GENERATE RESULT STUDENT BY STUDENT
                |--------------------------------------------------------------------------
                */

                foreach ($studentIds as $studentId) {

                    /*
                    |--------------------------------------------------------------------------
                    | MARKS FOR CURRENT STUDENT
                    |--------------------------------------------------------------------------
                    */

                    $marks = $uniqueMarks
                        ->filter(
                            fn ($mark) =>
                                (int) $mark->student_id
                                ===
                                (int) $studentId
                        )
                        ->values();


                    if ($marks->isEmpty()) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL MAX MARKS
                    |--------------------------------------------------------------------------
                    */

                    $totalMax = 0;


                    foreach ($marks as $mark) {

                        $subjectMax =
                            (float) (
                                $mark->theory_max_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->oral_max_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->practical_max_marks
                                ?? 0
                            );


                        $totalMax += $subjectMax;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL OBTAINED
                    |--------------------------------------------------------------------------
                    */

                    $totalObtained = 0;


                    foreach ($marks as $mark) {

                        if (
                            (int) $mark->is_absent === 1
                        ) {
                            continue;
                        }


                        $totalObtained +=
                            (float) (
                                $mark->theory_obtained_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->oral_obtained_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->practical_obtained_marks
                                ?? 0
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | OVERALL PERCENTAGE
                    |--------------------------------------------------------------------------
                    */

                    $percentage =
                        $totalMax > 0
                            ? round(
                                (
                                    $totalObtained
                                    /
                                    $totalMax
                                ) * 100,
                                2
                            )
                            : 0;


                    /*
                    |--------------------------------------------------------------------------
                    | FAILED SUBJECT COUNT
                    |--------------------------------------------------------------------------
                    */

                    $failedSubjects = 0;


                    foreach ($marks as $mark) {

                        /*
                        |--------------------------------------------------------------------------
                        | ABSENT = FAIL
                        |--------------------------------------------------------------------------
                        */

                        if (
                            (int) $mark->is_absent === 1
                        ) {

                            $failedSubjects++;

                            continue;
                        }


                        $obtained =
                            (float) (
                                $mark->theory_obtained_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->oral_obtained_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->practical_obtained_marks
                                ?? 0
                            );


                        $passing =
                            (float) (
                                $mark->theory_passing_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->oral_passing_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->practical_passing_marks
                                ?? 0
                            );


                        if ($obtained < $passing) {

                            $failedSubjects++;
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | FINAL RESULT
                    |--------------------------------------------------------------------------
                    */

                    $result =
                        $failedSubjects > 0
                            ? 'FAIL'
                            : 'PASS';


                    /*
                    |--------------------------------------------------------------------------
                    | OVERALL GRADE
                    |--------------------------------------------------------------------------
                    */

                    $grade =
                        $this->calculateOverallGrade(
                            $percentage
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | STEP 7
                    | INSERT STUDENT RESULT
                    |--------------------------------------------------------------------------
                    */

                    $resultId =
                        DB::table(
                            'student_results'
                        )
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
                                    $studentId,

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
                    | STEP 8
                    | INSERT SUBJECT RESULT DETAILS
                    |--------------------------------------------------------------------------
                    */

                    foreach ($marks as $mark) {

                        /*
                        |--------------------------------------------------------------------------
                        | SUBJECT MAXIMUM
                        |--------------------------------------------------------------------------
                        */

                        $maxMarks =
                            (float) (
                                $mark->theory_max_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->oral_max_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->practical_max_marks
                                ?? 0
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | SUBJECT PASSING MARKS
                        |--------------------------------------------------------------------------
                        */

                        $passingMarks =
                            (float) (
                                $mark->theory_passing_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->oral_passing_marks
                                ?? 0
                            )
                            +
                            (float) (
                                $mark->practical_passing_marks
                                ?? 0
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | ABSENT
                        |--------------------------------------------------------------------------
                        */

                        if (
                            (int) $mark->is_absent === 1
                        ) {

                            $obtained = 0;

                            $subjectResult =
                                'ABSENT';

                            $subjectGrade =
                                'AB';

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | OBTAINED MARKS
                            |--------------------------------------------------------------------------
                            */

                            $obtained =
                                (float) (
                                    $mark->theory_obtained_marks
                                    ?? 0
                                )
                                +
                                (float) (
                                    $mark->oral_obtained_marks
                                    ?? 0
                                )
                                +
                                (float) (
                                    $mark->practical_obtained_marks
                                    ?? 0
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | SUBJECT RESULT
                            |--------------------------------------------------------------------------
                            */

                            $subjectResult =
                                $obtained >= $passingMarks
                                    ? 'PASS'
                                    : 'FAIL';


                            /*
                            |--------------------------------------------------------------------------
                            | SUBJECT GRADE
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
                        | IMPORTANT
                        |
                        | student_marks.subject_id is now the canonical
                        | subjects.id.
                        |--------------------------------------------------------------------------
                        */

                        DB::table(
                            'student_result_details'
                        )
                            ->insert([

                                'student_result_id' =>
                                    $resultId,

                                'subject_id' =>
                                    (int) $mark->subject_id,

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
                | STEP 9
                | CALCULATE RANK
                |--------------------------------------------------------------------------
                */

                $passStudents =
                    DB::table(
                        'student_results'
                    )
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
                        ->orderByDesc(
                            'percentage'
                        )
                        ->orderByDesc(
                            'total_obtained_marks'
                        )
                        ->get();


                $rank = 0;

                $position = 0;

                $previousPercentage = null;

                $previousObtained = null;


                foreach (
                    $passStudents
                    as $studentResult
                ) {

                    $position++;


                    if (
                        $previousPercentage
                            !==
                            $studentResult->percentage
                        ||
                        $previousObtained
                            !==
                            $studentResult->total_obtained_marks
                    ) {

                        $rank = $position;
                    }


                    DB::table(
                        'student_results'
                    )
                        ->where(
                            'id',
                            $studentResult->id
                        )
                        ->update([
                            'rank' =>
                                $rank
                        ]);


                    $previousPercentage =
                        $studentResult->percentage;

                    $previousObtained =
                        $studentResult->total_obtained_marks;
                }


                /*
                |--------------------------------------------------------------------------
                | STEP 10
                | FAILED STUDENTS HAVE NO RANK
                |--------------------------------------------------------------------------
                */

                DB::table(
                    'student_results'
                )
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
                        'rank' =>
                            null
                    ]);
            });


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
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
    | SUBJECT GRADE
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
            (
                $obtainedMarks
                /
                $maxMarks
            ) * 100;


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
    | OVERALL GRADE
    |--------------------------------------------------------------------------
    */

    private function calculateOverallGrade(
        $percentage
    ) {

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

        if ($percentage >= 33) {
            return 'D';
        }

        return 'FAIL';
    }
}