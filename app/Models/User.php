<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'role',
        'is_active',
        'campus_id',
    ];

    protected $appends = ['full_name'];

    protected function casts(): array
    {
        return [
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /* ---- Name Accessors ---- */

    /**
     * Get the user's full name (first + middle + last).
     * Also accessible via ->name for backward compatibility.
     */
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    /**
     * Backward-compatible accessor: $user->name returns full name.
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
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

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /* ---- Relationships ---- */

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

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
