<?php

namespace App\Http\Controllers;

use App\Models\TeacherMarksStatus;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $role = Auth::user()->role;

        /*
        |--------------------------------------------------------------------------
        | Administrator
        |--------------------------------------------------------------------------
        */

        if ($role === 'Administrator') {
            return redirect()->route('exam-progress.index');
        }

        $pendingEntries = collect();

        if (Auth::check()) {

            $userId = Auth::id();

            /*
            |--------------------------------------------------------------------------
            | Load Pending Teacher Marks
            |--------------------------------------------------------------------------
            */

            $statuses = TeacherMarksStatus::with([
                'exam',

                /*
                 * IMPORTANT:
                 * Subject name comes from standard_wise_subjects
                 */
                'teacherSubjectAllocation.standardWiseSubject',

                /*
                 * Class allocation details
                 */
                'teacherSubjectAllocation.allocation.standard',
                'teacherSubjectAllocation.allocation.division',

            ])
            ->where('teacher_id', $userId)
            ->where('status', 'PENDING')
            ->get();


            foreach ($statuses as $status) {

                $allocation =
                    $status->teacherSubjectAllocation;


                /*
                |--------------------------------------------------------------------------
                | Skip if allocation no longer exists
                |--------------------------------------------------------------------------
                */

                if (!$allocation) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Get Subject Name
                |--------------------------------------------------------------------------
                |
                | The subject is stored in:
                | standard_wise_subjects
                |
                | through:
                | teacher_subject_allocations.standard_wise_subject_id
                |
                */

                $subjectName =
                    $allocation->standardWiseSubject?->subject_name
                    ?? '-';


                /*
                |--------------------------------------------------------------------------
                | Add Pending Entry
                |--------------------------------------------------------------------------
                */

                $pendingEntries->push((object)[

                    'exam_name' =>
                        $status->exam?->exam_name ?? '-',

                    'subject_name' =>
                        $subjectName,

                    'standard_name' =>
                        $allocation
                            ->allocation
                            ?->standard
                            ?->standard_name
                            ?? '-',

                    'division_name' =>
                        $allocation
                            ->allocation
                            ?->division
                            ?->division_name
                            ?? '-',

                    'exam_id' =>
                        $status->exam_master_id,

                    'teacher_subject_allocation_id' =>
                        $allocation->id,

                ]);
            }
        }


        return view(
            'dashboard',
            compact('pendingEntries')
        );
    }
}