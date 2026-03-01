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
                  ->orWhere('ic_no', 'like', "%{$search}%")
                  ->orWhere('program_leader', 'like', "%{$search}%")
                  ->orWhere('program_location', 'like', "%{$search}%")
                  ->orWhere('division_unit', 'like', "%{$search}%")
                  ->orWhere('cooperating_entities', 'like', "%{$search}%")
                  ->orWhere('rationale', 'like', "%{$search}%")
                  ->orWhere('general_objective', 'like', "%{$search}%");
            });
        }

        $programs = $query->latest()->paginate(15)->withQueryString();
        $campuses = Campus::orderBy('name')->get();

        // Counts for tabs
        $totalPrograms = ExtensionProgram::count();
        $statusCounts = [
            'draft'     => ExtensionProgram::where('status', 'draft')->count(),
            'proposal'  => ExtensionProgram::where('status', 'proposal')->count(),
            'ongoing'   => ExtensionProgram::where('status', 'ongoing')->count(),
            'completed' => ExtensionProgram::where('status', 'completed')->count(),
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

        // Non-admin users can only create proposals
        if (! auth()->user()->isAdmin()) {
            $data['status'] = 'proposal';
        }

        // Calculate funding total
        $data['funding_total'] = ($data['funding_chmsu_gaa'] ?? 0)
                               + ($data['funding_chmsu_stf'] ?? 0)
                               + ($data['funding_collaborator'] ?? 0);

        $members = $data['members'] ?? [];
        $projects = $data['projects'] ?? [];
        unset($data['members'], $data['projects']);

        // Structural integrity: non-draft programs require projects and members (Req 2.1, 2.3, 2.4)
        $effectiveStatus = $data['status'] ?? 'proposal';
        if ($effectiveStatus !== 'draft') {
            if (collect($projects)->filter(fn ($p) => ! empty($p['title']))->isEmpty()) {
                return back()->withErrors(['projects' => 'A Program must contain at least one Project.'])->withInput();
            }
            if (collect($members)->filter(fn ($m) => ! empty($m['name']))->isEmpty()) {
                return back()->withErrors(['members' => 'A Program must have at least one participant or member.'])->withInput();
            }
        }

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
        if (! auth()->user()->isAdmin() && $program->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to edit this program.');
        }

        $program->load(['members', 'projects']);
        $campuses = Campus::orderBy('name')->get();

        return view('extension.programs.edit', compact('program', 'campuses'));
    }

    public function update(UpdateExtensionProgramRequest $request, ExtensionProgram $program)
    {
        if (! auth()->user()->isAdmin() && $program->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to update this program.');
        }

        $data = $request->validated();

        // Non-admin users cannot change status directly
        if (! auth()->user()->isAdmin()) {
            unset($data['status']);
        }

        $data['funding_total'] = ($data['funding_chmsu_gaa'] ?? 0)
                               + ($data['funding_chmsu_stf'] ?? 0)
                               + ($data['funding_collaborator'] ?? 0);

        $members = $data['members'] ?? [];
        $projects = $data['projects'] ?? [];
        unset($data['members'], $data['projects']);

        // Structural integrity: non-draft programs require projects and members (Req 2.1, 2.3, 2.4)
        $effectiveStatus = $data['status'] ?? $program->status;
        if ($effectiveStatus !== 'draft') {
            if (collect($projects)->filter(fn ($p) => ! empty($p['title']))->isEmpty()) {
                return back()->withErrors(['projects' => 'A Program must contain at least one Project.'])->withInput();
            }
            if (collect($members)->filter(fn ($m) => ! empty($m['name']))->isEmpty()) {
                return back()->withErrors(['members' => 'A Program must have at least one participant or member.'])->withInput();
            }
        }

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
        if (! auth()->user()->isAdmin() && $program->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to delete this program.');
        }

        $deleteCheck = \App\Services\WorkflowService::canDelete($program);
        if (! $deleteCheck['can_delete']) {
            return redirect()
                ->route('extension.programs.show', $program)
                ->with('error', implode(' ', $deleteCheck['errors']));
        }

        $program->delete();

        return redirect()
            ->route('extension.programs.index')
            ->with('success', 'Extension program deleted successfully.');
    }
}
