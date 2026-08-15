<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';

    protected $fillable = [
        'subject_name',
        'subject_code',
        'short_name',
        'section_id',
        'subject_type_id',
        'display_order',
        'is_active',
    ];


    /*
    |--------------------------------------------------------------------------
    | SUBJECT TYPE
    |--------------------------------------------------------------------------
    */

    public function subjectType()
    {
        return $this->belongsTo(
            SubjectType::class,
            'subject_type_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STANDARD-WISE SUBJECT MAPPINGS
    |--------------------------------------------------------------------------
    */

    public function standardWiseSubjects()
    {
        return $this->hasMany(
            StandardWiseSubject::class,
            'subject_id'
        );
    }
}