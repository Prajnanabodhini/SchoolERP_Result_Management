<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProgressCard extends Model
{
    protected $table = 'student_progress_cards';

    protected $fillable = [
        'pen', // Renamed from student_id
        'skill_id',
        'exam_master_id',
        'academic_year_id',
        'standard_id',
        'division_id',

        // Snapshot Identity Fields
        'full_student_name',
        'father_name',
        'mother_name',
        'gender',
        'date_of_birth',
        'gr_no',
        'roll_no',
        'aadhar_no',

        // Contact & Info
        'mother_tongue',
        'blood_group',
        'phone',
        'email',
        'school_timing',
        'address',

        // Term Details
        'term1_weight',
        'term1_height',
        'term2_weight',
        'term2_height',
        'passed_promoted_to',
        'school_reopens_on',

        // Array / JSON Data
        'attendance_data',
        'descriptive_records',
        'second_term_grades',
        'class_test_data',
        'school_days_data',
        'teacher_remark',
        'academic_grades',
        'skill_grades',

        // Snapshot Results
        'percentage',
        'grade',
        'result',
        'total_max_marks',
        'total_obtained_marks',

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
        'teacher_remark'      => 'array',
        'academic_grades'     => 'array',
        'skill_grades'        => 'array',
    ];
}