<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardSubject extends Model
{
    protected $fillable = [
        'standard_id',
        'subject_id'
    ];
}