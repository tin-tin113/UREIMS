<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtensionProgramMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension_program_id',
        'name',
        'responsibility',
    ];

    /* ---- Relationships ---- */

    public function program()
    {
        return $this->belongsTo(ExtensionProgram::class, 'extension_program_id');
    }
}
