<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExtensionActivityRequest;
use App\Http\Requests\UpdateExtensionActivityRequest;
use App\Models\ExtensionActivity;
use App\Models\ExtensionProject;

class ExtensionActivityController extends Controller
{
    public function index()
    {
        $query = ExtensionActivity::with(['project.program', 'project.campus', 'creator']);

        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('project_id')) {
            $query->where('extension_project_id', request('project_id'));
        }
        if (request('overdue')) {
            $query->where('status', '!=', 'completed')
                  ->whereNotNull('target_date')
                  ->where('target_date', '<', now());
        }
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('persons_responsible', 'like', "%{$search}%");
            });
        }

        $activities = $query->latest()->paginate(15)->withQueryString();
        $projects = ExtensionProject::orderBy('title')->get();

        // Counts for tabs
        $totalActivities = ExtensionActivity::count();
        $statusCounts = [
            'proposal'  => ExtensionActivity::where('status', 'proposal')->count(),
            'ongoing'   => ExtensionActivity::where('status', 'ongoing')->count(),
            'completed' => ExtensionActivity::where('status', 'completed')->count(),
        ];
        $overdueCount = ExtensionActivity::where('status', '!=', 'completed')
            ->whereNotNull('target_date')
            ->where('target_date', '<', now())
            ->count();

        return view('extension.activities.index', compact('activities', 'projects', 'totalActivities', 'statusCounts', 'overdueCount'));
    }

    public function create()
    {
        $projects = ExtensionProject::orderBy('title')->get();
        $selectedProjectId = request('project_id');

        return view('extension.activities.create', compact('projects', 'selectedProjectId'));
    }

    public function store(StoreExtensionActivityRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();

        $activity = ExtensionActivity::create($data);

        return redirect()
            ->route('extension.projects.show', $activity->extension_project_id)
            ->with('success', 'Activity created successfully.');
    }

    public function edit(ExtensionActivity $activity)
    {
        $projects = ExtensionProject::orderBy('title')->get();

        return view('extension.activities.edit', compact('activity', 'projects'));
    }

    public function update(UpdateExtensionActivityRequest $request, ExtensionActivity $activity)
    {
        $activity->update($request->validated());

        return redirect()
            ->route('extension.projects.show', $activity->extension_project_id)
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy(ExtensionActivity $activity)
    {
        $projectId = $activity->extension_project_id;
        $activity->delete();

        return redirect()
            ->route('extension.projects.show', $projectId)
            ->with('success', 'Activity deleted successfully.');
    }
}
