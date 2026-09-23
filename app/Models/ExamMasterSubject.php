<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\StandardWiseSubject;

class ExamMasterSubject extends Model
{
    protected $fillable = [
    'exam_master_id',
    'standard_id',
    'subject_id',
    'subject_name',
    'max_marks',
    'passing_marks',
    'display_order',
    'is_optional',

    // Excel / component snapshot columns
    'exam_type',
    'theory_max_marks',
    'theory_passing_marks',
    'oral_max_marks',
    'oral_passing_marks',
    'practical_max_marks',
    'practical_passing_marks',
    'total_max_marks',
    'total_passing_marks',
    'excel_row_number',
    'structure_source_hash',
];

    

public function standardWiseSubject()
{
    return $this->belongsTo(
        StandardWiseSubject::class,
        'subject_id'
    );
}
public function subject()
{
    return $this->belongsTo(
        Subject::class,
        'subject_id'
    );
}
    public function examMaster()
    {
        return $this->belongsTo(ExamMaster::class);
    }
public function examSubjects()
{
    return $this->hasMany(
        \App\Models\ExamMasterSubject::class,
        'exam_master_id'
    );
}
}