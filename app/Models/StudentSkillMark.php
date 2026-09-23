<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSkillMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'exam_master_id',
        'student_id',
        'subject_id',
        'marks_obtained',
        'max_marks',
        'passing_marks',
        'grade',
        'created_by',
        'updated_by',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function exam()
    {
        return $this->belongsTo(ExamMaster::class, 'exam_master_id');
    }
}