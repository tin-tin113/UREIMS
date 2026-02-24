<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusTransitionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'transitionable_type',
        'transitionable_id',
        'from_status',
        'to_status',
        'transitioned_by',
        'is_bypass',
        'bypass_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_bypass' => 'boolean',
        ];
    }

    /* ---- Relationships ---- */

    public function transitionable()
    {
        return $this->morphTo();
    }

    public function transitioner()
    {
        return $this->belongsTo(User::class, 'transitioned_by');
    }
}
