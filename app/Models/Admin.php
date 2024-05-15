<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'admin';
    protected $primaryKey = 'id_admin';
    protected $fillable = [
        'id_admin',
        'nama_admin',
        'email_admin',
        'password',

    ];

    protected $hidden = [
        'password_admin',
        'remember_token',
    ];
}
