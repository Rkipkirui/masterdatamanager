<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SapUser extends Authenticatable
{
    protected $fillable = [
        'sap_user_code',
        'sap_user_name',
        'email',
        'user_code',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
