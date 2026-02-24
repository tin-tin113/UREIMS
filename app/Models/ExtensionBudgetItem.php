<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtensionBudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension_project_id',
        'location',
        'item_description',
        'total_budget',
    ];

    protected function casts(): array
    {
        return [
            'total_budget' => 'decimal:2',
        ];
    }

    /* ---- Relationships ---- */

    public function project()
    {
        return $this->belongsTo(ExtensionProject::class, 'extension_project_id');
    }
}
