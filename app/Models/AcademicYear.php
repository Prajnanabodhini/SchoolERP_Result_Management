<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'year_name',
        'start_date',
        'end_date',
        'is_current',
        'is_active'
    ];
}