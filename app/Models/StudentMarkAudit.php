<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMarkAudit extends Model
{
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
        'user_agent',
    ];
}