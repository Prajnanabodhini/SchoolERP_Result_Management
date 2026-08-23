<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDesignation extends Model
{
    protected $table = 'user_designations';

    protected $fillable = [
        'user_id',
        'designation_id',
        'academic_year_id',
        'standard_id',
        'division_id',
    ];


    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DESIGNATION
    |--------------------------------------------------------------------------
    */

    public function designation(): BelongsTo
    {
        return $this->belongsTo(
            Designation::class,
            'designation_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACADEMIC YEAR
    |--------------------------------------------------------------------------
    */

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(
            AcademicYear::class,
            'academic_year_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STANDARD
    |--------------------------------------------------------------------------
    */

    public function standard(): BelongsTo
    {
        return $this->belongsTo(
            Standard::class,
            'standard_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DIVISION
    |--------------------------------------------------------------------------
    */

    public function division(): BelongsTo
    {
        return $this->belongsTo(
            Division::class,
            'division_id'
        );
    }
}