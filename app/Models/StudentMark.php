<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMark extends Model
{
    protected $table = 'student_marks';

    protected $fillable = [

        'academic_year_id',
        'section_id',

        'standard_id',
        'division_id',

        'student_id',

        'exam_master_id',
        'subject_id',

        'teacher_subject_allocation_id',

        'theory_max_marks',
        'theory_passing_marks',
        'theory_obtained_marks',

        'oral_max_marks',
        'oral_passing_marks',
        'oral_obtained_marks',

        'practical_max_marks',
        'practical_passing_marks',
        'practical_obtained_marks',

        'is_absent',
        'is_locked',

        'created_by',
        'updated_by'
    ];

    public function subject()
{
    return $this->belongsTo(
        Subject::class,
        'subject_id'
    );
}

public function exam()
{
    return $this->belongsTo(
        ExamMaster::class,
        'exam_master_id'
    );
}
}