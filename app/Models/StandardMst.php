<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardMst extends Model
{
    protected $connection = 'sqlsrv_olderp';

    protected $table = 'StandardMst';

    protected $primaryKey = 'standardid';

    public $timestamps = false;

    protected $guarded = [];
}