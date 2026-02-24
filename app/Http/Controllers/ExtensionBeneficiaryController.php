<?php

namespace App\Http\Controllers;

use App\Models\ExtensionBeneficiary;
use App\Models\ExtensionProject;
use Illuminate\Http\Request;

class ExtensionBeneficiaryController extends Controller
{
    public function index()
    {
        $query = ExtensionBeneficiary::with('project.program');

        if (request('project_id')) {
            $query->where('extension_project_id', request('project_id'));
        }
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('organization', 'like', "%{$search}%");
            });
        }

        $beneficiaries = $query->latest()->paginate(15)->withQueryString();
        $projects = ExtensionProject::with('program')
            ->withCount('beneficiaries')
            ->orderBy('title')
            ->get();
        $totalBeneficiaries = ExtensionBeneficiary::count();

        return view('extension.beneficiaries.index', compact('beneficiaries', 'projects', 'totalBeneficiaries'));
    }

    public function create()
    {
        $projects = ExtensionProject::orderBy('title')->get();
        $selectedProjectId = request('project_id');

        return view('extension.beneficiaries.create', compact('projects', 'selectedProjectId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'extension_project_id' => ['required', 'exists:extension_projects,id'],
            'name'                 => ['required', 'string', 'max:255'],
            'address'              => ['nullable', 'string', 'max:500'],
            'contact_no'           => ['nullable', 'string', 'max:20'],
            'organization'         => ['nullable', 'string', 'max:255'],
        ]);

        ExtensionBeneficiary::create($data);

        return redirect()
            ->route('extension.projects.show', $data['extension_project_id'])
            ->with('success', 'Beneficiary added successfully.');
    }

    public function edit(ExtensionBeneficiary $beneficiary)
    {
        $beneficiary->load('project.program');
        $projects = ExtensionProject::with('program')->orderBy('title')->get();

        return view('extension.beneficiaries.edit', compact('beneficiary', 'projects'));
    }

    public function update(Request $request, ExtensionBeneficiary $beneficiary)
    {
        $data = $request->validate([
            'extension_project_id' => ['required', 'exists:extension_projects,id'],
            'name'                 => ['required', 'string', 'max:255'],
            'address'              => ['nullable', 'string', 'max:500'],
            'contact_no'           => ['nullable', 'string', 'max:20'],
            'organization'         => ['nullable', 'string', 'max:255'],
        ]);

        $beneficiary->update($data);

        return redirect()
            ->route('extension.projects.show', $beneficiary->extension_project_id)
            ->with('success', 'Beneficiary updated successfully.');
    }

    public function destroy(ExtensionBeneficiary $beneficiary)
    {
        $projectId = $beneficiary->extension_project_id;
        $beneficiary->delete();

        return redirect()
            ->route('extension.projects.show', $projectId)
            ->with('success', 'Beneficiary removed successfully.');
    }
}
