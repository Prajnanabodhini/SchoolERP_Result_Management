<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UserDesignation;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
'name',
'email',
'role',
'is_active',
'password',
];

/*
|--------------------------------------------------------------------------
| USER DESIGNATIONS
|--------------------------------------------------------------------------
*/

public function userDesignations(): HasMany
{
    return $this->hasMany(
        UserDesignation::class,
        'user_id'
    );
}

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            
        ];
    }

public function hasRole($role)
{
    return $this->role == $role;
}

}
