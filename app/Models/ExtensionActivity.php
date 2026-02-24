<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtensionActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension_project_id',
        'title',
        'description',
        'persons_responsible',
        'budget_requirement',
        'indicators_output',
        'target_date',
        'completion_date',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_date'        => 'date',
            'completion_date'    => 'date',
            'budget_requirement' => 'decimal:2',
        ];
    }

    /* ---- Relationships ---- */

    public function project()
    {
        return $this->belongsTo(ExtensionProject::class, 'extension_project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ---- Accessors ---- */

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'completed'
            && $this->target_date
            && $this->target_date->isPast();
    }
}
