<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardWiseSubject extends Model
{
    protected $table = 'standard_wise_subjects';

    protected $fillable = [
    'standard_id',
    'subject_id',
    'standard_name',
    'subject_name',
    'sort_order',
    'is_optional',
    'is_active',
];

    public function standard()
    {
        return $this->belongsTo(
            Standard::class,
            'standard_id'
        );
    }

    public function subject()
    {
        return $this->belongsTo(
            Subject::class,
            'subject_id'
        );
    }
}