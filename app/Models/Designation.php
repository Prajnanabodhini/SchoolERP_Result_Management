<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Section;
use App\Models\UserDesignation;

class Designation extends Model
{
    protected $fillable = [
        'designation_name',
        'designation_code',
        'section_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo(
            Section::class,
            'section_id'
        );
    }

    public function userDesignations()
    {
        return $this->hasMany(
            UserDesignation::class,
            'designation_id'
        );
    }
}