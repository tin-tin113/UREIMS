<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\ExtensionProgram;
use App\Models\ExtensionProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProposalWizardController extends Controller
{
    /* ================================================================
     *  CONFIGURATION
     * ================================================================ */

    const REQUIREMENTS = [
        'program' => [
            'The proposal has not been previously submitted, nor is it under review by another office or department.',
            'The proposal must include a complete Program Proposal Document in PDF, Microsoft Word, or RTF format.',
            'Use the institution-prescribed Proposal Template, which serves as the main reference for formatting all content, tables, figures, and annexes.',
            'Where available, include supporting documents such as MOAs, endorsement letters, or budget breakdowns.',
            'Include a cover letter in your submission highlighting the key aspects and expected impact of the extension program.',
            'Include the proponent(s) profile using the downloadable template. Ensure each proponent is represented with a brief biography.',
        ],
        'project' => [
            'The proposal has not been previously submitted, nor is it under review by another office or department.',
            'The proposal must include a complete Project Proposal Document in PDF, Microsoft Word, or RTF format.',
            'Use the institution-prescribed Project Proposal Template for formatting all content.',
            'Where available, include supporting documents such as MOAs, endorsement letters, or budget breakdowns.',
            'Include a cover letter highlighting the key aspects and expected outcomes of the project.',
            'Include the proponent(s) profile using the downloadable template.',
        ],
    ];

    const FILE_LABELS = [
        'Proposal Document',
        'Cover Letter',
        'Proponent Profile / Bio',
        'Supporting Document',
        'MOA / MOU',
        'Endorsement Letter',
        'Budget Breakdown',
        'Work Plan / Timeline',
        'Data Set',
        'Other',
    ];

    const DATA_PRIVACY_NOTICE = 'By submitting this proposal, you consent to the collection, processing, and storage of the personal information provided herein in accordance with the Data Privacy Act of 2012 (Republic Act No. 10173). The information collected will be used solely for the purpose of evaluating extension program/project proposals and will be handled with strict confidentiality. You may exercise your rights as a data subject, including the right to access, correct, and request deletion of your personal data, by contacting the University Data Protection Officer.';

    /* ================================================================
     *  HELPERS
     * ================================================================ */

    private function validateType(string $type): void
    {
        if (!in_array($type, ['program', 'project'])) {
            abort(404);
        }
    }

    /**
     * Load draft from session. Does NOT auto-load from DB —
     * DB loading is handled by continueDraft().
     */
    private function loadDraft(string $type): array
    {
        return session("proposal_wizard.{$type}", []);
    }

    /**
     * Persist the current session draft to the database.
     */
    private function persistDraft(string $type, array $draft, int $currentStep): mixed
    {
        $draft['draft_step'] = $currentStep;
        $details = $draft['details'] ?? [];

        $modelClass = $type === 'program' ? ExtensionProgram::class : ExtensionProject::class;

        $dbId = $draft['_db_id'] ?? null;
        $model = $dbId ? $modelClass::find($dbId) : null;

        // Verify ownership when updating an existing draft
        if ($model && $model->created_by !== auth()->id()) {
            abort(403, 'You do not own this draft.');
        }

        $commonData = [
            'title'      => $details['title'] ?? 'Untitled ' . ucfirst($type),
            'status'     => 'draft',
            'draft_data' => $draft,
            'campus_id'  => $details['campus_id'] ?? null,
        ];

        if ($type === 'program') {
            $commonData['proponent_name'] = $details['proponent_name'] ?? null;
        }

        if ($model && $model->status === 'draft') {
            $model->update($commonData);
        } else {
            $commonData['created_by'] = auth()->id();
            $model = $modelClass::create($commonData);
            $draft['_db_id'] = $model->id;
        }

        session(["proposal_wizard.{$type}" => $draft]);

        return $model;
    }

    /* ================================================================
     *  STEP 1 — START
     * ================================================================ */

    public function start(string $type)
    {
        $this->validateType($type);

        $draft = $this->loadDraft($type);
        $requirements = self::REQUIREMENTS[$type];
        $checkedRequirements = $draft['checked_requirements'] ?? [];
        $privacyAgreed = $draft['privacy_agreed'] ?? false;
        $comments = $draft['comments'] ?? '';
        $dataPrivacyNotice = self::DATA_PRIVACY_NOTICE;

        return view('extension.wizard.start', compact(
            'type', 'requirements', 'checkedRequirements', 'privacyAgreed', 'comments', 'dataPrivacyNotice'
        ));
    }

    public function saveStart(Request $request, string $type)
    {
        $this->validateType($type);

        $draft = session("proposal_wizard.{$type}", []);
        $draft['checked_requirements'] = $request->input('requirements', []);
        $draft['privacy_agreed'] = $request->boolean('privacy_agreed');
        $draft['comments'] = $request->input('comments', '');
        $draft['step1_completed'] = true;

        session(["proposal_wizard.{$type}" => $draft]);

        return redirect()->route('proposal.wizard.upload', $type);
    }

    /* ================================================================
     *  STEP 2 — UPLOAD DOCUMENTS
     * ================================================================ */

    public function upload(string $type)
    {
        $this->validateType($type);

        $draft = session("proposal_wizard.{$type}", []);
        $uploadedFiles = $draft['uploaded_files'] ?? [];
        $fileLabels = self::FILE_LABELS;

        return view('extension.wizard.upload', compact('type', 'uploadedFiles', 'fileLabels'));
    }

    public function saveUpload(Request $request, string $type)
    {
        $this->validateType($type);

        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store("proposal-drafts/{$type}", 'public');

        $draft = session("proposal_wizard.{$type}", []);
        $draft['uploaded_files'] = $draft['uploaded_files'] ?? [];
        $draft['uploaded_files'][] = [
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'label' => $request->input('file_label', 'Uncategorized'),
        ];

        session(["proposal_wizard.{$type}" => $draft]);

        return redirect()->route('proposal.wizard.upload', $type)->with('success', 'File uploaded successfully.');
    }

    public function removeFile(Request $request, string $type)
    {
        $this->validateType($type);

        $index = $request->input('file_index');
        $draft = session("proposal_wizard.{$type}", []);

        if (isset($draft['uploaded_files'][$index])) {
            Storage::disk('public')->delete($draft['uploaded_files'][$index]['path']);
            array_splice($draft['uploaded_files'], $index, 1);
            session(["proposal_wizard.{$type}" => $draft]);
        }

        return redirect()->route('proposal.wizard.upload', $type)->with('success', 'File removed.');
    }

    public function updateFileLabel(Request $request, string $type)
    {
        $this->validateType($type);

        $request->validate([
            'file_index' => 'required|integer|min:0',
            'new_label' => 'required|string|max:100',
        ]);

        $index = $request->input('file_index');
        $draft = session("proposal_wizard.{$type}", []);

        if (isset($draft['uploaded_files'][$index])) {
            $draft['uploaded_files'][$index]['label'] = $request->input('new_label');
            session(["proposal_wizard.{$type}" => $draft]);
        }

        return redirect()->route('proposal.wizard.upload', $type)->with('success', 'File label updated.');
    }

    public function saveUploadContinue(Request $request, string $type)
    {
        $this->validateType($type);

        $draft = session("proposal_wizard.{$type}", []);
        $draft['step2_completed'] = true;
        session(["proposal_wizard.{$type}" => $draft]);

        return redirect()->route('proposal.wizard.details', $type);
    }

    /* ================================================================
     *  STEP 3 — ENTER DETAILS (METADATA)
     * ================================================================ */

    public function details(string $type)
    {
        $this->validateType($type);

        $draft = session("proposal_wizard.{$type}", []);
        $campuses = Campus::orderBy('name')->get();
        $programs = $type === 'project'
            ? ExtensionProgram::where('status', '!=', 'draft')->orderBy('title')->get()
            : collect();

        return view("extension.wizard.details-{$type}", compact('type', 'draft', 'campuses', 'programs'));
    }

    public function saveDetails(Request $request, string $type)
    {
        $this->validateType($type);

        // Validate details based on type
        $commonRules = [
            'title'            => 'required|string|max:255',
            'campus_id'        => 'required|exists:campuses,id',
            'target_start_date' => 'nullable|date',
            'target_end_date'  => 'nullable|date|after_or_equal:target_start_date',
        ];

        if ($type === 'program') {
            $request->validate(array_merge($commonRules, [
                'proponent_name'       => 'required|string|max:255',
                'funding_chmsu_gaa'    => 'nullable|numeric|min:0',
                'funding_chmsu_stf'    => 'nullable|numeric|min:0',
                'funding_collaborator' => 'nullable|numeric|min:0',
                'target_recipients'    => 'nullable|integer|min:0',
            ]));
        } else {
            $request->validate(array_merge($commonRules, [
                'budget_requirement' => 'nullable|numeric|min:0',
            ]));
        }

        $draft = session("proposal_wizard.{$type}", []);
        $draft['details'] = $request->except(['_token', '_current_step']);
        $draft['step3_completed'] = true;

        session(["proposal_wizard.{$type}" => $draft]);

        // Programs go to "Add Projects" step; projects go straight to confirmation
        if ($type === 'program') {
            return redirect()->route('proposal.wizard.projects', $type);
        }

        return redirect()->route('proposal.wizard.confirmation', $type);
    }

    /* ================================================================
     *  STEP 4 (program only) — ADD PROJECTS
     * ================================================================ */

    public function projects(string $type)
    {
        if ($type !== 'program') {
            return redirect()->route('proposal.wizard.confirmation', $type);
        }

        $draft = session("proposal_wizard.{$type}", []);
        $campuses = Campus::orderBy('name')->get();
        $draftProjects = $draft['projects'] ?? [];

        return view('extension.wizard.projects', compact('type', 'draft', 'campuses', 'draftProjects'));
    }

    public function saveProjects(Request $request, string $type)
    {
        if ($type !== 'program') {
            return redirect()->route('proposal.wizard.confirmation', $type);
        }

        // Validate at least 1 project with a title (Req 2.3, 2.4)
        $projects = $request->input('projects', []);
        $validProjects = collect($projects)->filter(fn($p) => !empty($p['title']));
        if ($validProjects->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'A Program must contain at least one Project. Please add at least one project with a title.');
        }

        $draft = session("proposal_wizard.{$type}", []);
        $draft['projects'] = $projects;
        $draft['step4_completed'] = true;

        session(["proposal_wizard.{$type}" => $draft]);

        return redirect()->route('proposal.wizard.confirmation', $type);
    }

    /* ================================================================
     *  CONFIRMATION
     * ================================================================ */

    public function confirmation(string $type)
    {
        $this->validateType($type);

        $draft = session("proposal_wizard.{$type}", []);
        $requirements = self::REQUIREMENTS[$type];
        $campuses = Campus::pluck('name', 'id');
        $programs = $type === 'project'
            ? ExtensionProgram::where('status', '!=', 'draft')->pluck('title', 'id')
            : collect();

        return view('extension.wizard.confirmation', compact('type', 'draft', 'requirements', 'campuses', 'programs'));
    }

    /* ================================================================
     *  FINAL SUBMIT → NEXT STEPS
     * ================================================================ */

    public function submit(string $type)
    {
        $this->validateType($type);

        $draft = session("proposal_wizard.{$type}", []);

        // Validate wizard step completeness (#12)
        $requiredSteps = ['step1_completed', 'step2_completed', 'step3_completed'];
        if ($type === 'program') {
            $requiredSteps[] = 'step4_completed';
        }
        foreach ($requiredSteps as $step) {
            if (empty($draft[$step])) {
                return redirect()->route('proposal.wizard.start', $type)
                    ->with('error', 'Please complete all wizard steps before submitting.');
            }
        }

        $details = $draft['details'] ?? [];

        // Validate program structural requirements (Req 2.1, 2.3, 2.4)
        if ($type === 'program') {
            $draftProjects = $draft['projects'] ?? [];
            $validProjects = array_filter($draftProjects, fn($p) => !empty($p['title']));
            if (empty($validProjects)) {
                return redirect()->route('proposal.wizard.projects', $type)
                    ->with('error', 'A Program must contain at least one Project before submission.');
            }

            $draftMembers = $details['members'] ?? [];
            $validMembers = array_filter($draftMembers, fn($m) => !empty($m['name']));
            if (empty($validMembers)) {
                return redirect()->route('proposal.wizard.details', $type)
                    ->with('error', 'A Program must have at least one participant or member before submission.');
            }
        }

        // Validate critical fields before final submission (#16)
        if (empty($details['title'])) {
            return back()->with('error', 'Please provide a title for your ' . $type . '.');
        }
        if (empty($details['campus_id'])) {
            return back()->with('error', 'Please select a campus.');
        }
        if ($type === 'program' && empty($details['proponent_name'])) {
            return back()->with('error', 'Please provide the proponent name.');
        }

        $dbId = $draft['_db_id'] ?? null;

        try {
            // Wrap ALL operations (DB writes, audit log, file moves) in a single
            // transaction so that a failure at any point rolls back cleanly.
            $model = DB::transaction(function () use ($type, $draft, $details, $dbId) {
                if ($type === 'program') {
                    $model = $this->createOrUpdateProgram($details, $dbId);

                    // Create projects under this program at 'proposal' status
                    // to match the parent's status and stay consistent with
                    // the CRUD flow (ExtensionProgramController::store).
                    $draftProjects = $draft['projects'] ?? [];
                    foreach ($draftProjects as $proj) {
                        if (!empty($proj['title'])) {
                            ExtensionProject::create([
                                'extension_program_id' => $model->id,
                                'title'                => $proj['title'],
                                'description'          => $proj['description'] ?? null,
                                'persons_responsible'   => $proj['persons_responsible'] ?? null,
                                'budget_requirement'    => $proj['budget_requirement'] ?? 0,
                                'budget_source'         => $proj['budget_source'] ?? null,
                                'target_start_date'     => $proj['target_start_date'] ?? null,
                                'target_end_date'       => $proj['target_end_date'] ?? null,
                                'status'                => 'proposal',
                                'campus_id'             => $details['campus_id'],
                                'created_by'            => auth()->id(),
                            ]);
                        }
                    }
                } else {
                    $model = $this->createOrUpdateProject($details, $dbId);
                }

                // Log the draft → proposal transition inside the transaction
                // (business rule W7: every transition is logged atomically)
                \App\Models\StatusTransitionLog::create([
                    'transitionable_type' => get_class($model),
                    'transitionable_id'   => $model->id,
                    'from_status'         => 'draft',
                    'to_status'           => 'proposal',
                    'transitioned_by'     => auth()->id(),
                    'is_bypass'           => false,
                    'notes'               => 'Submitted via Proposal Wizard.',
                ]);

                // Move files and attach document records inside the transaction
                // so any failure rolls back both the DB records and we can
                // track which files were moved for cleanup.
                $movedFiles = [];
                try {
                    foreach ($draft['uploaded_files'] ?? [] as $fileInfo) {
                        $newPath = str_replace('proposal-drafts/', 'status-documents/', $fileInfo['path']);
                        Storage::disk('public')->move($fileInfo['path'], $newPath);
                        $movedFiles[] = ['from' => $fileInfo['path'], 'to' => $newPath];

                        // Resolve document_type key from the label (match against known types)
                        $docTypeKey = array_search($fileInfo['label'], \App\Models\StatusDocument::DOCUMENT_TYPES);

                        $model->statusDocuments()->create([
                            'phase'         => 'proposal',
                            'label'         => $fileInfo['label'],
                            'document_type' => $docTypeKey ?: 'supporting',
                            'file_name'     => basename($newPath),
                            'file_path'     => $newPath,
                            'original_name' => $fileInfo['original_name'],
                            'mime_type'     => $fileInfo['mime_type'],
                            'file_size'     => $fileInfo['size'],
                            'uploaded_by'   => auth()->id(),
                        ]);
                    }
                } catch (\Exception $fileEx) {
                    // Rollback moved files before the DB transaction rolls back
                    foreach ($movedFiles as $moved) {
                        try {
                            Storage::disk('public')->move($moved['to'], $moved['from']);
                        } catch (\Exception $ignored) {
                            // Best-effort rollback
                        }
                    }
                    throw $fileEx;
                }

                return $model;
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while submitting. Please try again.');
        }

        session()->forget("proposal_wizard.{$type}");

        session(["proposal_wizard_result.{$type}" => [
            'model_id'    => $model->id,
            'model_title' => $model->title,
            'model_type'  => $type,
        ]]);

        return redirect()->route('proposal.wizard.next-steps', $type);
    }

    public function nextSteps(string $type)
    {
        $this->validateType($type);

        $result = session("proposal_wizard_result.{$type}");
        if (!$result) {
            return redirect()->route("extension.{$type}s.index")
                ->with('error', 'No submission result found.');
        }

        return view('extension.wizard.next-steps', compact('type', 'result'));
    }

    /* ================================================================
     *  SAVE DRAFT — persist to DB & redirect to list
     * ================================================================ */

    public function saveDraft(Request $request, string $type)
    {
        $this->validateType($type);

        $draft = session("proposal_wizard.{$type}", []);
        $step = (int) $request->input('_current_step', 1);

        if ($step == 1) {
            $draft['checked_requirements'] = $request->input('requirements', []);
            $draft['privacy_agreed'] = $request->boolean('privacy_agreed');
            $draft['comments'] = $request->input('comments', '');
        } elseif ($step == 2) {
            $draft['step2_completed'] = true;
        } elseif ($step == 3) {
            $draft['details'] = $request->except(['_token', '_current_step']);
        } elseif ($step == 4 && $type === 'program') {
            $draft['projects'] = $request->input('projects', []);
        }

        $this->persistDraft($type, $draft, $step);

        session()->forget("proposal_wizard.{$type}");

        return redirect()->route("extension.{$type}s.index")
            ->with('success', ucfirst($type) . ' proposal saved as draft.');
    }

    /* ================================================================
     *  CONTINUE DRAFT — load from DB into session
     * ================================================================ */

    public function continueDraft(string $type, $id)
    {
        $this->validateType($type);

        $modelClass = $type === 'program' ? ExtensionProgram::class : ExtensionProject::class;
        $model = $modelClass::where('id', $id)
            ->where('status', 'draft')
            ->where('created_by', auth()->id())
            ->firstOrFail();

        $draft = $model->draft_data ?? [];
        $draft['_db_id'] = $model->id;

        session(["proposal_wizard.{$type}" => $draft]);

        $step = $draft['draft_step'] ?? 1;

        return match ((int) $step) {
            2       => redirect()->route('proposal.wizard.upload', $type),
            3       => redirect()->route('proposal.wizard.details', $type),
            4       => $type === 'program'
                        ? redirect()->route('proposal.wizard.projects', $type)
                        : redirect()->route('proposal.wizard.confirmation', $type),
            5       => redirect()->route('proposal.wizard.confirmation', $type),
            default => redirect()->route('proposal.wizard.start', $type),
        };
    }

    /* ================================================================
     *  DELETE DRAFT
     * ================================================================ */

    public function deleteDraft(string $type, $id)
    {
        $this->validateType($type);

        $modelClass = $type === 'program' ? ExtensionProgram::class : ExtensionProject::class;
        $model = $modelClass::where('id', $id)
            ->where('status', 'draft')
            ->where('created_by', auth()->id())
            ->firstOrFail();

        // Clean up uploaded files
        $draftData = $model->draft_data ?? [];
        foreach ($draftData['uploaded_files'] ?? [] as $fileInfo) {
            Storage::disk('public')->delete($fileInfo['path'] ?? '');
        }

        $model->delete();

        return redirect()->route("extension.{$type}s.index")
            ->with('success', 'Draft deleted.');
    }

    /* ================================================================
     *  CANCEL — clean up session only
     * ================================================================ */

    public function cancel(string $type)
    {
        $this->validateType($type);

        $draft = session("proposal_wizard.{$type}", []);
        // Only delete files if there's no DB backing (otherwise they belong to the draft record)
        if (empty($draft['_db_id'])) {
            foreach ($draft['uploaded_files'] ?? [] as $fileInfo) {
                Storage::disk('public')->delete($fileInfo['path']);
            }
        }

        session()->forget("proposal_wizard.{$type}");

        return redirect()->route("extension.{$type}s.index")
            ->with('success', 'Submission cancelled.');
    }

    /* ================================================================
     *  PRIVATE — create/update models
     * ================================================================ */

    private function createOrUpdateProgram(array $details, ?int $dbId = null): ExtensionProgram
    {
        $data = [
            'title'                      => $details['title'] ?? 'Untitled Program',
            'ic_no'                      => $details['ic_no'] ?? null,
            'proponent_name'             => $details['proponent_name'] ?? null,
            'division_unit'              => $details['division_unit'] ?? null,
            'proponent_address'          => $details['proponent_address'] ?? null,
            'contact_no'                 => $details['contact_no'] ?? null,
            'cooperating_entities'       => $details['cooperating_entities'] ?? null,
            'cooperating_entity_address' => $details['cooperating_entity_address'] ?? null,
            'program_location'           => $details['program_location'] ?? null,
            'beneficiary_class'          => $details['beneficiary_class'] ?? null,
            'target_recipients'          => $details['target_recipients'] ?? null,
            'funding_chmsu_gaa'          => $details['funding_chmsu_gaa'] ?? 0,
            'funding_chmsu_gaa_note'     => $details['funding_chmsu_gaa_note'] ?? null,
            'funding_chmsu_stf'          => $details['funding_chmsu_stf'] ?? 0,
            'funding_collaborator'       => $details['funding_collaborator'] ?? 0,
            'funding_collaborator_note'  => $details['funding_collaborator_note'] ?? null,
            'funding_total'              => ($details['funding_chmsu_gaa'] ?? 0) + ($details['funding_chmsu_stf'] ?? 0) + ($details['funding_collaborator'] ?? 0),
            'target_start_date'          => $details['target_start_date'] ?? null,
            'target_end_date'            => $details['target_end_date'] ?? null,
            'program_leader'             => $details['program_leader'] ?? null,
            'rationale'                  => $details['rationale'] ?? null,
            'conceptual_framework'       => $details['conceptual_framework'] ?? null,
            'general_objective'          => $details['general_objective'] ?? null,
            'specific_objectives'        => $details['specific_objectives'] ?? null,
            'methodology'                => $details['methodology'] ?? null,
            'status'                     => 'proposal',
            'draft_data'                 => null,
            'campus_id'                  => $details['campus_id'] ?? null,
            'created_by'                 => auth()->id(),
        ];

        if ($dbId) {
            $program = ExtensionProgram::find($dbId);
            if ($program && $program->status === 'draft') {
                $program->update($data);
                $program->members()->delete();
                $this->saveMembers($program, $details);
                return $program;
            }
        }

        $program = ExtensionProgram::create($data);
        $this->saveMembers($program, $details);
        return $program;
    }

    private function saveMembers(ExtensionProgram $program, array $details): void
    {
        foreach ($details['members'] ?? [] as $member) {
            if (!empty($member['name'])) {
                $program->members()->create($member);
            }
        }
    }

    private function createOrUpdateProject(array $details, ?int $dbId = null): ExtensionProject
    {
        $data = [
            'extension_program_id' => $details['extension_program_id'] ?? null,
            'title'                => $details['title'] ?? 'Untitled Project',
            'description'          => $details['description'] ?? null,
            'persons_responsible'   => $details['persons_responsible'] ?? null,
            'budget_requirement'    => $details['budget_requirement'] ?? 0,
            'budget_source'         => $details['budget_source'] ?? null,
            'indicators_output'     => $details['indicators_output'] ?? null,
            'target_start_date'     => $details['target_start_date'] ?? null,
            'target_end_date'       => $details['target_end_date'] ?? null,
            'status'                => 'proposal',
            'draft_data'            => null,
            'campus_id'             => $details['campus_id'] ?? null,
            'created_by'            => auth()->id(),
        ];

        if ($dbId) {
            $project = ExtensionProject::find($dbId);
            if ($project && $project->status === 'draft') {
                $project->update($data);
                return $project;
            }
        }

        return ExtensionProject::create($data);
    }
}
