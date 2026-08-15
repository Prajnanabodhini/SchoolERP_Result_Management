<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamMaster;
use App\Models\TeacherMarksStatus;
use App\Models\Standard;
use App\Models\Division;

class ExamProgressController extends Controller
{
    public function index(Request $request)
    {
        $exams = ExamMaster::where('is_active', 1)
            ->orderBy('display_order')
            ->get();

        $standards = Standard::orderBy('display_order')
            ->get();

        $divisions = Division::orderBy('division_name')
            ->get();

        $examId = $request->get('exam_master_id', '');
        $standardId = $request->get('standard_id', '');
        $divisionId = $request->get('division_id', '');

        $query = TeacherMarksStatus::with([
    'exam',
    'teacher',
    'subject',
    'standard',
    'division',
]);

        if (!empty($examId)) {
            $query->where('exam_master_id', $examId);
        }

        if (!empty($standardId)) {
            $query->where('standard_id', $standardId);
        }

        if (!empty($divisionId)) {
            $query->where('division_id', $divisionId);
        }

        $allStatuses = (clone $query)->get();

        $completed = $allStatuses
            ->where('status', 'COMPLETED')
            ->count();

        $pending = $allStatuses
            ->where('status', 'PENDING')
            ->count();

        $notAllocated = $allStatuses
            ->where('status', 'NOT_ALLOCATED')
            ->count();

        $statuses = $query
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

//             dd(
//     $statuses->first()->subject_id,
//     $statuses->first()->subject
// );
        return view(
            'administrator.exam-progress.index',
            compact(
                'exams',
                'standards',
                'divisions',
                'examId',
                'standardId',
                'divisionId',
                'statuses',
                'completed',
                'pending',
                'notAllocated'
            )
        );
    }
}