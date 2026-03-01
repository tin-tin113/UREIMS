<?php

namespace App\Services;

use App\Models\ExtensionActivity;
use App\Models\ExtensionProgram;
use App\Models\ExtensionProject;

/**
 * ═══════════════════════════════════════════════════════════════════════
 *  WorkflowService — Extension Program Management Workflow Engine
 * ═══════════════════════════════════════════════════════════════════════
 *
 * Governs the lifecycle of Programs, Projects, and Activities through
 * four clearly-defined phases:  DRAFT → PROPOSAL → ONGOING → COMPLETED
 *
 * Responsibilities:
 *   1.  Phase definitions and labels
 *   2.  Per-phase required fields, documents, and structural constraints
 *   3.  File-upload format/size policies
 *   4.  Advancement eligibility checks (target-phase validation only)
 *   5.  Document-type management helpers
 *   6.  Admin bypass with optional validation & child cascade
 *   7.  Backward movement (admin-only with documented reason)
 *   8.  Document modification guards (phase-aware)
 */
class WorkflowService
{
    /* ================================================================
     |  1.  PHASE DEFINITIONS
     | ================================================================ */

    /** Ordered lifecycle phases (index = sort order) */
    const PHASES = ['draft', 'proposal', 'ongoing', 'completed'];

    const PHASE_LABELS = [
        'draft'     => 'Draft',
        'proposal'  => 'Proposal',
        'ongoing'   => 'Ongoing',
        'completed' => 'Completed',
    ];

    /** Per-phase Tailwind color tokens for views */
    const PHASE_COLORS = [
        'draft'     => ['bg' => 'bg-gray-100',   'text' => 'text-gray-700',   'border' => 'border-gray-300',  'ring' => 'ring-gray-300'],
        'proposal'  => ['bg' => 'bg-blue-50',    'text' => 'text-blue-700',   'border' => 'border-blue-300',  'ring' => 'ring-blue-300'],
        'ongoing'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',  'border' => 'border-amber-300', 'ring' => 'ring-amber-300'],
        'completed' => ['bg' => 'bg-green-50',   'text' => 'text-green-700',  'border' => 'border-green-300', 'ring' => 'ring-green-300'],
    ];

    /** SVG path data for phase icons */
    const PHASE_ICONS = [
        'draft'     => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'proposal'  => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'ongoing'   => 'M13 10V3L4 14h7v7l9-11h-7z',
        'completed' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    ];

    /* ================================================================
     |  2.  FILE UPLOAD POLICIES
     | ================================================================ */

    const ALLOWED_FORMATS = [
        'draft'     => ['pdf', 'doc', 'docx', 'rtf', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'pptx'],
        'proposal'  => ['pdf', 'doc', 'docx'],
        'ongoing'   => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
        'completed' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'mp4', 'pptx'],
    ];

    const MAX_FILE_SIZE = [          // in KB
        'draft'     => 20480,        // 20 MB
        'proposal'  => 10240,        // 10 MB
        'ongoing'   => 20480,        // 20 MB
        'completed' => 51200,        // 50 MB
    ];

    /* ================================================================
     |  3.  PROGRAM — REQUIRED FIELDS, DOCS & STRUCTURAL RULES
     | ================================================================ */

    const PROGRAM_REQUIRED_FIELDS = [
        'draft' => [
            'title'          => 'Program Title',
            'campus_id'      => 'Campus',
        ],
        'proposal' => [
            'title'             => 'Program Title',
            'proponent_name'    => 'Proponent Name',
            'campus_id'         => 'Campus',
            'rationale'         => 'Rationale',
            'general_objective' => 'General Objective',
        ],
        'ongoing' => [
            'target_start_date' => 'Target Start Date',
            'program_leader'    => 'Program Leader',
        ],
        'completed' => [
            'target_end_date' => 'Target End Date',
        ],
    ];

    const PROGRAM_REQUIRED_DOCS = [
        'draft'     => [],
        'proposal'  => ['Proposal Document'],
        'ongoing'   => [],
        'completed' => ['Terminal/Completion Report'],
    ];

    const PROGRAM_STRUCTURAL_RULES = [
        'draft'    => 'No structural constraints.',
        'proposal' => 'Program must have at least 1 project.',
        'ongoing'  => 'All projects must be at least "ongoing".',
        'completed'=> 'All projects must be "completed".',
    ];

    /* ================================================================
     |  4.  PROJECT — REQUIRED FIELDS, DOCS & STRUCTURAL RULES
     | ================================================================ */

    const PROJECT_REQUIRED_FIELDS = [
        'draft' => [
            'title'     => 'Project Title',
            'campus_id' => 'Campus',
        ],
        'proposal' => [
            'title'       => 'Project Title',
            'description' => 'Description',
            'campus_id'   => 'Campus',
        ],
        'ongoing' => [
            'target_start_date'   => 'Target Start Date',
            'persons_responsible' => 'Person(s) Responsible',
        ],
        'completed' => [
            'target_end_date' => 'Target End Date',
        ],
    ];

    const PROJECT_REQUIRED_DOCS = [
        'draft'     => [],
        'proposal'  => ['Project Proposal Document'],
        'ongoing'   => [],
        'completed' => ['Completion Report'],
    ];

    const PROJECT_STRUCTURAL_RULES = [
        'draft'    => 'No structural constraints.',
        'proposal' => 'Project must have at least 1 activity and at least 1 beneficiary.',
        'ongoing'  => 'All activities must be at least "ongoing".',
        'completed'=> 'All activities must be "completed".',
    ];

    /* ================================================================
     |  5.  ACTIVITY — REQUIRED FIELDS, DOCS & STRUCTURAL RULES
     | ================================================================ */

    const ACTIVITY_REQUIRED_FIELDS = [
        'draft' => [
            'title' => 'Activity Title',
        ],
        'proposal' => [
            'title'       => 'Activity Title',
            'description' => 'Description',
        ],
        'ongoing' => [
            'target_date'         => 'Target Date',
            'persons_responsible' => 'Person(s) Responsible',
        ],
        'completed' => [
            'completion_date' => 'Completion Date',
        ],
    ];

    const ACTIVITY_REQUIRED_DOCS = [
        'draft'     => [],
        'proposal'  => [],
        'ongoing'   => [],
        'completed' => [],
    ];

    const ACTIVITY_STRUCTURAL_RULES = [
        'draft'    => 'Activity must belong to a project.',
        'proposal' => 'Activity must belong to a project.',
        'ongoing'  => 'Activity must belong to a project.',
        'completed'=> 'Activity must belong to a project.',
    ];

    /* ================================================================
     |  6.  DOCUMENT TYPE HELPERS
     | ================================================================ */

    const SUGGESTED_DOC_LABELS = [
        'draft'     => ['Draft Document', 'Supporting Document', 'Reference Material', 'Other'],
        'proposal'  => ['Proposal Document', 'Cover Letter', 'Proponent Profile / Bio', 'MOA / MOU', 'Endorsement Letter', 'Budget Breakdown', 'Work Plan / Timeline', 'Data Set', 'Other'],
        'ongoing'   => ['Monitoring Report', 'Progress Report', 'Attendance Sheet', 'Photo Documentation', 'Financial Report', 'Other'],
        'completed' => ['Terminal/Completion Report', 'Evaluation Report', 'Certificate', 'Photo Documentation', 'Financial Liquidation', 'Post-Activity Report', 'Other'],
    ];

    /* ================================================================
     |  7.  PHASE NAVIGATION HELPERS
     | ================================================================ */

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

    public static function isPhaseAtLeast(string $current, string $minimum): bool
    {
        return self::getPhaseIndex($current) >= self::getPhaseIndex($minimum);
    }

    /* ================================================================
     |  8.  ENTITY TYPE DETECTION
     | ================================================================ */

    public static function resolveEntityType($model): string
    {
        return match (true) {
            $model instanceof ExtensionProgram  => 'program',
            $model instanceof ExtensionProject  => 'project',
            $model instanceof ExtensionActivity => 'activity',
            default => throw new \InvalidArgumentException('Unknown entity type: ' . get_class($model)),
        };
    }

    /* ================================================================
     |  9.  REQUIREMENTS STATUS  (target-phase completeness)
     | ================================================================ */

    /**
     * Evaluate whether the model has fulfilled a given phase's
     * required fields and documents.
     *
     * Document matching uses BOTH label and document_type display
     * name, so renaming a label does not silently break validation.
     */
    public static function getRequirementsStatus($model, ?string $targetPhase = null): array
    {
        $type  = self::resolveEntityType($model);
        $phase = $targetPhase ?? $model->status;

        $requiredFields = match ($type) {
            'program'  => self::PROGRAM_REQUIRED_FIELDS[$phase]  ?? [],
            'project'  => self::PROJECT_REQUIRED_FIELDS[$phase]  ?? [],
            'activity' => self::ACTIVITY_REQUIRED_FIELDS[$phase] ?? [],
        };

        $requiredDocs = match ($type) {
            'program'  => self::PROGRAM_REQUIRED_DOCS[$phase]  ?? [],
            'project'  => self::PROJECT_REQUIRED_DOCS[$phase]  ?? [],
            'activity' => self::ACTIVITY_REQUIRED_DOCS[$phase] ?? [],
        };

        // --- Check fields ---
        $fieldStatus = [];
        foreach ($requiredFields as $field => $label) {
            $fieldStatus[$field] = [
                'label' => $label,
                'met'   => ! empty($model->$field),
            ];
        }

        // --- Check documents (match by label OR document_type display name) ---
        $uploadedDocs = $model->statusDocuments()
            ->where('phase', $phase)
            ->get();

        $docStatus = [];
        foreach ($requiredDocs as $docLabel) {
            $matchByLabel = $uploadedDocs->where('label', $docLabel)->isNotEmpty();
            $matchByType  = $uploadedDocs->filter(function ($doc) use ($docLabel) {
                $typeName = \App\Models\StatusDocument::DOCUMENT_TYPES[$doc->document_type] ?? null;
                return $typeName === $docLabel;
            })->isNotEmpty();

            $docStatus[$docLabel] = [
                'label' => $docLabel,
                'met'   => $matchByLabel || $matchByType,
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

    /* ================================================================
     |  10.  FULL ADVANCEMENT CHECK  (target-phase only + structure)
     | ================================================================ */

    /**
     * Complete pre-flight check before a model can move to its next phase.
     *
     * Validates only the TARGET phase requirements (the phase being entered).
     * Structural rules are checked against the TARGET phase.
     * Proposal → Ongoing requires admin role.
     */
    public static function canAdvance($model, ?string $actorRole = null): array
    {
        $type     = self::resolveEntityType($model);
        $phase    = $model->status;
        $nextPhase = self::getNextPhase($phase);
        $errors   = [];
        $warnings = [];

        if (! $nextPhase) {
            $errors[] = 'Already at the final phase (Completed).';
            return [
                'can_advance'  => false,
                'errors'       => $errors,
                'warnings'     => $warnings,
                'next_phase'   => null,
                'requirements' => [
                    'fields' => [], 'documents' => [],
                    'all_fields_met' => true, 'all_docs_met' => true, 'can_advance' => true,
                ],
            ];
        }

        // --- Pre-check: forward-only, single-step ---
        $statusCheck = self::validateStatusChange($model, $nextPhase);
        if (! $statusCheck['valid']) {
            return [
                'can_advance'  => false,
                'errors'       => $statusCheck['errors'],
                'warnings'     => [],
                'next_phase'   => $nextPhase,
                'requirements' => [],
            ];
        }

        // --- Target-phase requirements (must be met before entering) ---
        $targetReq = self::getRequirementsStatus($model, $nextPhase);
        if (! $targetReq['all_fields_met']) {
            foreach (collect($targetReq['fields'])->filter(fn ($f) => ! $f['met']) as $f) {
                $errors[] = "Missing required field for {$nextPhase} phase: {$f['label']}";
            }
        }
        if (! $targetReq['all_docs_met']) {
            foreach (collect($targetReq['documents'])->filter(fn ($d) => ! $d['met']) as $d) {
                $errors[] = "Missing required document for {$nextPhase} phase: {$d['label']}";
            }
        }

        // --- Structural integrity for the TARGET phase ---
        $errors = array_merge($errors, self::checkStructuralRules($model, $type, $nextPhase));

        // --- Role gate: proposal→ongoing requires admin approval ---
        if ($phase === 'proposal' && $nextPhase === 'ongoing') {
            if ($actorRole && $actorRole !== 'admin') {
                $errors[] = 'Advancing from Proposal to Ongoing requires administrator approval.';
            }
        }

        // --- Warnings (non-blocking) ---
        $warnings = array_merge($warnings, self::checkWarnings($model, $type, $phase));

        return [
            'can_advance'  => empty($errors),
            'errors'       => $errors,
            'warnings'     => $warnings,
            'next_phase'   => $nextPhase,
            'requirements' => $targetReq,
        ];
    }

    /* ================================================================
     |  11.  STRUCTURAL INTEGRITY RULES
     | ================================================================ */

    /**
     * Enforce hierarchy rules that prevent logical inconsistencies.
     * Rules are checked against the TARGET phase (the phase being entered).
     */
    private static function checkStructuralRules($model, string $type, string $targetPhase): array
    {
        $errors = [];

        // ── PROGRAM rules ──
        if ($type === 'program') {
            if (in_array($targetPhase, ['proposal', 'ongoing', 'completed'])) {
                $projectCount = $model->projects()->count();
                if ($projectCount === 0) {
                    $errors[] = 'Program must have at least 1 project before advancing.';
                }

                $memberCount = $model->members()->count();
                if ($memberCount === 0) {
                    $errors[] = 'Program must have at least 1 participant or member before advancing.';
                }
            }

            if ($targetPhase === 'ongoing') {
                $notReady = $model->projects()
                    ->whereNotIn('status', ['ongoing', 'completed'])
                    ->count();
                if ($notReady > 0) {
                    $errors[] = "All projects must be at least 'Ongoing' ({$notReady} not yet ready).";
                }
            }

            if ($targetPhase === 'completed') {
                $incomplete = $model->projects()->where('status', '!=', 'completed')->count();
                if ($incomplete > 0) {
                    $errors[] = "All projects must be completed first ({$incomplete} still incomplete).";
                }
            }
        }

        // ── PROJECT rules ──
        if ($type === 'project') {
            if (in_array($targetPhase, ['proposal', 'ongoing', 'completed'])) {
                $activityCount    = $model->activities()->count();
                $beneficiaryCount = $model->beneficiaries()->count();

                if ($activityCount === 0) {
                    $errors[] = 'Project must have at least 1 activity before advancing.';
                }
                if ($beneficiaryCount === 0) {
                    $errors[] = 'Project must have at least 1 beneficiary before advancing.';
                }
            }

            if ($targetPhase === 'ongoing') {
                $notReady = $model->activities()
                    ->whereNotIn('status', ['ongoing', 'completed'])
                    ->count();
                if ($notReady > 0) {
                    $errors[] = "All activities must be at least 'Ongoing' ({$notReady} not yet ready).";
                }
            }

            if ($targetPhase === 'completed') {
                $incomplete = $model->activities()->where('status', '!=', 'completed')->count();
                if ($incomplete > 0) {
                    $errors[] = "All activities must be completed first ({$incomplete} still incomplete).";
                }
            }
        }

        // ── ACTIVITY rules ──
        if ($type === 'activity') {
            if (! $model->extension_project_id) {
                $errors[] = 'Activity must belong to a project.';
            }
        }

        return $errors;
    }

    /* ================================================================
     |  12.  NON-BLOCKING WARNINGS
     | ================================================================ */

    private static function checkWarnings($model, string $type, string $phase): array
    {
        $warnings = [];

        if (in_array($phase, ['proposal', 'ongoing'])) {
            $dateField = match ($type) {
                'activity' => 'target_date',
                default    => 'target_end_date',
            };

            if ($model->$dateField && $model->$dateField->isPast()) {
                $warnings[] = 'This ' . $type . ' is past its target date.';
            }
        }

        if ($type === 'program' && $phase === 'proposal') {
            $totalBeneficiaries = 0;
            foreach ($model->projects as $project) {
                $totalBeneficiaries += $project->beneficiaries()->count();
            }
            if ($totalBeneficiaries === 0) {
                $warnings[] = 'None of the program\'s projects have beneficiaries listed yet.';
            }
        }

        if ($type === 'project' && $phase === 'proposal') {
            if ($model->budgetItems()->count() === 0) {
                $warnings[] = 'No budget items have been added to this project.';
            }
        }

        if ($type === 'program' && $phase === 'proposal') {
            if ($model->members()->count() === 0) {
                $warnings[] = 'No program members have been assigned.';
            }
        }

        return $warnings;
    }

    /* ================================================================
     |  13.  STATUS SUMMARY
     | ================================================================ */

    /**
     * Build a complete status summary for dashboard/reporting.
     */
    public static function getStatusSummary($model): array
    {
        $phase = $model->status;
        $check = self::canAdvance($model);

        return [
            'phase'        => $phase,
            'label'        => self::PHASE_LABELS[$phase] ?? $phase,
            'requirements' => $check['requirements'],
            'errors'       => $check['errors'],
            'warnings'     => $check['warnings'] ?? [],
            'can_advance'  => $check['can_advance'],
            'next_phase'   => $check['next_phase'],
        ];
    }

    /* ================================================================
     |  14.  ADMIN BYPASS VALIDATION
     | ================================================================ */

    /**
     * Validate an admin bypass operation (forward or backward).
     * Returns non-blocking warnings for any skipped requirements.
     */
    public static function validateBypass($model, string $targetPhase): array
    {
        $errors   = [];
        $warnings = [];
        $type     = self::resolveEntityType($model);
        $current  = $model->status;

        if (! in_array($targetPhase, self::PHASES)) {
            $errors[] = "Invalid target phase: {$targetPhase}.";
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings, 'is_backward' => false];
        }

        if ($targetPhase === $current) {
            $errors[] = 'Target phase is the same as current phase.';
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings, 'is_backward' => false];
        }

        $currentIdx = self::getPhaseIndex($current);
        $targetIdx  = self::getPhaseIndex($targetPhase);
        $isBackward = $targetIdx < $currentIdx;

        // Forward bypass: warn about skipped requirements (non-blocking)
        if (! $isBackward) {
            for ($i = $currentIdx + 1; $i <= $targetIdx; $i++) {
                $intermediatePhase = self::PHASES[$i];
                $req = self::getRequirementsStatus($model, $intermediatePhase);

                if (! $req['all_fields_met']) {
                    foreach (collect($req['fields'])->filter(fn ($f) => ! $f['met']) as $f) {
                        $warnings[] = "Skipping requirement for {$intermediatePhase} phase: {$f['label']} (not filled)";
                    }
                }
                if (! $req['all_docs_met']) {
                    foreach (collect($req['documents'])->filter(fn ($d) => ! $d['met']) as $d) {
                        $warnings[] = "Skipping requirement for {$intermediatePhase} phase: {$d['label']} (not uploaded)";
                    }
                }

                $structErrors = self::checkStructuralRules($model, $type, $intermediatePhase);
                foreach ($structErrors as $err) {
                    $warnings[] = "Structural rule bypassed for {$intermediatePhase}: {$err}";
                }
            }
        }

        // Backward bypass: warn about potential inconsistencies
        if ($isBackward) {
            $warnings[] = "Moving backward from {$current} to {$targetPhase}. This record will need to re-advance through skipped phases.";

            if ($type === 'program') {
                $higherChildren = $model->projects()
                    ->whereIn('status', array_slice(self::PHASES, $targetIdx + 1))
                    ->count();
                if ($higherChildren > 0) {
                    $warnings[] = "{$higherChildren} child project(s) are at a higher status than the target phase.";
                }
            }
            if ($type === 'project') {
                $higherChildren = $model->activities()
                    ->whereIn('status', array_slice(self::PHASES, $targetIdx + 1))
                    ->count();
                if ($higherChildren > 0) {
                    $warnings[] = "{$higherChildren} child activity/ies are at a higher status than the target phase.";
                }
            }
        }

        return [
            'valid'       => true,
            'errors'      => $errors,
            'warnings'    => $warnings,
            'is_backward' => $isBackward,
        ];
    }

    /* ================================================================
     |  15.  TOP-DOWN CASCADE  (after bypass)
     | ================================================================ */

    /**
     * Cascade a status change to child entities when a parent is bypassed.
     */
    public static function cascadeStatusToChildren($model, string $targetPhase, int $actorId): array
    {
        $type     = self::resolveEntityType($model);
        $cascaded = [];

        if ($type === 'program') {
            foreach ($model->projects as $project) {
                $projectIdx = self::getPhaseIndex($project->status);
                $targetIdx  = self::getPhaseIndex($targetPhase);

                if ($projectIdx !== $targetIdx) {
                    $oldStatus = $project->status;
                    $project->update(['status' => $targetPhase]);

                    \App\Models\StatusTransitionLog::create([
                        'transitionable_type' => get_class($project),
                        'transitionable_id'   => $project->id,
                        'from_status'         => $oldStatus,
                        'to_status'           => $targetPhase,
                        'transitioned_by'     => $actorId,
                        'is_bypass'           => true,
                        'bypass_reason'       => "Cascaded from parent program #{$model->id} bypass.",
                        'notes'               => "Auto-cascaded: parent program moved to {$targetPhase}.",
                    ]);
                    $cascaded[] = "Project #{$project->id} ({$project->title}): {$oldStatus} → {$targetPhase}";

                    $activityCascades = self::cascadeStatusToChildren($project, $targetPhase, $actorId);
                    $cascaded = array_merge($cascaded, $activityCascades);
                }
            }
        }

        if ($type === 'project') {
            foreach ($model->activities as $activity) {
                $activityIdx = self::getPhaseIndex($activity->status);
                $targetIdx   = self::getPhaseIndex($targetPhase);

                if ($activityIdx !== $targetIdx) {
                    $oldStatus = $activity->status;
                    $activity->update(['status' => $targetPhase]);

                    \App\Models\StatusTransitionLog::create([
                        'transitionable_type' => get_class($activity),
                        'transitionable_id'   => $activity->id,
                        'from_status'         => $oldStatus,
                        'to_status'           => $targetPhase,
                        'transitioned_by'     => $actorId,
                        'is_bypass'           => true,
                        'bypass_reason'       => "Cascaded from parent project #{$model->id} bypass.",
                        'notes'               => "Auto-cascaded: parent project moved to {$targetPhase}.",
                    ]);
                    $cascaded[] = "Activity #{$activity->id} ({$activity->title}): {$oldStatus} → {$targetPhase}";
                }
            }
        }

        return $cascaded;
    }

    /* ================================================================
     |  16.  HIERARCHY INTEGRITY VALIDATORS
     | ================================================================ */

    /**
     * Check whether a record can be deleted.
     */
    public static function canDelete($model): array
    {
        $type = self::resolveEntityType($model);
        $errors = [];

        if (in_array($model->status, ['ongoing', 'completed'])) {
            $errors[] = ucfirst($type) . ' cannot be deleted once it is ' . $model->status . '.';
        }

        if ($type === 'project' && $model->extension_program_id) {
            $siblingCount = ExtensionProject::where('extension_program_id', $model->extension_program_id)
                ->where('id', '!=', $model->id)
                ->count();
            $parentStatus = $model->program?->status;

            if ($siblingCount === 0 && in_array($parentStatus, ['proposal', 'ongoing', 'completed'])) {
                $errors[] = 'Cannot delete the only project of a submitted program.';
            }
        }

        if ($type === 'activity' && $model->extension_project_id) {
            $siblingCount = ExtensionActivity::where('extension_project_id', $model->extension_project_id)
                ->where('id', '!=', $model->id)
                ->count();
            $parentStatus = $model->project?->status;

            if ($siblingCount === 0 && in_array($parentStatus, ['proposal', 'ongoing', 'completed'])) {
                $errors[] = 'Cannot delete the only activity of a submitted project.';
            }
        }

        return [
            'can_delete' => empty($errors),
            'errors'     => $errors,
        ];
    }

    /**
     * Validate that a new status assignment doesn't violate hierarchy.
     * Ensures only forward, single-step transitions without admin bypass.
     */
    public static function validateStatusChange($model, string $newStatus): array
    {
        $errors  = [];
        $current = $model->status;

        if (! in_array($newStatus, self::PHASES)) {
            $errors[] = "Invalid status: {$newStatus}.";
            return ['valid' => false, 'errors' => $errors];
        }

        if ($newStatus === $current) {
            return ['valid' => true, 'errors' => []];
        }

        $currentIdx = self::getPhaseIndex($current);
        $newIdx     = self::getPhaseIndex($newStatus);

        if ($newIdx < $currentIdx) {
            $errors[] = 'Cannot move backward from ' . self::PHASE_LABELS[$current] . ' to ' . self::PHASE_LABELS[$newStatus] . ' without admin bypass.';
        }

        if ($newIdx > $currentIdx + 1) {
            $errors[] = 'Cannot skip phases. Use admin bypass to jump multiple phases.';
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /* ================================================================
     |  17.  DOCUMENT MODIFICATION GUARDS
     | ================================================================ */

    /**
     * Check if document modifications (edit/delete) are allowed.
     *
     * Rules:
     * - Documents on completed entities can only be deleted by admin
     * - Required documents cannot be deleted if they are the sole satisfier
     */
    public static function canModifyDocument($document, string $action, string $userRole): array
    {
        $errors = [];
        $model  = $document->documentable;

        if (! $model) {
            $errors[] = 'Parent entity not found.';
            return ['allowed' => false, 'errors' => $errors];
        }

        $entityPhase = $model->status;

        if ($action === 'delete' && $entityPhase === 'completed' && $userRole !== 'admin') {
            $errors[] = 'Documents on completed records can only be deleted by administrators.';
        }

        // Check if this is the sole satisfier of a required-doc constraint
        if ($action === 'delete') {
            $type  = self::resolveEntityType($model);
            $phase = $document->phase;

            $requiredDocs = match ($type) {
                'program'  => self::PROGRAM_REQUIRED_DOCS[$phase]  ?? [],
                'project'  => self::PROJECT_REQUIRED_DOCS[$phase]  ?? [],
                'activity' => self::ACTIVITY_REQUIRED_DOCS[$phase] ?? [],
            };

            if (in_array($document->label, $requiredDocs)) {
                $otherMatchCount = $model->statusDocuments()
                    ->where('phase', $phase)
                    ->where('label', $document->label)
                    ->where('id', '!=', $document->id)
                    ->count();

                if ($otherMatchCount === 0) {
                    $errors[] = "Cannot delete this document — it is the only one satisfying the required '{$document->label}' requirement for the {$phase} phase.";
                }
            }
        }

        return [
            'allowed' => empty($errors),
            'errors'  => $errors,
        ];
    }
}
