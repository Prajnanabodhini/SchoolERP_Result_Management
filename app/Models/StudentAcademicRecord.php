<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAcademicRecord extends Model
{
public function student()
{
    return $this->belongsTo(Student::class);
}

    protected $fillable = [
    'student_id',
    'academic_year_id',
    'standard_id',
    'division_id',
    'roll_no',
    'registration_no',
    'student_name',
    'old_substudent_id',
    'is_active',
];
}