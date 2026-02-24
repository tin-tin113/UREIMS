<?php

namespace App\Services;

use App\Models\ExtensionProgram;
use App\Models\ExtensionProject;

class WorkflowService
{
    /* ================================================================
     *  PHASE DEFINITIONS
     * ================================================================ */

    const PHASES = ['proposal', 'under_review', 'approved', 'ongoing', 'completed'];

    const PHASE_LABELS = [
        'proposal'     => 'Proposal',
        'under_review' => 'Under Review',
        'approved'     => 'Approved',
        'ongoing'      => 'Ongoing',
        'completed'    => 'Completed',
    ];

    const PHASE_COLORS = [
        'proposal'     => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'border' => 'border-yellow-200', 'dot' => 'bg-yellow-400', 'ring' => 'ring-yellow-400'],
        'under_review' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'dot' => 'bg-purple-500', 'ring' => 'ring-purple-400'],
        'approved'     => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'border' => 'border-blue-200',   'dot' => 'bg-blue-500',   'ring' => 'ring-blue-400'],
        'ongoing'      => ['bg' => 'bg-cyan-100',   'text' => 'text-cyan-700',   'border' => 'border-cyan-200',   'dot' => 'bg-cyan-500',   'ring' => 'ring-cyan-400'],
        'completed'    => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'border' => 'border-green-200',  'dot' => 'bg-green-500',  'ring' => 'ring-green-400'],
    ];

    const PHASE_ICONS = [
        'proposal'     => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'under_review' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        'approved'     => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'ongoing'      => 'M13 10V3L4 14h7v7l9-11h-7z',
        'completed'    => 'M5 13l4 4L19 7',
    ];

    /* ================================================================
     *  ALLOWED FILE FORMATS PER PHASE
     * ================================================================ */

    const ALLOWED_FORMATS = [
        'proposal'     => ['pdf', 'doc', 'docx'],
        'under_review' => ['pdf', 'doc', 'docx'],
        'approved'     => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
        'ongoing'      => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
        'completed'    => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'mp4', 'pptx'],
    ];

    const MAX_FILE_SIZE = [
        'proposal'     => 10240,   // 10 MB
        'under_review' => 10240,
        'approved'     => 10240,
        'ongoing'      => 20480,   // 20 MB
        'completed'    => 51200,   // 50 MB
    ];

    /* ================================================================
     *  UPLOAD ROLES PER PHASE
     * ================================================================ */

    const UPLOAD_ROLES = [
        'proposal'     => ['admin', 'extension_staff'],
        'under_review' => ['admin'],
        'approved'     => ['admin'],
        'ongoing'      => ['admin', 'extension_staff'],
        'completed'    => ['admin', 'extension_staff'],
    ];

    /* ================================================================
     *  PROGRAM REQUIREMENTS
     * ================================================================ */

    const PROGRAM_REQUIRED_FIELDS = [
        'proposal' => [
            'title'             => 'Program Title',
            'proponent_name'    => 'Proponent Name',
            'campus_id'         => 'Campus',
            'rationale'         => 'Rationale',
            'general_objective' => 'General Objective',
        ],
        'under_review' => [],
        'approved'     => [],
        'ongoing'      => [
            'target_start_date' => 'Target Start Date',
            'program_leader'    => 'Program Leader',
        ],
        'completed' => [
            'target_end_date' => 'Target End Date',
        ],
    ];

    const PROGRAM_REQUIRED_DOCS = [
        'proposal'     => ['Proposal Document'],
        'under_review' => ['Review Evaluation Form'],
        'approved'     => ['Approval Letter'],
        'ongoing'      => [],
        'completed'    => ['Terminal/Completion Report'],
    ];

    /* ================================================================
     *  PROJECT REQUIREMENTS
     * ================================================================ */

    const PROJECT_REQUIRED_FIELDS = [
        'proposal' => [
            'title'       => 'Project Title',
            'description' => 'Description',
            'campus_id'   => 'Campus',
        ],
        'under_review' => [],
        'approved'     => [],
        'ongoing'      => [
            'target_start_date'  => 'Target Start Date',
            'persons_responsible' => 'Person(s) Responsible',
        ],
        'completed' => [
            'target_end_date' => 'Target End Date',
        ],
    ];

    const PROJECT_REQUIRED_DOCS = [
        'proposal'     => ['Project Proposal Document'],
        'under_review' => ['Review Evaluation Form'],
        'approved'     => ['Approval Letter'],
        'ongoing'      => [],
        'completed'    => ['Completion Report'],
    ];

    /* ================================================================
     *  HELPERS
     * ================================================================ */

    public static function getPhaseIndex(string $phase): int
    {
        $idx = array_search($phase, self::PHASES);
        return $idx !== false ? $idx : 0;
    }

    public static function getNextPhase(string $current): ?string
    {
        $i = self::getPhaseIndex($current);
        return self::PHASES[$i + 1] ?? null;
    }

    public static function getPreviousPhase(string $current): ?string
    {
        $i = self::getPhaseIndex($current);
        return $i > 0 ? self::PHASES[$i - 1] : null;
    }

    public static function canUserUpload(string $phase, string $role): bool
    {
        return in_array($role, self::UPLOAD_ROLES[$phase] ?? []);
    }

    /* ================================================================
     *  REQUIREMENTS CHECK
     * ================================================================ */

    /**
     * Returns the requirements status for the model's CURRENT phase
     * (i.e. what must be done before leaving this phase).
     */
    public static function getRequirementsStatus($model): array
    {
        $isProgram = $model instanceof ExtensionProgram;
        $phase     = $model->status;

        $requiredFields = $isProgram
            ? (self::PROGRAM_REQUIRED_FIELDS[$phase] ?? [])
            : (self::PROJECT_REQUIRED_FIELDS[$phase] ?? []);

        $requiredDocs = $isProgram
            ? (self::PROGRAM_REQUIRED_DOCS[$phase] ?? [])
            : (self::PROJECT_REQUIRED_DOCS[$phase] ?? []);

        // Check fields
        $fieldStatus = [];
        foreach ($requiredFields as $field => $label) {
            $fieldStatus[$field] = [
                'label' => $label,
                'met'   => ! empty($model->$field),
            ];
        }

        // Check documents
        $uploadedDocs = $model->statusDocuments()->where('phase', $phase)->get();
        $docStatus = [];
        foreach ($requiredDocs as $docLabel) {
            $docStatus[$docLabel] = [
                'label' => $docLabel,
                'met'   => $uploadedDocs->where('label', $docLabel)->isNotEmpty(),
            ];
        }

        $allFieldsMet = collect($fieldStatus)->every(fn ($f) => $f['met']);
        $allDocsMet   = collect($docStatus)->every(fn ($d) => $d['met']);

        return [
            'fields'         => $fieldStatus,
            'documents'      => $docStatus,
            'all_fields_met' => $allFieldsMet,
            'all_docs_met'   => $allDocsMet,
            'can_advance'    => $allFieldsMet && $allDocsMet,
        ];
    }

    /**
     * Full advancement check including special conditions.
     */
    public static function canAdvance($model): array
    {
        $req    = self::getRequirementsStatus($model);
        $errors = [];

        if (! $req['all_fields_met']) {
            foreach (collect($req['fields'])->filter(fn ($f) => ! $f['met']) as $f) {
                $errors[] = "Missing required field: {$f['label']}";
            }
        }

        if (! $req['all_docs_met']) {
            foreach (collect($req['documents'])->filter(fn ($d) => ! $d['met']) as $d) {
                $errors[] = "Missing required document: {$d['label']}";
            }
        }

        // Completing a program requires all its projects to be completed
        if ($model->status === 'ongoing' && $model instanceof ExtensionProgram) {
            $incomplete = $model->projects()->where('status', '!=', 'completed')->count();
            if ($incomplete > 0) {
                $errors[] = "All projects must be completed first ({$incomplete} still incomplete)";
            }
        }

        return [
            'can_advance'  => empty($errors),
            'errors'       => $errors,
            'next_phase'   => self::getNextPhase($model->status),
            'requirements' => $req,
        ];
    }
}
