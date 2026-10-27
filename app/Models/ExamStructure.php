<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ExamStructure extends Model
{
    protected $table = 'exam_structures';

    protected $fillable = [
        'standard_key', 'standard_name',
        'exam_type', 'exam_type_order',
        'subject_type', 'subject_name', 'subject_code', 'display_order',
        'theory_max_marks', 'theory_passing_marks',
        'oral_max_marks', 'oral_passing_marks',
        'practical_max_marks', 'practical_passing_marks',
        'total_max_marks', 'total_passing_marks',
        'passing_percentage', 'is_compulsory', 'is_active',
    ];

    protected $casts = [
        'theory_max_marks'        => 'float',
        'theory_passing_marks'    => 'float',
        'oral_max_marks'          => 'float',
        'oral_passing_marks'      => 'float',
        'practical_max_marks'     => 'float',
        'practical_passing_marks' => 'float',
        'total_max_marks'         => 'float',
        'total_passing_marks'     => 'float',
        'passing_percentage'      => 'float',
        'is_compulsory'           => 'bool',
        'is_active'               => 'bool',
    ];

    public const CACHE_KEY = 'exam_structures:structured:v1';

    public const EXAM_TYPE_ORDER = [
        'UNIT TEST 1' => 1,
        'UNIT TEST 2' => 2,
        'TERM 1'      => 3,
        'TERM 2'      => 4,
    ];

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget(self::CACHE_KEY);
        static::saved($forget);
        static::deleted($forget);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $m) {
            // Do NOT auto-compute totals.
            // Excel-specified totals may differ from the sum of components
            // (e.g. 11Sci Term 1: 50 theory + 25 oral = 75, but Grand Total = 100).
            // Only set exam_type_order here.
            $m->exam_type_order = self::EXAM_TYPE_ORDER[$m->exam_type] ?? 0;
        });
    }
}