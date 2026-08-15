<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSubject extends Model
{
    protected $fillable = [
    'exam_master_id',
    'standard_id',
    'subject_id',
    'max_marks',
    'passing_marks'
];
}