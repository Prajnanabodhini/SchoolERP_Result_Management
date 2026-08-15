<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeMstStudent extends Model
{
    protected $connection = 'sqlsrv_olderp';

    protected $table = 'FeeMstStudent';

    protected $primaryKey = 'Studentid';

    public $timestamps = false;

    protected $guarded = [];

    public function academic()
    {
        return $this->hasOne(
            SubStudentMst::class,
            'Studentid',
            'Studentid'
        );
    }
}