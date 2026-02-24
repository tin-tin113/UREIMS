<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ---- Role Helpers ---- */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isExtensionStaff(): bool
    {
        return $this->role === 'extension_staff';
    }

    /* ---- Relationships ---- */

    public function createdPrograms()
    {
        return $this->hasMany(ExtensionProgram::class, 'created_by');
    }

    public function createdProjects()
    {
        return $this->hasMany(ExtensionProject::class, 'created_by');
    }

    public function createdActivities()
    {
        return $this->hasMany(ExtensionActivity::class, 'created_by');
    }
}
