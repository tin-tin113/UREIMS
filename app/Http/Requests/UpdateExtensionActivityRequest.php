<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExtensionActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Status changes go through workflow advance/bypass, not edit form
        return [
            'extension_project_id' => ['required', 'exists:extension_projects,id'],
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'persons_responsible'  => ['nullable', 'string', 'max:255'],
            'budget_requirement'   => ['nullable', 'numeric', 'min:0'],
            'indicators_output'    => ['nullable', 'string'],
            'target_date'          => ['nullable', 'date'],
            'completion_date'      => ['nullable', 'date'],
            'status'               => ['nullable', 'in:draft,proposal,ongoing,completed'],
        ];
    }
}
