<?php

namespace App\Models;


use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract
{

    use Authenticatable, Authorizable;

    protected $guarded = [];

    protected $casts = [
        'is_bot'                   => 'boolean',
        'is_premium'               => 'boolean',
        'added_to_attachment_menu' => 'boolean',
        'can_access'               => 'boolean',
        'can_invite'               => 'boolean',
    ];

}
