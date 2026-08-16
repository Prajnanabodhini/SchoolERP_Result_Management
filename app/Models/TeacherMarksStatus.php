<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Subject;


class TeacherMarksStatus extends Model
{
    protected $table = 'teacher_marks_status';

    protected $fillable = [ 'academic_year_id', 'exam_master_id', 'teacher_subject_allocation_id', 'standard_id', 'division_id', 'subject_id', 'teacher_id', 'status', ];

    // public function standardWiseSubject() { return $this->belongsTo( StandardWiseSubject::class, 'subject_id' ); }
public function subjectMapping()
{
    return $this->belongsTo(
        StandardWiseSubject::class,
        'subject_id'
    );
}
    
    public function teacherSubjectAllocation()
    {
        return $this->belongsTo(
            TeacherSubjectAllocation::class,
            'teacher_subject_allocation_id'
        );
    }
    
    public function exam()
    {
        return $this->belongsTo(
            \App\Models\ExamMaster::class,
            'exam_master_id'
        );
    }

    public function teacher()
    {
        return $this->belongsTo(
            \App\Models\User::class,
            'teacher_id'
        );
    }

    public function subject()
{
    return $this->belongsTo(
        Subject::class,
        'subject_id'
    );
}

    public function standard()
    {
        return $this->belongsTo(
            \App\Models\Standard::class,
            'standard_id'
        );
    }

    public function division()
    {
        return $this->belongsTo(
            \App\Models\Division::class,
            'division_id'
        );
    }
}