<?php

namespace App\Http\Controllers;

use App\Models\EvaluationForm;
use App\Models\EvaluationCriteria;
use App\Models\ExtensionProgram;
use Illuminate\Http\Request;

class EvaluationFormController extends Controller
{
    /**
     * List all evaluation forms (admin sees all, staff sees own programs' forms).
     */
    public function index(Request $request)
    {
        $query = EvaluationForm::with(['program', 'creator'])
            ->withCount(['criteria', 'responses']);

        if (! auth()->user()->isAdmin()) {
            $programIds = ExtensionProgram::where('created_by', auth()->id())->pluck('id');
            $query->whereIn('extension_program_id', $programIds);
        }

        if ($request->filled('program_id')) {
            $query->where('extension_program_id', $request->program_id);
        }

        $forms = $query->latest()->paginate(15)->withQueryString();

        $programs = auth()->user()->isAdmin()
            ? ExtensionProgram::orderBy('title')->get(['id', 'title'])
            : ExtensionProgram::where('created_by', auth()->id())->orderBy('title')->get(['id', 'title']);

        return view('evaluation.forms.index', compact('forms', 'programs'));
    }

    /**
     * Show the create form page.
     */
    public function create(Request $request)
    {
        $programs = auth()->user()->isAdmin()
            ? ExtensionProgram::orderBy('title')->get(['id', 'title'])
            : ExtensionProgram::where('created_by', auth()->id())->orderBy('title')->get(['id', 'title']);

        $selectedProgram = $request->program_id;

        return view('evaluation.forms.create', compact('programs', 'selectedProgram'));
    }

    /**
     * Store a new evaluation form with its criteria.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'extension_program_id' => ['required', 'exists:extension_programs,id'],
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string', 'max:2000'],
            'criteria'             => ['required', 'array', 'min:1'],
            'criteria.*.label'     => ['required', 'string', 'max:500'],
            'criteria.*.type'      => ['required', 'in:rating,text'],
            'criteria.*.is_required' => ['sometimes', 'boolean'],
        ]);

        // Permission check for non-admin
        if (! auth()->user()->isAdmin()) {
            $program = ExtensionProgram::findOrFail($data['extension_program_id']);
            if ($program->created_by !== auth()->id()) {
                abort(403, 'You can only create forms for your own programs.');
            }
        }

        $form = EvaluationForm::create([
            'extension_program_id' => $data['extension_program_id'],
            'title'                => $data['title'],
            'description'          => $data['description'] ?? null,
            'created_by'           => auth()->id(),
        ]);

        foreach ($data['criteria'] as $i => $criterionData) {
            EvaluationCriteria::create([
                'evaluation_form_id' => $form->id,
                'label'              => $criterionData['label'],
                'type'               => $criterionData['type'],
                'sort_order'         => $i,
                'is_required'        => $criterionData['is_required'] ?? true,
            ]);
        }

        return redirect()
            ->route('evaluation.forms.show', $form)
            ->with('success', 'Evaluation form created successfully.');
    }

    /**
     * Show form details with criteria and response summary.
     */
    public function show(EvaluationForm $form)
    {
        $form->load([
            'program',
            'creator',
            'criteria',
            'responses.activity',
            'responses.answers',
        ]);

        // Get activities available for this program
        $activities = $form->program->projects()
            ->with('activities')
            ->get()
            ->pluck('activities')
            ->flatten()
            ->sortBy('title');

        // Compute per-criterion averages
        $criteriaStats = [];
        foreach ($form->criteria as $criterion) {
            if ($criterion->type === 'rating') {
                $answers = $criterion->answers()
                    ->whereHas('response', fn ($q) => $q->where('evaluation_form_id', $form->id))
                    ->whereNotNull('numeric_value')
                    ->get();

                $criteriaStats[$criterion->id] = [
                    'count'   => $answers->count(),
                    'average' => $answers->count() > 0 ? round($answers->avg('numeric_value'), 2) : 0,
                    'total'   => $answers->sum('numeric_value'),
                ];
            }
        }

        // Overall stats
        $responseCount   = $form->responses->count();
        $overallAverage  = $responseCount > 0
            ? round($form->responses->avg('average_score'), 2)
            : 0;

        return view('evaluation.forms.show', compact(
            'form', 'activities', 'criteriaStats', 'responseCount', 'overallAverage'
        ));
    }

    /**
     * Show the edit form page.
     */
    public function edit(EvaluationForm $form)
    {
        if (! auth()->user()->isAdmin() && $form->created_by !== auth()->id()) {
            abort(403, 'You can only edit your own forms.');
        }

        $form->load('criteria');

        $programs = auth()->user()->isAdmin()
            ? ExtensionProgram::orderBy('title')->get(['id', 'title'])
            : ExtensionProgram::where('created_by', auth()->id())->orderBy('title')->get(['id', 'title']);

        return view('evaluation.forms.edit', compact('form', 'programs'));
    }

    /**
     * Update evaluation form and its criteria.
     */
    public function update(Request $request, EvaluationForm $form)
    {
        if (! auth()->user()->isAdmin() && $form->created_by !== auth()->id()) {
            abort(403, 'You can only edit your own forms.');
        }

        $data = $request->validate([
            'extension_program_id' => ['required', 'exists:extension_programs,id'],
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string', 'max:2000'],
            'is_active'            => ['sometimes', 'boolean'],
            'criteria'             => ['required', 'array', 'min:1'],
            'criteria.*.id'        => ['nullable', 'integer'],
            'criteria.*.label'     => ['required', 'string', 'max:500'],
            'criteria.*.type'      => ['required', 'in:rating,text'],
            'criteria.*.is_required' => ['sometimes', 'boolean'],
        ]);

        $form->update([
            'extension_program_id' => $data['extension_program_id'],
            'title'                => $data['title'],
            'description'          => $data['description'] ?? null,
            'is_active'            => $data['is_active'] ?? $form->is_active,
        ]);

        // Sync criteria: keep existing IDs that are still present, create new ones, delete removed
        $existingIds = collect($data['criteria'])
            ->pluck('id')
            ->filter()
            ->toArray();

        // Delete criteria no longer in the submitted list
        $form->criteria()->whereNotIn('id', $existingIds)->delete();

        foreach ($data['criteria'] as $i => $criterionData) {
            if (! empty($criterionData['id'])) {
                // Update existing
                EvaluationCriteria::where('id', $criterionData['id'])
                    ->where('evaluation_form_id', $form->id)
                    ->update([
                        'label'       => $criterionData['label'],
                        'type'        => $criterionData['type'],
                        'sort_order'  => $i,
                        'is_required' => $criterionData['is_required'] ?? true,
                    ]);
            } else {
                // Create new
                EvaluationCriteria::create([
                    'evaluation_form_id' => $form->id,
                    'label'              => $criterionData['label'],
                    'type'               => $criterionData['type'],
                    'sort_order'         => $i,
                    'is_required'        => $criterionData['is_required'] ?? true,
                ]);
            }
        }

        return redirect()
            ->route('evaluation.forms.show', $form)
            ->with('success', 'Evaluation form updated successfully.');
    }

    /**
     * Delete an evaluation form.
     */
    public function destroy(EvaluationForm $form)
    {
        if (! auth()->user()->isAdmin() && $form->created_by !== auth()->id()) {
            abort(403, 'You can only delete your own forms.');
        }

        $form->delete();

        return redirect()
            ->route('evaluation.forms.index')
            ->with('success', 'Evaluation form deleted successfully.');
    }

    /**
     * Toggle the active status of a form.
     */
    public function toggleActive(EvaluationForm $form)
    {
        if (! auth()->user()->isAdmin() && $form->created_by !== auth()->id()) {
            abort(403);
        }

        $form->update(['is_active' => ! $form->is_active]);

        $status = $form->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Evaluation form {$status}.");
    }

    /**
     * Show evaluation results / responses for a specific form.
     */
    public function results(EvaluationForm $form, Request $request)
    {
        $form->load(['program', 'criteria']);

        $query = $form->responses()
            ->with(['activity', 'encoder', 'answers.criteria']);

        if ($request->filled('activity_id')) {
            $query->where('extension_activity_id', $request->activity_id);
        }

        if ($request->filled('submission_type')) {
            $query->where('submission_type', $request->submission_type);
        }

        $responses = $query->latest()->paginate(20)->withQueryString();

        // Activities for filter dropdown
        $activities = $form->program->projects()
            ->with('activities')
            ->get()
            ->pluck('activities')
            ->flatten()
            ->sortBy('title');

        // Per-criterion averages (filtered)
        $criteriaStats = [];
        foreach ($form->criteria as $criterion) {
            if ($criterion->type === 'rating') {
                $answersQuery = $criterion->answers()
                    ->whereHas('response', function ($q) use ($form, $request) {
                        $q->where('evaluation_form_id', $form->id);
                        if ($request->filled('activity_id')) {
                            $q->where('extension_activity_id', $request->activity_id);
                        }
                    })
                    ->whereNotNull('numeric_value');

                $answers = $answersQuery->get();
                $criteriaStats[$criterion->id] = [
                    'count'   => $answers->count(),
                    'average' => $answers->count() > 0 ? round($answers->avg('numeric_value'), 2) : 0,
                ];
            }
        }

        $totalResponses = $form->responses()
            ->when($request->filled('activity_id'), fn ($q) => $q->where('extension_activity_id', $request->activity_id))
            ->count();

        $overallAverage = $totalResponses > 0
            ? round($form->responses()
                ->when($request->filled('activity_id'), fn ($q) => $q->where('extension_activity_id', $request->activity_id))
                ->avg('average_score'), 2)
            : 0;

        return view('evaluation.forms.results', compact(
            'form', 'responses', 'activities', 'criteriaStats', 'totalResponses', 'overallAverage'
        ));
    }
}
