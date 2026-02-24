<?php

namespace App\Http\Controllers;

use App\Models\ExtensionProgram;
use App\Models\ExtensionProject;
use App\Models\StatusDocument;
use App\Models\StatusTransitionLog;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkflowController extends Controller
{
    /* ----------------------------------------------------------------
     *  ADVANCE — sequential (next phase)
     * ---------------------------------------------------------------- */

    public function advance(Request $request, string $type, int $id)
    {
        $model = $this->resolveModel($type, $id);
        $check = WorkflowService::canAdvance($model);

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

    /* ----------------------------------------------------------------
     *  BYPASS — admin skip to a chosen future phase
     * ---------------------------------------------------------------- */

    public function bypass(Request $request, string $type, int $id)
    {
        if (! auth()->user()->isAdmin()) {
            return back()->with('error', 'Only administrators can bypass workflow phases.');
        }

        $request->validate([
            'target_phase'  => ['required', 'in:' . implode(',', WorkflowService::PHASES)],
            'bypass_reason' => ['required', 'string', 'min:10'],
        ]);

        $model       = $this->resolveModel($type, $id);
        $targetPhase = $request->input('target_phase');
        $currentIdx  = WorkflowService::getPhaseIndex($model->status);
        $targetIdx   = WorkflowService::getPhaseIndex($targetPhase);

        if ($targetIdx <= $currentIdx) {
            return back()->with('error', 'Can only bypass forward to a later phase.');
        }

        StatusTransitionLog::create([
            'transitionable_type' => get_class($model),
            'transitionable_id'  => $model->id,
            'from_status'        => $model->status,
            'to_status'          => $targetPhase,
            'transitioned_by'    => auth()->id(),
            'is_bypass'          => true,
            'bypass_reason'      => $request->input('bypass_reason'),
            'notes'              => $request->input('notes'),
        ]);

        $model->update(['status' => $targetPhase]);

        return back()->with('success', 'Status bypassed to ' . WorkflowService::PHASE_LABELS[$targetPhase] . ' (Admin Override).');
    }

    /* ----------------------------------------------------------------
     *  UPLOAD DOCUMENT
     * ---------------------------------------------------------------- */

    public function uploadDocument(Request $request, string $type, int $id)
    {
        $model = $this->resolveModel($type, $id);
        $phase = $model->status;

        if (! WorkflowService::canUserUpload($phase, auth()->user()->role)) {
            return back()->with('error', 'You do not have permission to upload documents in this phase.');
        }

        $formats = WorkflowService::ALLOWED_FORMATS[$phase] ?? [];
        $maxSize = WorkflowService::MAX_FILE_SIZE[$phase] ?? 10240;

        $request->validate([
            'document' => ['required', 'file', "max:{$maxSize}", 'mimes:' . implode(',', $formats)],
            'label'    => ['required', 'string', 'max:255'],
        ]);

        $file   = $request->file('document');
        $folder = "workflow-documents/{$type}/{$id}/{$phase}";
        $path   = $file->store($folder, 'public');

        StatusDocument::create([
            'documentable_type' => get_class($model),
            'documentable_id'  => $model->id,
            'phase'            => $phase,
            'label'            => $request->input('label'),
            'file_name'        => $file->hashName(),
            'file_path'        => $path,
            'original_name'    => $file->getClientOriginalName(),
            'mime_type'        => $file->getMimeType(),
            'file_size'        => $file->getSize(),
            'uploaded_by'      => auth()->id(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /* ----------------------------------------------------------------
     *  DELETE DOCUMENT
     * ---------------------------------------------------------------- */

    public function deleteDocument(StatusDocument $document)
    {
        if (! auth()->user()->isAdmin() && $document->uploaded_by !== auth()->id()) {
            return back()->with('error', 'You do not have permission to delete this document.');
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }

    /* ---------------------------------------------------------------- */

    private function resolveModel(string $type, int $id)
    {
        return match ($type) {
            'program' => ExtensionProgram::findOrFail($id),
            'project' => ExtensionProject::findOrFail($id),
            default   => abort(404),
        };
    }
}
