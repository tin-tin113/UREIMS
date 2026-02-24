<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExtensionProgramRequest;
use App\Http\Requests\UpdateExtensionProgramRequest;
use App\Models\Campus;
use App\Models\ExtensionProgram;
use App\Models\ExtensionProgramMember;
use App\Models\ExtensionProject;

class ExtensionProgramController extends Controller
{
    public function index()
    {
        $query = ExtensionProgram::with(['campus', 'creator', 'projects']);

        // Filters
        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('campus_id')) {
            $query->where('campus_id', request('campus_id'));
        }
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('proponent_name', 'like', "%{$search}%")
                  ->orWhere('ic_no', 'like', "%{$search}%");
            });
        }

        $programs = $query->latest()->paginate(15)->withQueryString();
        $campuses = Campus::orderBy('name')->get();

        // Counts for tabs
        $totalPrograms = ExtensionProgram::count();
        $statusCounts = [
            'proposal'     => ExtensionProgram::where('status', 'proposal')->count(),
            'under_review' => ExtensionProgram::where('status', 'under_review')->count(),
            'approved'     => ExtensionProgram::where('status', 'approved')->count(),
            'ongoing'      => ExtensionProgram::where('status', 'ongoing')->count(),
            'completed'    => ExtensionProgram::where('status', 'completed')->count(),
        ];

        return view('extension.programs.index', compact('programs', 'campuses', 'totalPrograms', 'statusCounts'));
    }

    public function create()
    {
        $campuses = Campus::orderBy('name')->get();

        return view('extension.programs.create', compact('campuses'));
    }

    public function store(StoreExtensionProgramRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        // Calculate funding total
        $data['funding_total'] = ($data['funding_chmsu_gaa'] ?? 0)
                               + ($data['funding_chmsu_stf'] ?? 0)
                               + ($data['funding_collaborator'] ?? 0);

        $members = $data['members'] ?? [];
        $projects = $data['projects'] ?? [];
        unset($data['members'], $data['projects']);

        $program = ExtensionProgram::create($data);

        // Save members
        foreach ($members as $member) {
            if (! empty($member['name'])) {
                $program->members()->create($member);
            }
        }

        // Save inline projects
        foreach ($projects as $proj) {
            if (! empty($proj['title'])) {
                $program->projects()->create([
                    'title'               => $proj['title'],
                    'description'         => $proj['description'] ?? null,
                    'persons_responsible' => $proj['persons_responsible'] ?? null,
                    'budget_requirement'  => $proj['budget_requirement'] ?? null,
                    'budget_source'       => $proj['budget_source'] ?? null,
                    'target_start_date'   => $proj['target_start_date'] ?? null,
                    'target_end_date'     => $proj['target_end_date'] ?? null,
                    'status'              => $proj['status'] ?? 'proposal',
                    'campus_id'           => $data['campus_id'],
                    'created_by'          => auth()->id(),
                ]);
            }
        }

        return redirect()
            ->route('extension.programs.show', $program)
            ->with('success', 'Extension program created successfully.');
    }

    public function show(ExtensionProgram $program)
    {
        $program->load(['campus', 'creator', 'members', 'projects.activities', 'projects.campus', 'statusDocuments.uploader', 'transitionLogs.transitioner']);

        $workflowCheck = \App\Services\WorkflowService::canAdvance($program);

        return view('extension.programs.show', compact('program', 'workflowCheck'));
    }

    public function edit(ExtensionProgram $program)
    {
        $program->load(['members', 'projects']);
        $campuses = Campus::orderBy('name')->get();

        return view('extension.programs.edit', compact('program', 'campuses'));
    }

    public function update(UpdateExtensionProgramRequest $request, ExtensionProgram $program)
    {
        $data = $request->validated();

        $data['funding_total'] = ($data['funding_chmsu_gaa'] ?? 0)
                               + ($data['funding_chmsu_stf'] ?? 0)
                               + ($data['funding_collaborator'] ?? 0);

        $members = $data['members'] ?? [];
        $projects = $data['projects'] ?? [];
        unset($data['members'], $data['projects']);

        $program->update($data);

        // Sync members: delete old, insert new
        $program->members()->delete();
        foreach ($members as $member) {
            if (! empty($member['name'])) {
                $program->members()->create($member);
            }
        }

        // Sync inline projects
        $submittedIds = collect($projects)->pluck('id')->filter()->toArray();

        // Delete projects that were removed from the form
        $program->projects()->whereNotIn('id', $submittedIds)->delete();

        // Create or update projects
        foreach ($projects as $proj) {
            if (empty($proj['title'])) {
                continue;
            }

            $projectData = [
                'title'               => $proj['title'],
                'description'         => $proj['description'] ?? null,
                'persons_responsible' => $proj['persons_responsible'] ?? null,
                'budget_requirement'  => $proj['budget_requirement'] ?? null,
                'budget_source'       => $proj['budget_source'] ?? null,
                'target_start_date'   => $proj['target_start_date'] ?? null,
                'target_end_date'     => $proj['target_end_date'] ?? null,
                'status'              => $proj['status'] ?? 'proposal',
                'campus_id'           => $data['campus_id'],
            ];

            if (! empty($proj['id'])) {
                // Update existing project
                ExtensionProject::where('id', $proj['id'])
                    ->where('extension_program_id', $program->id)
                    ->update($projectData);
            } else {
                // Create new project
                $projectData['created_by'] = auth()->id();
                $program->projects()->create($projectData);
            }
        }

        return redirect()
            ->route('extension.programs.show', $program)
            ->with('success', 'Extension program updated successfully.');
    }

    public function destroy(ExtensionProgram $program)
    {
        $program->delete();

        return redirect()
            ->route('extension.programs.index')
            ->with('success', 'Extension program deleted successfully.');
    }
}
