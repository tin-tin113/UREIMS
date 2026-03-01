<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExtensionProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'extension_program_id' => ['nullable', 'exists:extension_programs,id'],
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'persons_responsible'  => ['nullable', 'string', 'max:255'],
            'budget_requirement'   => ['nullable', 'numeric', 'min:0'],
            'budget_source'        => ['nullable', 'string', 'max:255'],
            'indicators_output'    => ['nullable', 'string'],
            'target_start_date'    => ['nullable', 'date'],
            'target_end_date'      => ['nullable', 'date', 'after_or_equal:target_start_date'],
            'status'               => ['required', 'in:draft,proposal,ongoing,completed'],
            'campus_id'            => ['required', 'exists:campuses,id'],
        ];
    }
}
