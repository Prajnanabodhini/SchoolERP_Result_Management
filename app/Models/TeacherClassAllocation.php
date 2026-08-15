<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherClassAllocation extends Model
{
    protected $fillable = [
        'user_id',
        'academic_year_id',
        'section_id',
        'standard_id',
        'division_id',
        'is_class_teacher',
    ];

    public function teacher()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function academicYear()
    {
        return $this->belongsTo(
            AcademicYear::class,
            'academic_year_id'
        );
    }

    public function section()
    {
        return $this->belongsTo(
            Section::class,
            'section_id'
        );
    }

    public function standard()
    {
        return $this->belongsTo(
            Standard::class,
            'standard_id'
        );
    }

    public function division()
    {
        return $this->belongsTo(
            Division::class,
            'division_id'
        );
    }

    public function subjectAllocations()
    {
        return $this->hasMany(
            TeacherSubjectAllocation::class,
            'teacher_class_allocation_id'
        );
    }

    /*
    |----------------------------------------------------------------------
    | Alias
    |----------------------------------------------------------------------
    */

    public function subjects()
    {
        return $this->hasMany(
            TeacherSubjectAllocation::class,
            'teacher_class_allocation_id'
        );
    }
}