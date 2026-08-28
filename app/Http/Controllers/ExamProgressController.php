<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\ExamMaster;
use App\Models\Standard;
use App\Models\Division;

class ExamProgressController extends Controller
{
    private function isAdministrator(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('Administrator') || $user->hasRole('admin')) return true;
        }
        $role = strtolower(trim((string) ($user->role ?? '')));
        return in_array($role, ['administrator', 'admin'], true);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) abort(403);
        $userId = (int) $user->id;
        $isAdministrator = $this->isAdministrator();

        $examId = $request->input('exam_master_id', '');
        $standardId = $request->input('standard_id', '');
        $divisionId = $request->input('division_id', '');

        $exams = ExamMaster::query()->where('is_active', 1)->orderBy('display_order')->orderBy('exam_name')->get();
        $standards = Standard::query()->where('is_active', 1)->orderBy('display_order')->get();
        $divisions = Division::query()->where('is_active', 1)->orderBy('division_name')->get();

        $query = DB::table('teacher_marks_status as tms')
            ->join('teacher_subject_allocations as tsa', 'tsa.id', '=', 'tms.teacher_subject_allocation_id')
            // Filter: only academic subjects that exist in standard_wise_subjects
            ->join('standard_wise_subjects as sws', function ($join) {
                $join->on('sws.standard_id', '=', 'tms.standard_id')
                     ->where('sws.is_active', 1)
                     ->where(function ($q) {
                         $q->whereColumn('sws.subject_id', '=', 'tsa.subject_id')
                           ->orWhereColumn('sws.id', '=', 'tsa.subject_id');
                     });
            })
            ->join('subjects as subj', 'subj.id', '=', DB::raw('COALESCE(sws.subject_id, tsa.subject_id)'))
            ->leftJoin('exam_masters as em', 'em.id', '=', 'tms.exam_master_id')
            ->leftJoin('standards as st', 'st.id', '=', 'tms.standard_id')
            ->leftJoin('divisions as d', 'd.id', '=', 'tms.division_id')
            ->leftJoin('users as u', 'u.id', '=', 'tms.teacher_id')
            ->leftJoin('teacher_class_allocations as tca', 'tca.id', '=', 'tsa.teacher_class_allocation_id')
            ->whereNotNull('tms.teacher_subject_allocation_id');

        if ($examId !== '') $query->where('tms.exam_master_id', (int) $examId);
        if ($standardId !== '') $query->where('tms.standard_id', (int) $standardId);
        if ($divisionId !== '') $query->where('tms.division_id', (int) $divisionId);

        if (!$isAdministrator) {
            $query->where('tca.user_id', $userId);
        }

        $total = (clone $query)->count('tms.id');
        $completed = (clone $query)->whereRaw("UPPER(TRIM(tms.status)) = 'COMPLETED'")->count('tms.id');
        $pending = (clone $query)->whereRaw("UPPER(TRIM(tms.status)) = 'PENDING'")->count('tms.id');

        $statuses = (clone $query)
            ->select([
                'tms.id',
                'tms.teacher_subject_allocation_id',
                'tms.exam_master_id',
                'tms.standard_id',
                'tms.division_id',
                'tms.teacher_id',
                'tms.academic_year_id',
                'tms.status',
                'em.exam_name',
                'em.display_order as exam_display_order',
                'st.standard_name',
                'st.display_order as standard_display_order',
                'd.division_name',
                'd.display_order as division_display_order',
                'u.name as teacher_name',
                'subj.subject_name',
                'subj.subject_code',
                'subj.id as resolved_subject_id',
            ])
            ->orderBy('em.display_order')
            ->orderBy('st.display_order')
            ->orderBy('d.display_order')
            ->orderBy('tms.id')
            ->paginate(50)
            ->withQueryString();

        // Mark counts
        $tsaIds = $statuses->pluck('teacher_subject_allocation_id')->filter()->unique()->values()->toArray();
        $markCounts = [];
        if (!empty($tsaIds)) {
            $markCounts = DB::table('student_marks')
                ->select('teacher_subject_allocation_id', DB::raw('COUNT(DISTINCT student_id) as mark_count'))
                ->whereIn('teacher_subject_allocation_id', $tsaIds)
                ->groupBy('teacher_subject_allocation_id')
                ->get()
                ->keyBy('teacher_subject_allocation_id')
                ->toArray();
        }

        foreach ($statuses as $status) {
            $status->status = strtoupper(trim((string) ($status->status ?? '')));
            $status->mark_count = isset($markCounts[$status->teacher_subject_allocation_id]) ? (int) $markCounts[$status->teacher_subject_allocation_id]->mark_count : 0;
        }

        return view('administrator.exam-progress.index', compact(
            'statuses', 'exams', 'standards', 'divisions',
            'examId', 'standardId', 'divisionId',
            'completed', 'pending', 'total'
        ));
    }
}