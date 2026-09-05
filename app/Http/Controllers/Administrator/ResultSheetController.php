<?php

namespace App\Http\Controllers\Administrator;

use App\Helpers\MarksHelper;
use App\Helpers\ResultSheetHelper;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Division;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Services\ResultSheetAnalysisService;
use App\Services\ResultSheetDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResultSheetController extends Controller
{
    public function __construct(
        private ResultSheetDataService $dataService,
        private ResultSheetAnalysisService $analysisService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        return view(
            'administrator.result-sheet.index',
            [
                'exams' =>
                    ExamMaster::orderByDesc('id')
                        ->get(),

                'standards' =>
                    Standard::orderBy('display_order')
                        ->get(),

                'divisions' =>
                    Division::orderBy('division_name')
                        ->get(),

                'academicYears' =>
                    AcademicYear::orderByDesc('id')
                        ->get(),

                'results' =>
                    collect(),

                'displayColumns' =>
                    collect(),

                'totalMaxMarks' =>
                    0,

                'passPercentage' =>
                    35,

                'exam' =>
                    null,

                'standard' =>
                    null,

                'division' =>
                    null,

                'academicYear' =>
                    null,

                'classTeacher' =>
                    null,

                'principal' =>
                    null,

                'overallGradeAnalysis' =>
                    [],

                'girlsSubjectAnalysis' =>
                    [],

                'boysSubjectAnalysis' =>
                    [],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(Request $request)
    {
        $request->validate([
            'academic_year_id' =>
                'required|integer',

            'exam_master_id' =>
                'required|integer',

            'division_id' =>
                'required|integer',
        ]);


        $data =
            $this->build($request);


        if (
            !empty($data['error'])
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $data['error']
                );
        }


        $viewData =
            $data['viewData'];


        $this->attachAnalysis(
            $viewData
        );


        return view(
            'administrator.result-sheet.index',
            $viewData
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    public function print(Request $request)
    {
        $request->validate([
            'academic_year_id' =>
                'required|integer',

            'exam_master_id' =>
                'required|integer',

            'division_id' =>
                'required|integer',
        ]);


        $data =
            $this->build($request);


        if (
            !empty($data['error'])
        ) {

            return redirect()
                ->route(
                    'result-sheet.index'
                )
                ->with(
                    'error',
                    $data['error']
                );
        }


        $viewData =
            $data['viewData'];


        $this->attachAnalysis(
            $viewData
        );


        return view(
            'administrator.result-sheet.print',
            $viewData
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT EXCEL
    |--------------------------------------------------------------------------
    */

    public function exportExcel(
        Request $request
    ) {
        $request->validate([
            'academic_year_id' =>
                'required|integer',

            'exam_master_id' =>
                'required|integer',

            'division_id' =>
                'required|integer',
        ]);


        $data =
            $this->build($request);


        if (
            !empty($data['error'])
        ) {

            return redirect()
                ->route(
                    'result-sheet.index'
                )
                ->with(
                    'error',
                    $data['error']
                );
        }


        $viewData =
            $data['viewData'];


        $this->attachAnalysis(
            $viewData
        );


        return $this->makeExcelResponse(
            $viewData
        );
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD
    |--------------------------------------------------------------------------
    */

    private function build(
        Request $request
    ): array {

        $exam =
            ExamMaster::find(
                (int)
                $request->exam_master_id
            );


        if (!$exam) {

            return [
                'error' =>
                    'Selected Exam was not found.',
            ];
        }


        $standardId =
            (int) (
                $exam->standard_id ?? 0
            );


        if (
            $standardId <= 0 &&
            Schema::hasTable(
                'exam_master_subjects'
            )
        ) {

            $standardId =
                (int) (
                    DB::table(
                        'exam_master_subjects'
                    )
                    ->where(
                        'exam_master_id',
                        $exam->id
                    )
                    ->whereNotNull(
                        'standard_id'
                    )
                    ->value(
                        'standard_id'
                    ) ?? 0
                );
        }


        if (
            $standardId <= 0
        ) {

            return [
                'error' =>
                    'The selected Exam is not mapped to a Standard.',
            ];
        }


        $data =
            $this->dataService->build(
                (int)
                $request->academic_year_id,

                (int)
                $request->exam_master_id,

                $standardId,

                (int)
                $request->division_id
            );


        if (
            !empty($data['viewData'])
        ) {

            /*
            |--------------------------------------------------------------------------
            | ENSURE CENTRAL PASSING RULE
            |--------------------------------------------------------------------------
            */

            $data['viewData']['passPercentage'] =
                MarksHelper::getPassingPercentage(
                    $standardId
                );
        }


        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | ANALYSIS
    |--------------------------------------------------------------------------
    */

    private function attachAnalysis(
        array &$viewData
    ): void {

        $results =
            collect(
                $viewData['results'] ?? []
            );

        $columns =
            collect(
                $viewData['displayColumns'] ?? []
            );


        $viewData['overallGradeAnalysis'] =
            $this->analysisService
                ->buildOverallGradeAnalysis(
                    $results
                );


        $viewData['girlsSubjectAnalysis'] =
            $this->analysisService
                ->buildSubjectAnalysis(
                    $results,
                    $columns,
                    'FEMALE'
                );


        $viewData['boysSubjectAnalysis'] =
            $this->analysisService
                ->buildSubjectAnalysis(
                    $results,
                    $columns,
                    'MALE'
                );
    }


    /*
    |--------------------------------------------------------------------------
    | EXCEL
    |--------------------------------------------------------------------------
    */

    private function makeExcelResponse(
        array $data
    ) {

        $results =
            collect(
                $data['results'] ?? []
            );

        $columns =
            collect(
                $data['displayColumns'] ?? []
            );


        $yearName =
            $data['academicYear']->year_name
            ??
            $data['academicYear']->name
            ??
            'Year';


        $examName =
            $data['exam']->display_exam_name
            ??
            $data['exam']->exam_name
            ??
            'Exam';


        $standardName =
            $data['standard']->standard_name
            ??
            'Standard';


        $divisionName =
            $data['division']->division_name
            ??
            'Division';


        /*
        |--------------------------------------------------------------------------
        | CORE MAXIMUM MARKS ONLY
        |--------------------------------------------------------------------------
        */

        $totalMaxMarks = $columns
            ->filter(
                function ($column) {

                    return (int) (
                        $column->is_optional ?? 0
                    ) !== 1;
                }
            )
            ->sum(
                function ($column) {

                    return (float) (
                        $column->max_marks ?? 0
                    );
                }
            );


        $fileName =
            'Result_Sheet_' .
            ResultSheetHelper::cleanFileName(
                $yearName
            ) .
            '_' .
            ResultSheetHelper::cleanFileName(
                $examName
            ) .
            '_' .
            ResultSheetHelper::cleanFileName(
                $standardName
            ) .
            '_' .
            ResultSheetHelper::cleanFileName(
                $divisionName
            ) .
            '.xls';


        $columnCount =
            4 +
            $columns->count() +
            5;


        $html =
            '<html><head><meta charset="UTF-8">';

        $html .= '<style>';

        $html .=
            'body{font-family:Arial,sans-serif;font-size:11px;}';

        $html .=
            'table{border-collapse:collapse;width:100%;}';

        $html .=
            'th,td{border:1px solid #999;padding:5px;vertical-align:middle;}';

        $html .=
            'th{background:#dbeafe;font-weight:bold;text-align:center;}';

        $html .=
            'td{text-align:center;}';

        $html .=
            '.left{text-align:left;}';

        $html .=
            '.pass{font-weight:bold;}';

        $html .=
            '.fail{font-weight:bold;}';

        $html .=
            '</style></head><body>';


        $html .=
            '<table>';

        $html .=
            '<tr><th colspan="' .
            $columnCount .
            '">EXAMINATION RESULT SHEET</th></tr>';

        $html .=
            '<tr><td><b>Academic Year</b></td><td>' .
            e($yearName) .
            '</td>';

        $html .=
            '<td><b>Exam</b></td><td colspan="' .
            ($columnCount - 3) .
            '">' .
            e($examName) .
            '</td></tr>';

        $html .=
            '<tr><td><b>Standard</b></td><td>' .
            e($standardName) .
            '</td>';

        $html .=
            '<td><b>Division</b></td><td>' .
            e($divisionName) .
            '</td></tr>';

        $html .=
            '<tr><td><b>Total Maximum Marks</b></td><td>' .
            e(
                ResultSheetHelper::displayNumber(
                    $totalMaxMarks
                )
            ) .
            '</td>';

        $html .=
            '<td><b>Overall Pass %</b></td><td>' .
            e(
                (string) (
                    $data['passPercentage']
                    ?? 35
                )
            ) .
            '%</td></tr>';

        $html .= '</table><br>';


        /*
        |--------------------------------------------------------------------------
        | MAIN EXCEL TABLE
        |--------------------------------------------------------------------------
        */

        $html .=
            '<table><thead><tr>';

        $html .=
            '<th>Sr. No.</th>' .
            '<th>Roll No.</th>' .
            '<th>Student Name</th>' .
            '<th>Gender</th>';


        foreach (
            $columns as $column
        ) {

            $subjectMax =
                (float) (
                    $column->max_marks ?? 0
                );


            $html .=
                '<th>' .
                e(
                    $column->subject_name
                    ?? '-'
                ) .
                '<br>(Max Mark=' .
                e(
                    ResultSheetHelper::displayNumber(
                        $subjectMax
                    )
                ) .
                ')</th>';
        }


        $html .=
            '<th>Total</th>' .
            '<th>Max Total</th>' .
            '<th>Percentage</th>' .
            '<th>Grade</th>' .
            '<th>Result</th>';

        $html .=
            '</tr></thead><tbody>';


        $srNo = 1;


        foreach (
            $results as $student
        ) {

            $html .= '<tr>';

            $html .=
                '<td>' .
                $srNo++ .
                '</td>';

            $html .=
                '<td>' .
                e(
                    (string)
                    ($student->roll_no ?? '')
                ) .
                '</td>';


            /*
            |--------------------------------------------------------------------------
            | FULL STUDENT NAME
            |--------------------------------------------------------------------------
            */

            $fullName =
                trim(
                    (string) (
                        $student->full_student_name
                        ??
                        $student->student_name
                        ??
                        $student->full_name
                        ??
                        ''
                    )
                );


            $html .=
                '<td class="left">' .
                e($fullName) .
                '</td>';


            $html .=
                '<td>' .
                e(
                    (string)
                    ($student->gender ?? '')
                ) .
                '</td>';


            foreach (
                $columns as $column
            ) {

                $mark =
                    ResultSheetHelper::getStudentMark(
                        $student,
                        $column
                    );

                $html .=
                    '<td>' .
                    e(
                        (string)
                        ($mark ?? '-')
                    ) .
                    '</td>';
            }


            $html .=
                '<td>' .
                e(
                    (string)
                    (
                        $student->academic_total
                        ?? '-'
                    )
                ) .
                '</td>';


            $html .=
                '<td>' .
                e(
                    ResultSheetHelper::displayNumber(
                        $totalMaxMarks
                    )
                ) .
                '</td>';


            $html .=
                '<td>' .
                (
                    $student->calculated_percentage !== null
                        ?
                        e(
                            (string)
                            $student->calculated_percentage
                        ) . '%'
                        :
                        '-'
                ) .
                '</td>';


            $html .=
                '<td>' .
                e(
                    (string)
                    (
                        $student->calculated_grade
                        ?? '-'
                    )
                ) .
                '</td>';


            $result =
                strtoupper(
                    trim(
                        (string)
                        (
                            $student->result
                            ?? '-'
                        )
                    )
                );


            $class =
                $result === 'PASS'
                    ? 'pass'
                    :
                    (
                        $result === 'FAIL'
                            ? 'fail'
                            : ''
                    );


            $html .=
                '<td class="' .
                $class .
                '">' .
                e($result) .
                '</td>';

            $html .= '</tr>';
        }


        if (
            $results->isEmpty()
        ) {

            $html .=
                '<tr><td colspan="' .
                $columnCount .
                '">No result records found.</td></tr>';
        }


        $html .=
            '</tbody></table>';

        $html .=
            '</body></html>';


        return response(
            $html,
            200,
            [
                'Content-Type' =>
                    'application/vnd.ms-excel; charset=UTF-8',

                'Content-Disposition' =>
                    'attachment; filename="' .
                    $fileName .
                    '"',

                'Cache-Control' =>
                    'max-age=0',

                'Pragma' =>
                    'public',
            ]
        );
    }
}