<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardYearMapping extends Model
{
    protected $fillable = [
        'academic_year_id',
        'standard_id',
        'old_standard_id'
    ];
}