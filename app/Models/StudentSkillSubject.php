<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSkillSubject extends Model
{
    protected $table = 'student_skill_subjects';

    protected $fillable = [
        'academic_year_id',
        'student_id',
        'subject_id',
        'created_by',
        'updated_by'
    ];
}