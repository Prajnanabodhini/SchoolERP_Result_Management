<?php

namespace App\Http\Controllers\ResultSheet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResultSheetController extends Controller
{
    protected ResultSheetDataService $dataService;

    protected ResultSheetExportService $exportService;


    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(
        ResultSheetDataService $dataService,
        ResultSheetExportService $exportService
    ) {
        $this->dataService = $dataService;

        $this->exportService = $exportService;
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        return $this->dataService->index();
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(Request $request)
    {
        $request->validate([
            'academic_year_id' => [
                'required',
                'integer',
            ],

            'exam_master_id' => [
                'required',
                'integer',
            ],

            'division_id' => [
                'required',
                'integer',
            ],
        ]);


        return $this->dataService->search(
            (int) $request->academic_year_id,
            (int) $request->exam_master_id,
            (int) $request->division_id
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
            'academic_year_id' => [
                'required',
                'integer',
            ],

            'exam_master_id' => [
                'required',
                'integer',
            ],

            'division_id' => [
                'required',
                'integer',
            ],
        ]);


        return $this->dataService->print(
            (int) $request->academic_year_id,
            (int) $request->exam_master_id,
            (int) $request->division_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT EXCEL
    |--------------------------------------------------------------------------
    */

    public function exportExcel(Request $request)
    {
        $request->validate([
            'academic_year_id' => [
                'required',
                'integer',
            ],

            'exam_master_id' => [
                'required',
                'integer',
            ],

            'division_id' => [
                'required',
                'integer',
            ],
        ]);


        $data =
            $this->dataService->buildForExport(
                (int) $request->academic_year_id,
                (int) $request->exam_master_id,
                (int) $request->division_id
            );


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


        return $this->exportService->download(
            $data['viewData']
        );
    }
}