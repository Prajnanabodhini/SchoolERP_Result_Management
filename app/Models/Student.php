<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
public function academicRecords()
{
    return $this->hasMany(StudentAcademicRecord::class);
}

    protected $fillable = [
    'old_student_id',
    'registration_no',
    'admission_no',

    'first_name',
    'last_name',
    'mobile',

    'student_name',

    'father_name',
    'mother_name',

    'father_mobile',
    'mother_mobile',

    'gender',
    'date_of_birth',

    'aadhaar_no',

    'address',

    'religion',
    'nationality',

    'admission_date',
    'saral_id',

    'is_active'
	];
}