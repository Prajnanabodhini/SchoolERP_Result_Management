<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamMaster extends Model
{
    protected $fillable = [
        'exam_name',
        'exam_pattern_id',
        'standard_id',

        'max_marks',
        'passing_marks',

        'has_theory',
        'theory_max_marks',
        'theory_passing_marks',

        'has_oral',
        'oral_max_marks',
        'oral_passing_marks',

        'has_practical',
        'practical_max_marks',
        'practical_passing_marks',

        'display_order',
        'is_active',
    ];

    /*
    |----------------------------------------------------------------------
    | Exam Pattern
    |----------------------------------------------------------------------
    */

    public function examPattern()
    {
        return $this->belongsTo(
            ExamPattern::class,
            'exam_pattern_id'
        );
    }

    /*
    |----------------------------------------------------------------------
    | Standard
    |----------------------------------------------------------------------
    */

    public function standard()
    {
        return $this->belongsTo(
            Standard::class,
            'standard_id'
        );
    }

    /*
    |----------------------------------------------------------------------
    | Exam Subjects
    |----------------------------------------------------------------------
    */

    public function examSubjects()
    {
        return $this->hasMany(
            ExamMasterSubject::class,
            'exam_master_id'
        );
    }

    /*
    |----------------------------------------------------------------------
    | Teacher Subject Allocations
    |----------------------------------------------------------------------
    */

    public function teacherSubjectAllocations()
    {
        return $this->hasMany(
            TeacherSubjectAllocation::class,
            'exam_master_id'
        );
    }
}