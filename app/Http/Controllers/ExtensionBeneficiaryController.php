<?php

namespace App\Http\Controllers;

use App\Models\ExtensionBeneficiary;
use App\Models\ExtensionProject;
use Illuminate\Http\Request;

class ExtensionBeneficiaryController extends Controller
{
    public function index(ExtensionProject $project)
    {
        $query = $project->beneficiaries();

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('organization', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('contact_no', 'like', "%{$search}%")
                  ->orWhere('sector', 'like', "%{$search}%");
            });
        }

        if (request('type')) {
            $query->where('type', request('type'));
        }

        if (request('sector')) {
            $query->where('sector', request('sector'));
        }

        $beneficiaries = $query->latest()->paginate(15)->withQueryString();

        return view('extension.beneficiaries.index', compact('beneficiaries', 'project'));
    }

    public function create(ExtensionProject $project)
    {
        // The add form is now inline on the index page
        return redirect()->route('extension.beneficiaries.index', $project->id);
    }

    public function store(Request $request, ExtensionProject $project)
    {
        if (! auth()->user()->isAdmin() && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to add beneficiaries to this project.');
        }

        $validated = $request->validate([
            'beneficiaries'                  => ['required', 'array', 'min:1'],
            'beneficiaries.*.name'           => ['required', 'string', 'max:255'],
            'beneficiaries.*.address'        => ['nullable', 'string', 'max:500'],
            'beneficiaries.*.contact_no'     => ['nullable', 'string', 'max:20'],
            'beneficiaries.*.organization'   => ['nullable', 'string', 'max:255'],
            'beneficiaries.*.type'           => ['required', 'in:individual,organization,community'],
            'beneficiaries.*.sector'         => ['nullable', 'string', 'max:50'],
            'beneficiaries.*.gender'         => ['nullable', 'in:male,female'],
            'beneficiaries.*.male_count'     => ['nullable', 'integer', 'min:0'],
            'beneficiaries.*.female_count'   => ['nullable', 'integer', 'min:0'],
        ]);

        $count = 0;
        foreach ($validated['beneficiaries'] as $row) {
            // Derive counts from gender radio if provided, otherwise use explicit counts
            $gender = $row['gender'] ?? null;
            if ($gender === 'male') {
                $male   = 1;
                $female = 0;
            } elseif ($gender === 'female') {
                $male   = 0;
                $female = 1;
            } else {
                $male   = $row['male_count'] ?? 0;
                $female = $row['female_count'] ?? 0;
            }

            ExtensionBeneficiary::create([
                'extension_project_id' => $project->id,
                'name'                 => $row['name'],
                'address'              => $row['address'] ?? null,
                'contact_no'           => $row['contact_no'] ?? null,
                'organization'         => $row['organization'] ?? null,
                'type'                 => $row['type'],
                'sector'               => $row['sector'] ?? null,
                'male_count'           => $male,
                'female_count'         => $female,
                // total_count is auto-computed by the model's boot saving event
            ]);
            $count++;
        }

        $label = $count === 1 ? 'Beneficiary added' : "{$count} beneficiaries added";

        $redirectRoute = $request->input('redirect_to') === 'index'
            ? route('extension.beneficiaries.index', $project->id)
            : route('extension.projects.show', $project->id);

        return redirect($redirectRoute)->with('success', "{$label} successfully.");
    }

    public function edit(ExtensionProject $project, ExtensionBeneficiary $beneficiary)
    {
        // The edit modal is now inline on the index page
        return redirect()->route('extension.beneficiaries.index', $project->id);
    }

    public function update(Request $request, ExtensionProject $project, ExtensionBeneficiary $beneficiary)
    {
        if ($beneficiary->extension_project_id !== $project->id) {
            abort(404, 'Beneficiary not found in this project.');
        }

        if (! auth()->user()->isAdmin() && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to update this beneficiary.');
        }

        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'address'              => ['nullable', 'string', 'max:500'],
            'contact_no'           => ['nullable', 'string', 'max:20'],
            'organization'         => ['nullable', 'string', 'max:255'],
            'type'                 => ['required', 'in:individual,organization,community'],
            'sector'               => ['nullable', 'string', 'max:50'],
            'gender'               => ['nullable', 'in:male,female'],
            'male_count'           => ['nullable', 'integer', 'min:0'],
            'female_count'         => ['nullable', 'integer', 'min:0'],
        ]);

        // Derive counts from gender radio if provided, otherwise use explicit counts
        $gender = $data['gender'] ?? null;
        unset($data['gender']);

        if ($gender === 'male') {
            $data['male_count']   = 1;
            $data['female_count'] = 0;
        } elseif ($gender === 'female') {
            $data['male_count']   = 0;
            $data['female_count'] = 1;
        } else {
            $data['male_count']   = $data['male_count'] ?? 0;
            $data['female_count'] = $data['female_count'] ?? 0;
        }
        // total_count is auto-computed by the model's boot saving event

        $beneficiary->update($data);

        $redirectRoute = $request->input('redirect_to') === 'index'
            ? route('extension.beneficiaries.index', $project->id)
            : route('extension.projects.show', $project->id);

        return redirect($redirectRoute)->with('success', 'Beneficiary updated successfully.');
    }

    public function destroy(Request $request, ExtensionProject $project, ExtensionBeneficiary $beneficiary)
    {
        if ($beneficiary->extension_project_id !== $project->id) {
            abort(404, 'Beneficiary not found in this project.');
        }

        if (! auth()->user()->isAdmin() && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to delete this beneficiary.');
        }

        // Structural guard: delegate to WorkflowService for centralized policy
        $deleteCheck = \App\Services\WorkflowService::canDeleteBeneficiary($beneficiary);
        if (! $deleteCheck['can_delete']) {
            return redirect()
                ->route('extension.beneficiaries.index', $project->id)
                ->with('error', implode(' ', $deleteCheck['errors']));
        }

        $beneficiary->delete();

        return redirect()
            ->route('extension.beneficiaries.index', $project->id)
            ->with('success', 'Beneficiary removed successfully.');
    }
}
