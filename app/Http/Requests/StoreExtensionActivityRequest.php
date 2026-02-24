<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExtensionActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'extension_project_id' => ['required', 'exists:extension_projects,id'],
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'persons_responsible'  => ['nullable', 'string', 'max:255'],
            'budget_requirement'   => ['nullable', 'numeric', 'min:0'],
            'indicators_output'    => ['nullable', 'string'],
            'target_date'          => ['nullable', 'date'],
            'completion_date'      => ['nullable', 'date'],
            'status'               => ['required', 'in:proposal,ongoing,completed'],
        ];
    }
}
