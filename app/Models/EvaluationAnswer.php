<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_response_id',
        'evaluation_criteria_id',
        'numeric_value',
        'text_value',
    ];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'integer',
        ];
    }

    /* ---- Relationships ---- */

    public function response()
    {
        return $this->belongsTo(EvaluationResponse::class, 'evaluation_response_id');
    }

    public function criteria()
    {
        return $this->belongsTo(EvaluationCriteria::class, 'evaluation_criteria_id');
    }
}
