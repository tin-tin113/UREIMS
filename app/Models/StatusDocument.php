<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'phase',
        'label',
        'document_type',
        'file_name',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    /** Known document types (editable after upload) */
    public const DOCUMENT_TYPES = [
        'proposal'       => 'Proposal Document',
        'cover_letter'   => 'Cover Letter',
        'proponent_bio'  => 'Proponent Profile / Bio',
        'moa_mou'        => 'MOA / MOU',
        'endorsement'    => 'Endorsement Letter',
        'budget'         => 'Budget Breakdown',
        'workplan'       => 'Work Plan / Timeline',
        'completion'     => 'Completion / Terminal Report',
        'monitoring'     => 'Monitoring Report',
        'evaluation'     => 'Evaluation Report',
        'attendance'     => 'Attendance Sheet',
        'photo_doc'      => 'Photo Documentation',
        'certificate'    => 'Certificate',
        'data_set'       => 'Data Set',
        'supporting'     => 'Supporting Document',
        'other'          => 'Other',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    /* ---- Relationships ---- */

    public function documentable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /* ---- Accessors ---- */

    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    public function getFileExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    public function getDocumentTypeNameAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? ($this->document_type ?? 'Unclassified');
    }
}
