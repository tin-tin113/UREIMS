<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtensionProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension_program_id',
        'title',
        'description',
        'persons_responsible',
        'budget_requirement',
        'budget_source',
        'indicators_output',
        'target_start_date',
        'target_end_date',
        'status',
        'draft_data',
        'campus_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_start_date'  => 'date',
            'target_end_date'    => 'date',
            'budget_requirement' => 'decimal:2',
            'draft_data'         => 'array',
        ];
    }

    /* ---- Relationships ---- */

    public function program()
    {
        return $this->belongsTo(ExtensionProgram::class, 'extension_program_id');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities()
    {
        return $this->hasMany(ExtensionActivity::class);
    }

    public function beneficiaries()
    {
        return $this->hasMany(ExtensionBeneficiary::class);
    }

    public function budgetItems()
    {
        return $this->hasMany(ExtensionBudgetItem::class);
    }

    public function statusDocuments()
    {
        return $this->morphMany(StatusDocument::class, 'documentable');
    }

    public function transitionLogs()
    {
        return $this->morphMany(StatusTransitionLog::class, 'transitionable');
    }

    /* ---- Accessors ---- */

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'completed'
            && $this->target_end_date
            && $this->target_end_date->isPast();
    }

    /** Total budget from all budget line items for this project */
    public function getTotalBudgetSpentAttribute(): float
    {
        return (float) $this->budgetItems()->sum('total_budget');
    }
}
