<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address'];

    /* ---- Relationships ---- */

    public function extensionPrograms()
    {
        return $this->hasMany(ExtensionProgram::class);
    }

    public function extensionProjects()
    {
        return $this->hasMany(ExtensionProject::class);
    }
}
