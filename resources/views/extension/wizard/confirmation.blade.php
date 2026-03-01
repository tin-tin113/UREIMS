@extends('layouts.app')

@section('title', 'Submit Proposal — Confirmation')
@section('page-title', 'Submit ' . ucfirst($type) . ' Proposal')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Stepper --}}
    @include('extension.wizard._stepper', ['currentStep' => $type === 'program' ? 5 : 4, 'type' => $type])

    <div class="bg-white border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-academic-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-academic-heading">Review Your Submission</h3>
                    <p class="text-xs text-gray-500">Please review all the information below before submitting your proposal.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Requirements Status --}}
    <div class="bg-white border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h4 class="text-sm font-bold text-academic-heading flex items-center gap-2">
                <svg class="w-4 h-4 text-academic-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Submission Requirements
            </h4>
        </div>
        <div class="p-6">
        <div class="space-y-2">
            @foreach($requirements as $index => $req)
                @php $checked = in_array($index, $draft['checked_requirements'] ?? []); @endphp
                <div class="flex items-start gap-2 text-sm">
                    @if($checked)
                        <svg class="w-4 h-4 mt-0.5 text-academic-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @else
                        <svg class="w-4 h-4 mt-0.5 text-gray-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    @endif
                    <span class="{{ $checked ? 'text-gray-700' : 'text-gray-400' }}">{{ $req }}</span>
                </div>
            @endforeach
        </div>

        {{-- Data Privacy --}}
        <div class="mt-4 pt-3 border-t border-gray-100">
            <div class="flex items-center gap-2 text-sm">
                @if($draft['privacy_agreed'] ?? false)
                    <svg class="w-4 h-4 text-academic-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-gray-700 font-medium">Data Privacy Notice — Agreed</span>
                @else
                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span class="text-amber-600 font-medium">Data Privacy Notice — Not agreed (optional)</span>
                @endif
            </div>
        </div>
    </div>
    </div>

    {{-- Uploaded Files --}}
    <div class="bg-white border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h4 class="text-sm font-bold text-academic-heading flex items-center gap-2">
                <svg class="w-4 h-4 text-academic-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Uploaded Files
                <span class="text-xs font-normal text-gray-400">({{ count($draft['uploaded_files'] ?? []) }} file(s))</span>
            </h4>
        </div>
        <div class="p-6">

        @if(count($draft['uploaded_files'] ?? []) > 0)
            <div class="space-y-2">
                @foreach($draft['uploaded_files'] as $file)
                    <div class="flex items-center gap-3 px-3 py-2 bg-gray-50 rounded-lg">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="flex-1 text-sm text-gray-700 truncate">{{ $file['original_name'] }}</span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded bg-blue-50 text-academic-500">{{ $file['label'] }}</span>
                        <span class="text-xs text-gray-400">{{ number_format($file['size'] / 1024, 1) }} KB</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 italic">No files uploaded.</p>
        @endif
    </div>
    </div>

    {{-- Proposal Details --}}
    <div class="bg-white border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h4 class="text-sm font-bold text-academic-heading flex items-center gap-2">
                <svg class="w-4 h-4 text-academic-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                {{ ucfirst($type) }} Details
            </h4>
        </div>
        <div class="p-6">

        @php $details = $draft['details'] ?? []; @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
            @if($type === 'program')
                @php
                    $fields = [
                        'Title' => $details['title'] ?? '',
                        'I.C. Number' => $details['ic_no'] ?? '',
                        'Campus' => isset($details['campus_id']) ? ($campuses[$details['campus_id']] ?? 'N/A') : '',
                        'Proponent' => $details['proponent_name'] ?? '',
                        'Division/Unit' => $details['division_unit'] ?? '',
                        'Contact' => $details['contact_no'] ?? '',
                        'Location' => $details['program_location'] ?? '',
                        'Program Leader' => $details['program_leader'] ?? '',
                        'Start Date' => $details['target_start_date'] ?? '',
                        'End Date' => $details['target_end_date'] ?? '',
                        'CHMSU GAA' => isset($details['funding_chmsu_gaa']) ? '₱' . number_format($details['funding_chmsu_gaa'], 2) : '',
                        'CHMSU STF' => isset($details['funding_chmsu_stf']) ? '₱' . number_format($details['funding_chmsu_stf'], 2) : '',
                        'Collaborator' => isset($details['funding_collaborator']) ? '₱' . number_format($details['funding_collaborator'], 2) : '',
                        'Total Funding' => '₱' . number_format(($details['funding_chmsu_gaa'] ?? 0) + ($details['funding_chmsu_stf'] ?? 0) + ($details['funding_collaborator'] ?? 0), 2),
                    ];
                @endphp

                @foreach($fields as $label => $value)
                    <div class="{{ $label === 'Title' ? 'md:col-span-2' : '' }}">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $label }}</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach

                {{-- Narrative fields --}}
                @foreach(['rationale' => 'Rationale', 'general_objective' => 'General Objective', 'specific_objectives' => 'Specific Objectives', 'methodology' => 'Methodology'] as $key => $label)
                    @if(!empty($details[$key]))
                        <div class="md:col-span-2 mt-2">
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $label }}</dt>
                            <dd class="text-sm text-gray-800 mt-0.5 whitespace-pre-line">{{ $details[$key] }}</dd>
                        </div>
                    @endif
                @endforeach

            @else
                @php
                    $fields = [
                        'Title' => $details['title'] ?? '',
                        'Parent Program' => isset($details['extension_program_id']) ? ($programs[$details['extension_program_id']] ?? 'Standalone') : 'Standalone',
                        'Campus' => isset($details['campus_id']) ? ($campuses[$details['campus_id']] ?? 'N/A') : '',
                        'Persons Responsible' => $details['persons_responsible'] ?? '',
                        'Budget' => isset($details['budget_requirement']) ? '₱' . number_format($details['budget_requirement'], 2) : '',
                        'Budget Source' => $details['budget_source'] ?? '',
                        'Start Date' => $details['target_start_date'] ?? '',
                        'End Date' => $details['target_end_date'] ?? '',
                    ];
                @endphp

                @foreach($fields as $label => $value)
                    <div class="{{ $label === 'Title' ? 'md:col-span-2' : '' }}">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $label }}</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach

                @if(!empty($details['description']))
                    <div class="md:col-span-2 mt-2">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Description</dt>
                        <dd class="text-sm text-gray-800 mt-0.5 whitespace-pre-line">{{ $details['description'] }}</dd>
                    </div>
                @endif
                @if(!empty($details['indicators_output']))
                    <div class="md:col-span-2 mt-2">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Indicators / Output</dt>
                        <dd class="text-sm text-gray-800 mt-0.5 whitespace-pre-line">{{ $details['indicators_output'] }}</dd>
                    </div>
                @endif
            @endif
        </div>
    </div>
    </div>

    {{-- Reviewer Comments --}}
    @if(!empty($draft['comments']))
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="text-sm font-bold text-academic-heading">Comments for Reviewer</h4>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $draft['comments'] }}</p>
            </div>
        </div>
    @endif

    {{-- Draft Projects (program only) --}}
    @if($type === 'program' && !empty($draft['projects']))
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="text-sm font-bold text-academic-heading flex items-center gap-2">
                    <svg class="w-4 h-4 text-academic-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Projects Under This Program
                    <span class="text-xs font-normal text-gray-400">({{ count($draft['projects']) }} project(s))</span>
                </h4>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                @foreach($draft['projects'] as $i => $proj)
                    @if(!empty($proj['title']))
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">{{ $proj['title'] }}</p>
                                    @if(!empty($proj['description']))
                                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($proj['description'], 120) }}</p>
                                    @endif
                                </div>
                                @if(!empty($proj['budget_requirement']))
                                    <span class="text-xs font-medium text-gray-600 bg-gray-200 px-2 py-0.5 rounded">₱{{ number_format($proj['budget_requirement'], 2) }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                @if(!empty($proj['persons_responsible']))
                                    <span>{{ $proj['persons_responsible'] }}</span>
                                @endif
                                @if(!empty($proj['target_start_date']))
                                    <span>{{ $proj['target_start_date'] }} — {{ $proj['target_end_date'] ?? '?' }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            </div>
        </div>
    @endif

    {{-- Actions --}}
    <div class="flex items-center justify-between py-2">
        <a href="{{ $type === 'program' ? route('proposal.wizard.projects', $type) : route('proposal.wizard.details', $type) }}"
           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('proposal.wizard.save-draft', $type) }}">
                @csrf
                <input type="hidden" name="_current_step" value="{{ $type === 'program' ? 5 : 4 }}">
                <button type="submit"
                        class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    Save Draft
                </button>
            </form>
            <form method="POST" action="{{ route('proposal.wizard.submit', $type) }}">
                @csrf
                <button type="submit"
                        class="px-6 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-medium rounded-lg shadow-sm transition inline-flex items-center gap-2">
                    Submit Proposal
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection




