<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarksEntry extends Model
{
    protected $fillable = [
        'academic_year_id',
        'exam_master_id',
        'standard_id',
        'division_id',
        'student_id',
        'subject_id',
        'marks_obtained'
    ];
}