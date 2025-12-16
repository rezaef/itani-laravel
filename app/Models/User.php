<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    public $timestamps = false; // karena tabel kamu pakai created_at saja

    protected $fillable = ['name', 'username', 'role', 'password_hash', 'created_at'];

    protected $hidden = ['password_hash'];

    // PENTING: Laravel auth pakai kolom ini untuk cek password
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
