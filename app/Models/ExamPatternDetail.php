<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPatternDetail extends Model
{
    protected $fillable = [
        'exam_pattern_id',
        'standard_id',
        'subject_id',
        'display_order'
    ];


    public function examPattern()
    {
        return $this->belongsTo(ExamPattern::class);
    }


    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }


    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}