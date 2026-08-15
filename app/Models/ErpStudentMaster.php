<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpStudentMaster extends Model
{
    protected $table = 'erp_student_master';

    protected $fillable = [
        'academic_year_id',
        'old_student_id',
        'gr_no',
        'roll_no',
        'student_name',
        'father_name',
        'mobile_no',
        'old_standard_id',
        'old_division_id',
        'standard_code',
        'standard_name',
        'division_name',
    ];
}