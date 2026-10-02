<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSkillSubject extends Model
{
    protected $table = 'student_skill_subjects';

    protected $fillable = [
        'academic_year_id',
        'student_id',
        'subject_id',
        'created_by',
        'updated_by'
    ];
    protected $casts = [
        'academic_year_id' => 'integer',
        'student_id'       => 'integer',
        'subject_id'       => 'integer',
        'updated_by'       => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
