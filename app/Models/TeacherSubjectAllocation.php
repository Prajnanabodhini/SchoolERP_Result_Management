<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubjectAllocation extends Model
{
    protected $fillable = [
        'teacher_class_allocation_id',
        'subject_id',
        'exam_master_id'
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

    public function teacherClassAllocation()
    {
        return $this->belongsTo(
            TeacherClassAllocation::class,
            'teacher_class_allocation_id'
        );
    }

    // Alias used by MarkEntryController
    public function allocation()
    {
        return $this->belongsTo(
            TeacherClassAllocation::class,
            'teacher_class_allocation_id'
        );
    }
}