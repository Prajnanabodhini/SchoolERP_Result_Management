<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubStudentMst extends Model
{
    protected $connection = 'sqlsrv_olderp';

    protected $table = 'SubStudentMst';

    protected $primaryKey = 'SubStudid';

    public $timestamps = false;

    public function student()
    {
        return $this->belongsTo(
            FeeMstStudent::class,
            'Studentid',
            'Studentid'
        );
    }

    public function standard()
    {
        return $this->belongsTo(
            StandardMst::class,
            'standardid',
            'standardid'
        );
    }

    public function division()
    {
        return $this->belongsTo(
            DivisionMst::class,
            'divisionid',
            'divisionid'
        );
    }
}