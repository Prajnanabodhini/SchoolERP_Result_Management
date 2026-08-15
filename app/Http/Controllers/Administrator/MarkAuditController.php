<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\MarkAuditLog;
use App\Models\ExamMaster;
use App\Models\Subject;

class MarkAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = MarkAuditLog::query();

        if ($request->exam_master_id) {
            $query->where(
                'exam_master_id',
                $request->exam_master_id
            );
        }

        if ($request->subject_id) {
            $query->where(
                'subject_id',
                $request->subject_id
            );
        }

        $logs = $query
            ->orderBy('id', 'desc')
            ->paginate(100);

        $exams = ExamMaster::orderBy(
            'display_order'
        )->get();

        $subjects = Subject::orderBy(
            'subject_name'
        )->get();

        return view(
            'administrator.audit.index',
            compact(
                'logs',
                'exams',
                'subjects'
            )
        );
    }
}