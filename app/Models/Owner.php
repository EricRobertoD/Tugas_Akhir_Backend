<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Owner extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'owner';
    protected $primaryKey = 'id_owner';
    protected $fillable = [
        'id_owner',
        'nama_owner',
        'email_owner',
        'password',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
