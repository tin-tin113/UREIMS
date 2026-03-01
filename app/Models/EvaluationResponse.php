<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_form_id',
        'extension_activity_id',
        'respondent_name',
        'respondent_email',
        'respondent_contact',
        'respondent_organization',
        'respondent_gender',
        'submission_type',
        'encoded_by',
        'total_score',
        'average_score',
        'rated_criteria_count',
    ];

    protected function casts(): array
    {
        return [
            'total_score'          => 'decimal:2',
            'average_score'        => 'decimal:2',
            'rated_criteria_count' => 'integer',
        ];
    }

    /* ---- Relationships ---- */

    public function form()
    {
        return $this->belongsTo(EvaluationForm::class, 'evaluation_form_id');
    }

    public function activity()
    {
        return $this->belongsTo(ExtensionActivity::class, 'extension_activity_id');
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function answers()
    {
        return $this->hasMany(EvaluationAnswer::class);
    }

    /* ---- Helpers ---- */

    /** Recompute total / average score from answers and persist */
    public function recomputeScores(): void
    {
        $ratingAnswers = $this->answers()
            ->whereNotNull('numeric_value')
            ->get();

        $this->rated_criteria_count = $ratingAnswers->count();
        $this->total_score          = $ratingAnswers->sum('numeric_value');
        $this->average_score        = $this->rated_criteria_count > 0
            ? (float) round($this->total_score / $this->rated_criteria_count, 2)
            : 0;

        $this->save();
    }
}
