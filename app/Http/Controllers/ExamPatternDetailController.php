<?php

namespace App\Http\Controllers;

use App\Models\ExamPattern;
use App\Models\ExamPatternDetail;
use App\Models\Standard;
use App\Models\Subject;
use Illuminate\Http\Request;

class ExamPatternDetailController extends Controller
{

    public function index(Request $request)
{
    $patterns = ExamPattern::orderBy('pattern_name')->get();

    $details = ExamPatternDetail::with([
        'examPattern',
        'standard',
        'subject'
    ])
    ->orderBy('exam_pattern_id')
    ->orderBy('standard_id')
    ->orderBy('display_order')
    ->get();


    return view(
        'exam-pattern-details.index',
        compact(
            'patterns',
            'details'
        )
    );
}



    public function create()
    {
        $patterns = ExamPattern::where('is_active',1)->get();

        $standards = Standard::where('is_active',1)
                    ->orderBy('display_order')
                    ->get();


        $subjects = Subject::orderBy('subject_name')
                    ->get();


        return view(
            'exam-pattern-details.create',
            compact(
                'patterns',
                'standards',
                'subjects'
            )
        );
    }



    public function store(Request $request)
    {

        $request->validate([
            'exam_pattern_id'=>'required',
            'standard_id'=>'required',
            'subjects'=>'required'
        ]);


        foreach($request->subjects as $index=>$subjectId)
        {

            ExamPatternDetail::updateOrCreate(
                [
                    'exam_pattern_id'=>$request->exam_pattern_id,
                    'standard_id'=>$request->standard_id,
                    'subject_id'=>$subjectId,
                ],
                [
                    'display_order'=>$index+1
                ]
            );

        }


        return redirect()
            ->route('exam-pattern-details.index')
            ->with(
                'success',
                'Subjects allocated successfully.'
            );
    }



    public function destroy(ExamPatternDetail $examPatternDetail)
    {
        $examPatternDetail->delete();


        return back()
        ->with(
            'success',
            'Subject removed successfully.'
        );
    }

public function getSubjects($standard)
{
    $subjects = \DB::table('standard_subjects')
        ->join(
            'subjects',
            'subjects.id',
            '=',
            'standard_subjects.subject_id'
        )
        ->where(
            'standard_subjects.standard_id',
            $standard
        )
        ->select(
            'subjects.id',
            'subjects.subject_name'
        )
        ->orderBy(
            'subjects.subject_name'
        )
        ->get();


    return response()->json($subjects);
}

public function edit($id)
{
    $detail = ExamPatternDetail::findOrFail($id);


    $patterns = ExamPattern::where('is_active',1)->get();


    $standards = Standard::where('is_active',1)
                ->orderBy('display_order')
                ->get();


    $subjects = Subject::orderBy('subject_name')
                ->get();


    $selectedSubjects = ExamPatternDetail::where([
        'exam_pattern_id' => $detail->exam_pattern_id,
        'standard_id' => $detail->standard_id
    ])
    ->pluck('subject_id')
    ->toArray();



    return view(
        'exam-pattern-details.edit',
        compact(
            'detail',
            'patterns',
            'standards',
            'subjects',
            'selectedSubjects'
        )
    );
}



public function update(Request $request,$id)
{

    $request->validate([
        'exam_pattern_id'=>'required',
        'standard_id'=>'required',
        'subjects'=>'required'
    ]);



    ExamPatternDetail::where([
        'exam_pattern_id'=>$request->exam_pattern_id,
        'standard_id'=>$request->standard_id
    ])
    ->delete();



    foreach($request->subjects as $index=>$subjectId)
    {

        ExamPatternDetail::create([

            'exam_pattern_id'=>$request->exam_pattern_id,

            'standard_id'=>$request->standard_id,

            'subject_id'=>$subjectId,

            'display_order'=>$index+1

        ]);

    }



    return redirect()
        ->route('exam-pattern-details.index')
        ->with(
            'success',
            'Allocation updated successfully.'
        );

}

}