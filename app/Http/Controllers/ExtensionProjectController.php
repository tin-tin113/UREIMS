<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExtensionProjectRequest;
use App\Http\Requests\UpdateExtensionProjectRequest;
use App\Models\Campus;
use App\Models\ExtensionProgram;
use App\Models\ExtensionProject;

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
                  ->orWhere('persons_responsible', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest()->paginate(15)->withQueryString();
        $campuses = Campus::orderBy('name')->get();
        $programs = ExtensionProgram::orderBy('title')->get();

        // Counts for tabs
        $totalProjects = ExtensionProject::count();
        $statusCounts = [
            'proposal'     => ExtensionProject::where('status', 'proposal')->count(),
            'under_review' => ExtensionProject::where('status', 'under_review')->count(),
            'approved'     => ExtensionProject::where('status', 'approved')->count(),
            'ongoing'      => ExtensionProject::where('status', 'ongoing')->count(),
            'completed'    => ExtensionProject::where('status', 'completed')->count(),
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

        $project = ExtensionProject::create($data);

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

        $workflowCheck = \App\Services\WorkflowService::canAdvance($project);

        return view('extension.projects.show', compact('project', 'workflowCheck'));
    }

    public function edit(ExtensionProject $project)
    {
        $campuses = Campus::orderBy('name')->get();
        $programs = ExtensionProgram::orderBy('title')->get();

        return view('extension.projects.edit', compact('project', 'campuses', 'programs'));
    }

    public function update(UpdateExtensionProjectRequest $request, ExtensionProject $project)
    {
        $project->update($request->validated());

        return redirect()
            ->route('extension.projects.show', $project)
            ->with('success', 'Extension project updated successfully.');
    }

    public function destroy(ExtensionProject $project)
    {
        $project->delete();

        return redirect()
            ->route('extension.projects.index')
            ->with('success', 'Extension project deleted successfully.');
    }
}
