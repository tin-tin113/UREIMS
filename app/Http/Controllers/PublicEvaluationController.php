<?php

namespace App\Http\Controllers;

use App\Models\EvaluationForm;
use App\Models\EvaluationResponse;
use App\Models\EvaluationAnswer;
use Illuminate\Http\Request;

class PublicEvaluationController extends Controller
{
    /**
     * Show the public evaluation form (no auth required).
     * Participant opens via link / QR and selects an activity to evaluate.
     */
    public function show(string $token)
    {
        $form = EvaluationForm::where('access_token', $token)
            ->where('is_active', true)
            ->with(['program.projects.activities', 'criteria'])
            ->firstOrFail();

        // Gather all activities under this program
        $activities = $form->program->projects
            ->pluck('activities')
            ->flatten()
            ->filter(fn ($a) => in_array($a->status, ['ongoing', 'completed']))
            ->sortBy('title')
            ->values();

        return view('evaluation.public.form', compact('form', 'activities'));
    }

    /**
     * Handle public evaluation submission.
     * System: converts answers to numeric, computes total & average, saves response, displays thank you.
     */
    public function submit(Request $request, string $token)
    {
        $form = EvaluationForm::where('access_token', $token)
            ->where('is_active', true)
            ->with('criteria')
            ->firstOrFail();

        $data = $request->validate([
            'extension_activity_id'    => ['required', 'exists:extension_activities,id'],
            'respondent_name'          => ['nullable', 'string', 'max:255'],
            'respondent_email'         => ['nullable', 'email', 'max:255'],
            'respondent_contact'       => ['nullable', 'string', 'max:20'],
            'respondent_organization'  => ['nullable', 'string', 'max:255'],
            'respondent_gender'        => ['nullable', 'in:male,female'],
            'answers'                  => ['required', 'array'],
            'answers.*'                => ['nullable'],
        ]);

        // Server-side guard: only accept evaluations for ongoing/completed activities
        $activity = \App\Models\ExtensionActivity::findOrFail($data['extension_activity_id']);
        if (! in_array($activity->status, ['ongoing', 'completed'])) {
            return back()->with('error', 'This activity is not yet open for evaluation.');
        }

        // Verify the activity belongs to a project under this form's program
        $programProjectIds = $form->program->projects()->pluck('id')->toArray();
        if (! in_array($activity->extension_project_id, $programProjectIds)) {
            return back()->with('error', 'This activity does not belong to the evaluated program.');
        }

        // Create the response record
        $response = EvaluationResponse::create([
            'evaluation_form_id'       => $form->id,
            'extension_activity_id'    => $data['extension_activity_id'],
            'respondent_name'          => $data['respondent_name'] ?? null,
            'respondent_email'         => $data['respondent_email'] ?? null,
            'respondent_contact'       => $data['respondent_contact'] ?? null,
            'respondent_organization'  => $data['respondent_organization'] ?? null,
            'respondent_gender'        => $data['respondent_gender'] ?? null,
            'submission_type'          => 'online',
        ]);

        // Save individual answers
        foreach ($form->criteria as $criterion) {
            $rawValue = $data['answers'][$criterion->id] ?? null;

            EvaluationAnswer::create([
                'evaluation_response_id' => $response->id,
                'evaluation_criteria_id' => $criterion->id,
                'numeric_value'          => $criterion->type === 'rating' && is_numeric($rawValue) ? (int) $rawValue : null,
                'text_value'             => $criterion->type === 'text' ? $rawValue : null,
            ]);
        }

        // Compute scores
        $response->recomputeScores();

        return redirect()->route('evaluation.public.thanks', $token);
    }

    /**
     * Thank you page after successful submission.
     */
    public function thanks(string $token)
    {
        $form = EvaluationForm::where('access_token', $token)->firstOrFail();

        return view('evaluation.public.thanks', compact('form'));
    }
}
