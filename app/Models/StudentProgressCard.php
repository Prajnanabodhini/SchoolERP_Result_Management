<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProgressCard extends Model
{
    protected $table = 'student_progress_cards';

    protected $fillable = [
        'student_id',
        'exam_master_id',
        'academic_year_id',
        'standard_id',
        'division_id',
        'phone',
        'email',
        'school_timing',
        'blood_group',
        'mother_tongue',
        'term1_weight',
        'term1_height',
        'term2_weight',
        'term2_height',
        'attendance_data',
        'passed_promoted_to',
        'school_reopens_on',
        'second_term_grades',
        'descriptive_records',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'attendance_data'     => 'array',
        'second_term_grades'  => 'array',
        'descriptive_records' => 'array',
    ];
}