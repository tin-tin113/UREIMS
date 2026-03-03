<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtensionProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'ic_no',
        'title',
        'proponent_name',
        'division_unit',
        'proponent_address',
        'contact_no',
        'cooperating_entities',
        'cooperating_entity_address',
        'program_location',
        'beneficiary_class',
        'target_recipients',
        'funding_chmsu_gaa',
        'funding_chmsu_gaa_note',
        'funding_chmsu_stf',
        'funding_collaborator',
        'funding_collaborator_note',
        'funding_total',
        'target_start_date',
        'target_end_date',
        'program_leader',
        'rationale',
        'conceptual_framework',
        'general_objective',
        'specific_objectives',
        'methodology',
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
            'funding_chmsu_gaa'  => 'decimal:2',
            'funding_chmsu_stf'  => 'decimal:2',
            'funding_collaborator' => 'decimal:2',
            'funding_total'      => 'decimal:2',
            'draft_data'         => 'array',
        ];
    }

    /* ---- Relationships ---- */

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->hasMany(ExtensionProgramMember::class);
    }

    public function projects()
    {
        return $this->hasMany(ExtensionProject::class);
    }

    public function evaluationForms()
    {
        return $this->hasMany(EvaluationForm::class);
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
}
