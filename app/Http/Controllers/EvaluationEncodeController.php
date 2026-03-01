<?php

namespace App\Http\Controllers;

use App\Models\EvaluationForm;
use App\Models\EvaluationResponse;
use App\Models\EvaluationAnswer;
use App\Models\ExtensionProgram;
use Illuminate\Http\Request;

class EvaluationEncodeController extends Controller
{
    /**
     * Show the encoding form — staff selects a form and activity, then enters respondent answers.
     */
    public function create(Request $request)
    {
        $programs = auth()->user()->isAdmin()
            ? ExtensionProgram::has('evaluationForms')->orderBy('title')->get(['id', 'title'])
            : ExtensionProgram::where('created_by', auth()->id())
                ->has('evaluationForms')
                ->orderBy('title')
                ->get(['id', 'title']);

        $selectedForm = null;
        $activities   = collect();

        if ($request->filled('form_id')) {
            $selectedForm = EvaluationForm::with(['criteria', 'program.projects.activities'])
                ->findOrFail($request->form_id);

            $activities = $selectedForm->program->projects
                ->pluck('activities')
                ->flatten()
                ->sortBy('title')
                ->values();
        }

        // Get all forms for the dropdown
        $forms = auth()->user()->isAdmin()
            ? EvaluationForm::with('program')->where('is_active', true)->orderBy('title')->get()
            : EvaluationForm::with('program')
                ->where('is_active', true)
                ->whereHas('program', fn ($q) => $q->where('created_by', auth()->id()))
                ->orderBy('title')
                ->get();

        return view('evaluation.encode.create', compact('programs', 'forms', 'selectedForm', 'activities'));
    }

    /**
     * Store an encoded (hardcopy) evaluation response.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'evaluation_form_id'       => ['required', 'exists:evaluation_forms,id'],
            'extension_activity_id'    => ['required', 'exists:extension_activities,id'],
            'respondent_name'          => ['nullable', 'string', 'max:255'],
            'respondent_email'         => ['nullable', 'email', 'max:255'],
            'respondent_contact'       => ['nullable', 'string', 'max:20'],
            'respondent_organization'  => ['nullable', 'string', 'max:255'],
            'respondent_gender'        => ['nullable', 'in:male,female'],
            'answers'                  => ['required', 'array'],
            'answers.*'                => ['nullable'],
        ]);

        $form = EvaluationForm::with('criteria')->findOrFail($data['evaluation_form_id']);

        // Permission check
        if (! auth()->user()->isAdmin()) {
            $program = $form->program;
            if ($program->created_by !== auth()->id()) {
                abort(403, 'You can only encode responses for your own programs.');
            }
        }

        $response = EvaluationResponse::create([
            'evaluation_form_id'       => $form->id,
            'extension_activity_id'    => $data['extension_activity_id'],
            'respondent_name'          => $data['respondent_name'] ?? null,
            'respondent_email'         => $data['respondent_email'] ?? null,
            'respondent_contact'       => $data['respondent_contact'] ?? null,
            'respondent_organization'  => $data['respondent_organization'] ?? null,
            'respondent_gender'        => $data['respondent_gender'] ?? null,
            'submission_type'          => 'encoded',
            'encoded_by'              => auth()->id(),
        ]);

        foreach ($form->criteria as $criterion) {
            $rawValue = $data['answers'][$criterion->id] ?? null;

            EvaluationAnswer::create([
                'evaluation_response_id' => $response->id,
                'evaluation_criteria_id' => $criterion->id,
                'numeric_value'          => $criterion->type === 'rating' && is_numeric($rawValue) ? (int) $rawValue : null,
                'text_value'             => $criterion->type === 'text' ? $rawValue : null,
            ]);
        }

        $response->recomputeScores();

        return redirect()
            ->route('evaluation.encode.create', ['form_id' => $form->id])
            ->with('success', 'Hardcopy evaluation encoded successfully. You can encode another one.');
    }
}
