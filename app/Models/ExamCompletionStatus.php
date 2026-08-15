<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamCompletionStatus extends Model
{
    protected $table =
        'exam_completion_status';

    protected $fillable = [

        'academic_year_id',

        'exam_master_id',

        'standard_id',

        'division_id',

        'status',

        'completed_at'
    ];
}