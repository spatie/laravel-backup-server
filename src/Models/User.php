<?php

namespace Spatie\BackupServer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\BackupServer\Tests\Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    public $table = 'backup_server_users';

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
