<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPatternAllocation extends Model
{
    protected $fillable = [
        'exam_master_id',
        'standard_id',
        'exam_pattern_id'
    ];

    public function pattern()
    {
        return $this->belongsTo(
            ExamPattern::class,
            'exam_pattern_id'
        );
    }
}