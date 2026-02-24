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
        'type',
        'sector',
        'male_count',
        'female_count',
        'total_count',
    ];

    public const TYPES = ['individual', 'organization', 'community'];

    public const SECTORS = [
        'farmer'      => 'Farmer / Fisherfolk',
        'student'     => 'Student',
        'youth'       => 'Out-of-School Youth',
        'women'       => 'Women',
        'senior'      => 'Senior Citizen',
        'indigenous'  => 'Indigenous People',
        'pwd'         => 'Person with Disability',
        'government'  => 'Government Employee',
        'private'     => 'Private Sector',
        'community'   => 'Community / Barangay',
        'other'       => 'Other',
    ];

    protected function casts(): array
    {
        return [
            'male_count'   => 'integer',
            'female_count' => 'integer',
            'total_count'  => 'integer',
        ];
    }

    /* ---- Relationships ---- */

    public function project()
    {
        return $this->belongsTo(ExtensionProject::class, 'extension_project_id');
    }
}
