<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Standard extends Model
{
    protected $fillable = [
        'standard_name',
        'display_order',
        'is_active',
    ];
}