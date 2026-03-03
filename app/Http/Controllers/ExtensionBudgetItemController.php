<?php

namespace App\Http\Controllers;

use App\Models\ExtensionBudgetItem;
use App\Models\ExtensionProject;
use Illuminate\Http\Request;

class ExtensionBudgetItemController extends Controller
{
    /**
     * List budget items for a project.
     */
    public function index(ExtensionProject $project)
    {
        $query = $project->budgetItems();

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('item_description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $budgetItems = $query->latest()->paginate(15)->withQueryString();
        $totalBudget = $project->budgetItems()->sum('total_budget');

        return view('extension.budget-items.index', compact('budgetItems', 'project', 'totalBudget'));
    }

    /**
     * Store one or more budget items for a project.
     */
    public function store(Request $request, ExtensionProject $project)
    {
        if (! auth()->user()->isAdmin() && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to add budget items to this project.');
        }

        $validated = $request->validate([
            'budget_items'                       => ['required', 'array', 'min:1'],
            'budget_items.*.item_description'    => ['required', 'string', 'max:500'],
            'budget_items.*.location'            => ['nullable', 'string', 'max:255'],
            'budget_items.*.total_budget'        => ['required', 'numeric', 'min:0'],
        ]);

        $count = 0;
        foreach ($validated['budget_items'] as $row) {
            ExtensionBudgetItem::create([
                'extension_project_id' => $project->id,
                'item_description'     => $row['item_description'],
                'location'             => $row['location'] ?? null,
                'total_budget'         => $row['total_budget'],
            ]);
            $count++;
        }

        $label = $count === 1 ? 'Budget item added' : "{$count} budget items added";

        $redirectRoute = $request->input('redirect_to') === 'index'
            ? route('extension.budget-items.index', $project->id)
            : route('extension.projects.show', $project->id);

        return redirect($redirectRoute)->with('success', "{$label} successfully.");
    }

    /**
     * Update a budget item.
     */
    public function update(Request $request, ExtensionProject $project, ExtensionBudgetItem $budgetItem)
    {
        if ($budgetItem->extension_project_id !== $project->id) {
            abort(404, 'Budget item not found in this project.');
        }

        if (! auth()->user()->isAdmin() && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to update this budget item.');
        }

        $data = $request->validate([
            'item_description' => ['required', 'string', 'max:500'],
            'location'         => ['nullable', 'string', 'max:255'],
            'total_budget'     => ['required', 'numeric', 'min:0'],
        ]);

        $budgetItem->update($data);

        $redirectRoute = $request->input('redirect_to') === 'index'
            ? route('extension.budget-items.index', $project->id)
            : route('extension.projects.show', $project->id);

        return redirect($redirectRoute)->with('success', 'Budget item updated successfully.');
    }

    /**
     * Delete a budget item.
     */
    public function destroy(Request $request, ExtensionProject $project, ExtensionBudgetItem $budgetItem)
    {
        if ($budgetItem->extension_project_id !== $project->id) {
            abort(404, 'Budget item not found in this project.');
        }

        if (! auth()->user()->isAdmin() && $project->created_by !== auth()->id()) {
            abort(403, 'You do not have permission to delete this budget item.');
        }

        $budgetItem->delete();

        return redirect()
            ->route('extension.budget-items.index', $project->id)
            ->with('success', 'Budget item removed successfully.');
    }
}
