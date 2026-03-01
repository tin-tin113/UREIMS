<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EvaluationForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension_program_id',
        'title',
        'description',
        'is_active',
        'access_token',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /* ---- Boot ---- */

    protected static function booted(): void
    {
        static::creating(function (self $form) {
            if (empty($form->access_token)) {
                $form->access_token = Str::random(48);
            }
        });
    }

    /* ---- Relationships ---- */

    public function program()
    {
        return $this->belongsTo(ExtensionProgram::class, 'extension_program_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function criteria()
    {
        return $this->hasMany(EvaluationCriteria::class)->orderBy('sort_order');
    }

    public function responses()
    {
        return $this->hasMany(EvaluationResponse::class);
    }

    /* ---- Accessors ---- */

    /** Public URL for this evaluation form */
    public function getPublicUrlAttribute(): string
    {
        return route('evaluation.public.show', $this->access_token);
    }

    /** Number of rating-type criteria (for score computations) */
    public function getRatingCriteriaCountAttribute(): int
    {
        return $this->criteria()->where('type', 'rating')->count();
    }
}
