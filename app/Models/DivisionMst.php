<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DivisionMst extends Model
{
    protected $connection = 'sqlsrv_olderp';

    protected $table = 'DivisionMst';

    protected $primaryKey = 'divisionid';

    // public $timestamps = false;

    protected $guarded = [];

    protected $fillable = [
        'division',
        'standardid',
        'AddUid',
        'EditUid',
        'yearid',
        'sectionid',
        'divisionid'
    ];

    public $timestamps = false;
}