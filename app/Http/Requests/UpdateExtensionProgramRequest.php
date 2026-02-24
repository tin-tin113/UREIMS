<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExtensionProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ic_no'                     => ['nullable', 'string', 'max:50', Rule::unique('extension_programs', 'ic_no')->ignore($this->route('program'))],
            'title'                     => ['required', 'string', 'max:255'],
            'proponent_name'            => ['required', 'string', 'max:255'],
            'division_unit'             => ['nullable', 'string', 'max:255'],
            'proponent_address'         => ['nullable', 'string', 'max:500'],
            'contact_no'               => ['nullable', 'string', 'max:20'],
            'cooperating_entities'      => ['nullable', 'string'],
            'cooperating_entity_address'=> ['nullable', 'string', 'max:500'],
            'program_location'          => ['nullable', 'string', 'max:500'],
            'beneficiary_class'         => ['nullable', 'string'],
            'target_recipients'         => ['nullable', 'integer', 'min:0'],
            'funding_chmsu_gaa'         => ['nullable', 'numeric', 'min:0'],
            'funding_chmsu_gaa_note'    => ['nullable', 'string', 'max:255'],
            'funding_chmsu_stf'         => ['nullable', 'numeric', 'min:0'],
            'funding_collaborator'      => ['nullable', 'numeric', 'min:0'],
            'funding_collaborator_note' => ['nullable', 'string', 'max:255'],
            'funding_total'             => ['nullable', 'numeric', 'min:0'],
            'target_start_date'         => ['nullable', 'date'],
            'target_end_date'           => ['nullable', 'date', 'after_or_equal:target_start_date'],
            'program_leader'            => ['nullable', 'string', 'max:255'],
            'rationale'                 => ['nullable', 'string'],
            'conceptual_framework'      => ['nullable', 'string'],
            'general_objective'         => ['nullable', 'string'],
            'specific_objectives'       => ['nullable', 'string'],
            'methodology'               => ['nullable', 'string'],
            'status'                    => ['required', 'in:proposal,under_review,approved,ongoing,completed'],
            'campus_id'                 => ['required', 'exists:campuses,id'],

            'members'                   => ['nullable', 'array'],
            'members.*.name'            => ['required_with:members', 'string', 'max:255'],
            'members.*.responsibility'  => ['nullable', 'string', 'max:255'],

            // Inline projects
            'projects'                       => ['nullable', 'array'],
            'projects.*.id'                  => ['nullable', 'integer', 'exists:extension_projects,id'],
            'projects.*.title'               => ['required', 'string', 'max:255'],
            'projects.*.description'         => ['nullable', 'string'],
            'projects.*.persons_responsible' => ['nullable', 'string', 'max:255'],
            'projects.*.budget_requirement'  => ['nullable', 'numeric', 'min:0'],
            'projects.*.budget_source'       => ['nullable', 'string', 'max:255'],
            'projects.*.target_start_date'   => ['nullable', 'date'],
            'projects.*.target_end_date'     => ['nullable', 'date'],
            'projects.*.status'              => ['nullable', 'in:proposal,under_review,approved,ongoing,completed'],
        ];
    }
}
