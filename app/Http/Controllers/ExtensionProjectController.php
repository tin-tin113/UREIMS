<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExtensionProjectRequest;
use App\Http\Requests\UpdateExtensionProjectRequest;
use App\Models\Campus;
use App\Models\ExtensionProgram;
use App\Models\ExtensionProject;
use App\Models\StatusTransitionLog;
use App\Services\WorkflowService;

class ExtensionProjectController extends Controller
{
    public function index()
    {
        $query = ExtensionProject::with(['campus', 'program', 'creator', 'activities']);

        // Filters
        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('campus_id')) {
            $query->where('campus_id', request('campus_id'));
        }
        if (request('program_id')) {
            if (request('program_id') === 'standalone') {
                $query->whereNull('extension_program_id');
            } else {
                $query->where('extension_program_id', request('program_id'));
            }
        }
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('persons_responsible', 'like', "%{$search}%")
                  ->orWhere('budget_source', 'like', "%{$search}%")
                  ->orWhere('indicators_output', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest()->paginate(15)->withQueryString();
        $campuses = Campus::orderBy('name')->get();
        $programs = ExtensionProgram::orderBy('title')->get();

        // Counts for tabs
        $totalProjects = ExtensionProject::count();
        $statusCounts = [
            'draft'     => ExtensionProject::where('status', 'draft')->count(),
            'proposal'  => ExtensionProject::where('status', 'proposal')->count(),
            'ongoing'   => ExtensionProject::where('status', 'ongoing')->count(),
            'completed' => ExtensionProject::where('status', 'completed')->count(),
        ];

        return view('extension.projects.index', compact('projects', 'campuses', 'programs', 'totalProjects', 'statusCounts'));
    }

    public function create()
    {
        $campuses = Campus::orderBy('name')->get();
        $programs = ExtensionProgram::orderBy('title')->get();

        // Pre-select program if passed via query string
        $selectedProgramId = request('program_id');

        return view('extension.projects.create', compact('campuses', 'programs', 'selectedProgramId'));
    }

    public function store(StoreExtensionProjectRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        // Non-admin users can only create proposals
        if (! auth()->user()->isAdmin()) {
            $data['status'] = 'proposal';
        }

        $project = ExtensionProject::create($data);

        // Log the initial creation for audit trail (business rule W7)
        StatusTransitionLog::create([
            'transitionable_type' => get_class($project),
            'transitionable_id'   => $project->id,
            'from_status'         => 'created',
            'to_status'           => $project->status,
            'transitioned_by'     => auth()->id(),
            'is_bypass'           => false,
            'notes'               => 'Initial creation via project form.',
        ]);

        return redirect()
            ->route('extension.projects.show', $project)
            ->with('success', 'Extension project created successfully.');
    }

    public function show(ExtensionProject $project)
    {
        $project->load([
            'campus',
            'program',
            'creator',
            'activities' => fn ($q) => $q->orderBy('target_date'),
            'beneficiaries',
            'budgetItems',
            'statusDocuments.uploader',
            'transitionLogs.transitioner',
        ]);

        $workflowCheck = WorkflowService::canAdvance($project);

        return view('extension.projects.show', compact('project', 'workflowCheck'));
    }

    public function edit(ExtensionProject $project)
    {
        if (! auth()->user()->isAdmin() && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to edit this project.');
        }

        $campuses = Campus::orderBy('name')->get();
        $programs = ExtensionProgram::orderBy('title')->get();

        return view('extension.projects.edit', compact('project', 'campuses', 'programs'));
    }

    public function update(UpdateExtensionProjectRequest $request, ExtensionProject $project)
    {
        if (! auth()->user()->isAdmin() && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to update this project.');
        }

        $data = $request->validated();

        // Status changes must go through workflow advance/bypass — never via edit form
        unset($data['status']);

        $project->update($data);

        return redirect()
            ->route('extension.projects.show', $project)
            ->with('success', 'Extension project updated successfully.');
    }

    public function destroy(ExtensionProject $project)
    {
        if (! auth()->user()->isAdmin() && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to delete this project.');
        }

        $deleteCheck = WorkflowService::canDelete($project);
        if (! $deleteCheck['can_delete']) {
            return redirect()
                ->route('extension.projects.show', $project)
                ->with('error', implode(' ', $deleteCheck['errors']));
        }

        $project->delete();

        return redirect()
            ->route('extension.projects.index')
            ->with('success', 'Extension project deleted successfully.');
    }
}
