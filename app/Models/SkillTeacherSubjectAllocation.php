<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillTeacherSubjectAllocation extends Model
{
    protected $fillable = [
        'skill_teacher_class_allocation_id',
        'exam_master_id',
        'subject_id',
    ];

    public function classAllocation()
    {
        return $this->belongsTo(SkillTeacherClassAllocation::class, 'skill_teacher_class_allocation_id');
    }

    public function exam()
    {
        return $this->belongsTo(ExamMaster::class, 'exam_master_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}