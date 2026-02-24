<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtensionBeneficiary extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension_project_id',
        'name',
        'address',
        'contact_no',
        'organization',
    ];

    /* ---- Relationships ---- */

    public function project()
    {
        return $this->belongsTo(ExtensionProject::class, 'extension_project_id');
    }
}
