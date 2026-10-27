<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSkillObservation extends Model
{
    protected $table = 'student_skill_observations';

    protected $fillable = [
        'academic_year_id',
        'standard_id',
        'division_id',
        'skill_teacher_id',
        'exam_master_id',
        'student_id',
        'subject_id',
        'observations',
        'overall_grade',
        'is_absent',
        'remark',
        'created_by',
        'updated_by',
        // New Fields
        'ground_activities_grade',
        'trg_senses_grade',
        'trg_senses_remark',
        'assembly_grade',
        'activity_book_grade',
        'activity_book_remark',
        'general_skills_grade',
    ];

    protected $casts = [
        'observations'     => 'array',
        'is_absent'        => 'integer',
        'standard_id'      => 'integer',
        'division_id'      => 'integer',
        'skill_teacher_id' => 'integer',
    ];

    public function standard()
    {
        return $this->belongsTo(\App\Models\Standard::class, 'standard_id');
    }

    public function division()
    {
        return $this->belongsTo(\App\Models\Division::class, 'division_id');
    }

    public function subject()
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function exam()
    {
        return $this->belongsTo(\App\Models\ExamMaster::class, 'exam_master_id');
    }

    public function skillTeacher()
    {
        return $this->belongsTo(\App\Models\User::class, 'skill_teacher_id');
    }
}