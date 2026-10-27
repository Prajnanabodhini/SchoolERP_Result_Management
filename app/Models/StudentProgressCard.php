<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProgressCard extends Model
{
    protected $table = 'student_progress_cards';

    protected $fillable = [
        'student_id',
        'skill_id',       // ← Added
        'exam_master_id',
        'academic_year_id',
        'standard_id',
        'division_id',

        'mother_tongue',
        'blood_group',
        'phone',
        'email',
        'school_timing',
        'address',

        'term1_weight',
        'term1_height',
        'term2_weight',
        'term2_height',

        'passed_promoted_to',
        'school_reopens_on',

        'attendance_data',
        'descriptive_records',
        'second_term_grades',
        'class_test_data',
        'school_days_data',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'skill_id'            => 'integer',
        'attendance_data'     => 'array',
        'descriptive_records' => 'array',
        'second_term_grades'  => 'array',
        'class_test_data'     => 'array',
        'school_days_data'    => 'array',
    ];
}