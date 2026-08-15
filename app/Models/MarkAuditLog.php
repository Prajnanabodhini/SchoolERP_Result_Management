<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarkAuditLog extends Model
{
    protected $table = 'mark_audit_logs';

    protected $fillable = [
        'student_mark_id',
        'student_id',
        'exam_master_id',
        'subject_id',
        'teacher_id',
        'action',
        'old_theory_marks',
        'new_theory_marks',
        'old_oral_marks',
        'new_oral_marks',
        'old_practical_marks',
        'new_practical_marks',
        'remarks',
        'ip_address',
        'user_agent'
    ];

    public function exam()
{
    return $this->belongsTo(
        \App\Models\ExamMaster::class,
        'exam_master_id'
    );
}

public function subject()
{
    return $this->belongsTo(
        \App\Models\Subject::class,
        'subject_id'
    );
}

public function user()
{
    return $this->belongsTo(
        \App\Models\User::class,
        'teacher_id'
    );
}
}