<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectType extends Model
{
    protected $fillable = [
        'type_name',
        'description',
        'is_active'
    ];
}
