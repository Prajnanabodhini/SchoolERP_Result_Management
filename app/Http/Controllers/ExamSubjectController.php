<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Subject;
use App\Models\ExamSubject;

class ExamSubjectController extends Controller
{
    public function index(Request $request)
    {
        $exams = ExamMaster::with('examPattern')
            ->where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        $standards = Standard::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        $subjects = collect();

        $allocatedSubjects = [];

        if (
            $request->filled('exam_master_id') &&
            $request->filled('standard_id')
        ) {

            $exam = ExamMaster::find($request->exam_master_id);

            if ($exam && $exam->exam_pattern_id) {

                $subjects = Subject::join(
        'standard_subjects',
        'subjects.id',
        '=',
        'standard_subjects.subject_id'
    )
    ->where(
        'standard_subjects.standard_id',
        $request->standard_id
    )
    ->where(
        'subjects.is_active',
        1
    )
    ->select(
        'subjects.id',
        'subjects.subject_name'
    )
    ->orderBy(
        'subjects.subject_name'
    )
    ->get();
    
            }

            $allocatedSubjects = ExamSubject::where(
                    'exam_master_id',
                    $request->exam_master_id
                )
                ->where(
                    'standard_id',
                    $request->standard_id
                )
                ->pluck('subject_id')
                ->toArray();
        }

        return view(
            'exam-subjects.index',
            compact(
                'exams',
                'standards',
                'subjects',
                'allocatedSubjects'
            )
        );
    }

    public function save(Request $request)
    {
        $request->validate([
            'exam_master_id' => 'required|exists:exam_masters,id',
            'standard_id'    => 'required|exists:standards,id',
        ]);

        ExamSubject::where(
                'exam_master_id',
                $request->exam_master_id
            )
            ->where(
                'standard_id',
                $request->standard_id
            )
            ->delete();

        if (!empty($request->subjects)) {

            foreach ($request->subjects as $subjectId) {

                ExamSubject::create([
    'exam_master_id' => $request->exam_master_id,

    'standard_id'    => $request->standard_id,

    'subject_id'     => $subjectId,

    'max_marks'      =>
        $request->max_marks[$subjectId] ?? 0,

    'passing_marks'  =>
        $request->passing_marks[$subjectId] ?? 0,
]);
            }
        }

        return redirect()
            ->route(
                'exam-subjects.index',
                [
                    'exam_master_id' => $request->exam_master_id,
                    'standard_id'    => $request->standard_id,
                ]
            )
            ->with(
                'success',
                'Exam Subject Allocation Saved Successfully.'
            );
    }
}