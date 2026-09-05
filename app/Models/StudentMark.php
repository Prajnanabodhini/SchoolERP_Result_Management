<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMark extends Model
{
    protected $table = 'student_marks';


    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'academic_year_id',
        'section_id',

        'standard_id',
        'division_id',

        'student_id',

        'exam_master_id',
        'subject_id',

        'teacher_subject_allocation_id',


        /*
        |--------------------------------------------------------------------------
        | THEORY
        |--------------------------------------------------------------------------
        */

        'theory_max_marks',
        'theory_passing_marks',
        'theory_obtained_marks',


        /*
        |--------------------------------------------------------------------------
        | ORAL
        |--------------------------------------------------------------------------
        */

        'oral_max_marks',
        'oral_passing_marks',
        'oral_obtained_marks',


        /*
        |--------------------------------------------------------------------------
        | PRACTICAL
        |--------------------------------------------------------------------------
        */

        'practical_max_marks',
        'practical_passing_marks',
        'practical_obtained_marks',


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        'is_absent',

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Optional student flag.
        |
        */

        'is_optional',

        'is_locked',


        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */

        'created_by',
        'updated_by',
    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'academic_year_id' =>
            'integer',

        'section_id' =>
            'integer',

        'standard_id' =>
            'integer',

        'division_id' =>
            'integer',

        'student_id' =>
            'integer',

        'exam_master_id' =>
            'integer',

        'subject_id' =>
            'integer',

        'teacher_subject_allocation_id' =>
            'integer',


        'theory_max_marks' =>
            'float',

        'theory_passing_marks' =>
            'float',

        'theory_obtained_marks' =>
            'float',


        'oral_max_marks' =>
            'float',

        'oral_passing_marks' =>
            'float',

        'oral_obtained_marks' =>
            'float',


        'practical_max_marks' =>
            'float',

        'practical_passing_marks' =>
            'float',

        'practical_obtained_marks' =>
            'float',


        'is_absent' =>
            'integer',

        'is_optional' =>
            'integer',

        'is_locked' =>
            'integer',

        'created_by' =>
            'integer',

        'updated_by' =>
            'integer',
    ];


    /*
    |--------------------------------------------------------------------------
    | SUBJECT
    |--------------------------------------------------------------------------
    */

    public function subject()
    {
        return $this->belongsTo(
            Subject::class,
            'subject_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EXAM
    |--------------------------------------------------------------------------
    */

    public function exam()
    {
        return $this->belongsTo(
            ExamMaster::class,
            'exam_master_id'
        );
    }
}