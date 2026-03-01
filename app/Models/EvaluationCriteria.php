<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationCriteria extends Model
{
    use HasFactory;

    protected $table = 'evaluation_criteria';

    protected $fillable = [
        'evaluation_form_id',
        'label',
        'type',
        'sort_order',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'sort_order'  => 'integer',
            'is_required' => 'boolean',
        ];
    }

    /* ---- Relationships ---- */

    public function form()
    {
        return $this->belongsTo(EvaluationForm::class, 'evaluation_form_id');
    }

    public function answers()
    {
        return $this->hasMany(EvaluationAnswer::class, 'evaluation_criteria_id');
    }
}
