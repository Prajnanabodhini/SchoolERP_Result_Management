<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role_id',
        'menu_name',
        'can_view',
        'can_add',
        'can_edit',
        'can_delete',
        'can_print',
        'can_export',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
