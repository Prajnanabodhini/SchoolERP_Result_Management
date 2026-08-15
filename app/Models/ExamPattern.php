<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPattern extends Model
{
    protected $fillable = [
        'pattern_name',
        'pattern_type',
        'description',
        'is_active'
    ];

    public function details()
    {
        return $this->hasMany(
            ExamPatternDetail::class
        );
    }
}