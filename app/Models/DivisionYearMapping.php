<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DivisionYearMapping extends Model
{
    protected $fillable = [
        'academic_year_id',
        'division_id',
        'old_standard_id',
        'old_division_id'
    ];
}