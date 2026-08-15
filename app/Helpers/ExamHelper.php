<?php

namespace App\Helpers;

use App\Models\TeacherMarksStatus;
use App\Models\TeacherSubjectAllocation;
use App\Models\ExamCompletionStatus;

class ExamHelper
{
    public static function updateCompletionStatus(
        $academicYearId,
        $examId,
        $standardId,
        $divisionId
    )
    {
        $totalAssignments =
            TeacherSubjectAllocation::whereHas(
                'allocation',
                function ($q) use (
                    $standardId,
                    $divisionId
                )
                {
                    $q->where(
                        'standard_id',
                        $standardId
                    )
                    ->where(
                        'division_id',
                        $divisionId
                    );
                }
            )->count();

        $completedAssignments =
            TeacherMarksStatus::where(
                'exam_master_id',
                $examId
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
                'status',
                'COMPLETED'
            )
            ->count();

        $status =
            (
                $totalAssignments > 0
                &&
                $totalAssignments ==
                $completedAssignments
            )
            ? 'COMPLETED'
            : 'PENDING';

        ExamCompletionStatus::updateOrCreate(
            [
                'academic_year_id' =>
                    $academicYearId,

                'exam_master_id' =>
                    $examId,

                'standard_id' =>
                    $standardId,

                'division_id' =>
                    $divisionId
            ],
            [
                'status' =>
                    $status,

                'completed_at' =>
                    $status == 'COMPLETED'
                    ? now()
                    : null
            ]
        );
    }

    public static function isCompleted(
        $examId,
        $standardId,
        $divisionId
    )
    {
        return ExamCompletionStatus::where(
            'exam_master_id',
            $examId
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
            'status',
            'COMPLETED'
        )
        ->exists();
    }

    public static function getStatus(
        $examId,
        $standardId,
        $divisionId
    )
    {
        return ExamCompletionStatus::where(
            'exam_master_id',
            $examId
        )
        ->where(
            'standard_id',
            $standardId
        )
        ->where(
            'division_id',
            $divisionId
        )
        ->value('status');
    }
}