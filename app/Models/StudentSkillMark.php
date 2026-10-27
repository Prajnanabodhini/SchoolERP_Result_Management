<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSkillMark extends Model
{
    use HasFactory;

    protected $table = 'student_skill_marks';

    protected $fillable = [
        'academic_year_id',
        'standard_id',
        'division_id',
        'exam_master_id',
        'student_id',
        'student_name',
        'roll_no',
        'gr_no',
        'skill_id',
        'skill_slot',
        'category',
        'subject_name',
        'max_marks',
        'marks_obtained',
        'is_absent',
        'total_marks',
        'grade',
        'skill_teacher_name',
        'is_locked', // ← Added
    ];

    protected $casts = [
        'skill_id'       => 'integer',
        'is_absent'      => 'boolean',
        'is_locked'      => 'boolean', // ← Added
        'max_marks'      => 'float',
        'marks_obtained' => 'float',
        'total_marks'    => 'float',
    ];
}