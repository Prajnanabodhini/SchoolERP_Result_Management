<?php

namespace App\Helpers;

use App\Models\StudentMarkAudit;

class MarkAuditHelper
{
    public static function log(
        $studentMark,
        $action,
        $oldTheory = null,
        $newTheory = null,
        $oldOral = null,
        $newOral = null,
        $oldPractical = null,
        $newPractical = null
    ) {

        StudentMarkAudit::create([

            'student_mark_id' => $studentMark->id,

            'student_id' => $studentMark->student_id,

            'exam_master_id' => $studentMark->exam_master_id,

            'subject_id' => $studentMark->subject_id,

            'teacher_id' => auth()->id(),

            'action' => $action,

            'old_theory_marks' => $oldTheory,
            'new_theory_marks' => $newTheory,

            'old_oral_marks' => $oldOral,
            'new_oral_marks' => $newOral,

            'old_practical_marks' => $oldPractical,
            'new_practical_marks' => $newPractical,

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),
        ]);
    }
}