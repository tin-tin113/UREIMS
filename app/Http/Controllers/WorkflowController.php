<?php

namespace App\Http\Controllers;

use App\Models\ExtensionActivity;
use App\Models\ExtensionProgram;
use App\Models\ExtensionProject;
use App\Models\StatusDocument;
use App\Models\StatusTransitionLog;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkflowController extends Controller
{
    /* ================================================================
     |  ADVANCE — move to the next phase (with full validation)
     | ================================================================ */

    public function advance(Request $request, string $type, int $id)
    {
        $model = $this->resolveModel($type, $id);

        if (! auth()->user()->isAdmin() && $model->created_by !== auth()->id()) {
            return back()->with('error', 'You do not have permission to advance this record.');
        }

        $check = WorkflowService::canAdvance($model, auth()->user()->role);

        if (! $check['can_advance']) {
            return back()->with('error', 'Cannot advance: ' . implode('. ', $check['errors']));
        }

        $toStatus = $check['next_phase'];
        if (! $toStatus) {
            return back()->with('error', 'Already at the final phase.');
        }

        StatusTransitionLog::create([
            'transitionable_type' => get_class($model),
            'transitionable_id'  => $model->id,
            'from_status'        => $model->status,
            'to_status'          => $toStatus,
            'transitioned_by'    => auth()->id(),
            'is_bypass'          => false,
            'notes'              => $request->input('notes'),
        ]);

        $model->update(['status' => $toStatus]);

        return back()->with('success', 'Status advanced to ' . WorkflowService::PHASE_LABELS[$toStatus] . '.');
    }

    /* ================================================================
     |  BYPASS — admin override to jump phases (forward OR backward)
     | ================================================================ */

    public function bypass(Request $request, string $type, int $id)
    {
        if (! auth()->user()->isAdmin()) {
            return back()->with('error', 'Only administrators can bypass workflow phases.');
        }

        $request->validate([
            'target_phase'   => ['required', 'in:' . implode(',', WorkflowService::PHASES)],
            'bypass_reason'  => ['required', 'string', 'min:10'],
            'cascade_children' => ['nullable', 'boolean'],
        ]);

        $model       = $this->resolveModel($type, $id);
        $targetPhase = $request->input('target_phase');

        // Validate the bypass operation
        $validation = WorkflowService::validateBypass($model, $targetPhase);

        if (! $validation['valid']) {
            return back()->with('error', 'Cannot bypass: ' . implode('. ', $validation['errors']));
        }

        // Log the transition (including backward moves)
        StatusTransitionLog::create([
            'transitionable_type' => get_class($model),
            'transitionable_id'  => $model->id,
            'from_status'        => $model->status,
            'to_status'          => $targetPhase,
            'transitioned_by'    => auth()->id(),
            'is_bypass'          => true,
            'bypass_reason'      => $request->input('bypass_reason'),
            'notes'              => $request->input('notes')
                . ($validation['warnings'] ? "\n[Warnings: " . implode('; ', $validation['warnings']) . ']' : ''),
        ]);

        $model->update(['status' => $targetPhase]);

        // Optional top-down cascade to children
        $cascaded = [];
        if ($request->boolean('cascade_children', false)) {
            $cascaded = WorkflowService::cascadeStatusToChildren($model, $targetPhase, auth()->id());
        }

        $direction = $validation['is_backward'] ? 'reverted' : 'bypassed';
        $message   = "Status {$direction} to " . WorkflowService::PHASE_LABELS[$targetPhase] . ' (Admin Override).';
        if (! empty($cascaded)) {
            $message .= ' ' . count($cascaded) . ' child record(s) were also updated.';
        }

        return back()->with('success', $message);
    }

    /* ================================================================
     |  UPLOAD DOCUMENT — with format/size validation per phase
     | ================================================================ */

    public function uploadDocument(Request $request, string $type, int $id)
    {
        $model = $this->resolveModel($type, $id);
        $phase = $model->status;

        // Permission: owner or admin
        if (! auth()->user()->isAdmin() && $model->created_by !== auth()->id()) {
            return back()->with('error', 'You do not have permission to upload documents.');
        }

        $formats = WorkflowService::ALLOWED_FORMATS[$phase] ?? [];
        $maxSize = WorkflowService::MAX_FILE_SIZE[$phase] ?? 10240;

        $request->validate([
            'document'      => ['required', 'file', "max:{$maxSize}", 'mimes:' . implode(',', $formats)],
            'label'         => ['required', 'string', 'max:255'],
            'document_type' => ['nullable', 'string', 'max:100'],
        ]);

        $file   = $request->file('document');
        $folder = "workflow-documents/{$type}/{$id}/{$phase}";
        $path   = $file->store($folder, 'public');

        StatusDocument::create([
            'documentable_type' => get_class($model),
            'documentable_id'   => $model->id,
            'phase'             => $phase,
            'label'             => $request->input('label'),
            'document_type'     => $request->input('document_type'),
            'file_name'         => $file->hashName(),
            'file_path'         => $path,
            'original_name'     => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'uploaded_by'       => auth()->id(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /* ================================================================
     |  UPDATE DOCUMENT TYPE — edit the category/type after upload
     | ================================================================ */

    public function updateDocumentType(Request $request, StatusDocument $document)
    {
        if (! auth()->user()->isAdmin() && $document->uploaded_by !== auth()->id()) {
            return back()->with('error', 'You do not have permission to edit this document.');
        }

        $request->validate([
            'label'         => ['nullable', 'string', 'max:255'],
            'document_type' => ['nullable', 'string', 'max:100'],
        ]);

        $updates = [];
        if ($request->has('label')) {
            $updates['label'] = $request->input('label');
        }
        if ($request->has('document_type')) {
            $updates['document_type'] = $request->input('document_type');
        }

        if (! empty($updates)) {
            $document->update($updates);
        }

        return back()->with('success', 'Document details updated.');
    }

    /* ================================================================
     |  DELETE DOCUMENT — soft-delete with history preservation
     | ================================================================ */

    public function deleteDocument(StatusDocument $document)
    {
        if (! auth()->user()->isAdmin() && $document->uploaded_by !== auth()->id()) {
            return back()->with('error', 'You do not have permission to delete this document.');
        }

        // Phase-aware deletion guard
        $modCheck = WorkflowService::canModifyDocument($document, 'delete', auth()->user()->role);
        if (! $modCheck['allowed']) {
            return back()->with('error', implode(' ', $modCheck['errors']));
        }

        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    /* ================================================================
     |  PRIVATE — resolve model from type + id
     | ================================================================ */

    private function resolveModel(string $type, int $id)
    {
        return match ($type) {
            'program'  => ExtensionProgram::findOrFail($id),
            'project'  => ExtensionProject::findOrFail($id),
            'activity' => ExtensionActivity::findOrFail($id),
            default    => abort(404),
        };
    }
}
