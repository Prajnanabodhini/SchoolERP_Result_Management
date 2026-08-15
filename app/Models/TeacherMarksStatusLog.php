<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherMarksStatusLog extends Model
{
    protected $fillable = [

        'teacher_subject_allocation_id',
        'exam_master_id',

        'old_status',
        'new_status',

        'changed_by',

        'remarks',
    ];
}