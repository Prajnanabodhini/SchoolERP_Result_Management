<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function permissions()
    {
        return $this->hasMany(RolePermission::class);
    }
}
