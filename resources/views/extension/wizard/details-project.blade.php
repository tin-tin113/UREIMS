@extends('layouts.app')

@section('title', 'Submit Proposal — Project Details')
@section('page-title', 'Submit Project Proposal')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Stepper --}}
    @include('extension.wizard._stepper', ['currentStep' => 3, 'type' => $type])

    @if($errors->any())
        <div class="mb-6 px-4 py-3 rounded-lg bg-red-50 border border-red-200">
            <p class="font-medium text-red-700 text-sm mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('proposal.wizard.save-details', $type) }}">
        @csrf
        <input type="hidden" name="_current_step" value="3">

        {{-- Section: Project Information --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Project Information</h3>
                <p class="text-xs text-gray-400 mt-0.5">Fields marked with <span class="text-red-500">*</span> are recommended but not required to proceed.</p>
            </div>
            <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ $draft['details']['title'] ?? old('title') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition"
                           placeholder="Enter the full project title">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent Program</label>
                    <select name="extension_program_id"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                        <option value="">— Standalone Project —</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ ($draft['details']['extension_program_id'] ?? old('extension_program_id')) == $prog->id ? 'selected' : '' }}>{{ $prog->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campus <span class="text-red-500">*</span></label>
                    <select name="campus_id"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                        <option value="">Select Campus</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ ($draft['details']['campus_id'] ?? old('campus_id')) == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Persons Responsible</label>
                    <input type="text" name="persons_responsible" value="{{ $draft['details']['persons_responsible'] ?? old('persons_responsible') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition resize-y">{{ $draft['details']['description'] ?? old('description') }}</textarea>
                </div>
            </div>
            </div>
        </div>

        {{-- Section: Budget & Timeline --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Budget & Timeline</h3>
            </div>
            <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Budget Requirement (₱)</label>
                    <input type="number" step="0.01" name="budget_requirement"
                           value="{{ $draft['details']['budget_requirement'] ?? old('budget_requirement', 0) }}" min="0"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Budget Source</label>
                    <input type="text" name="budget_source" value="{{ $draft['details']['budget_source'] ?? old('budget_source') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Start Date</label>
                    <input type="date" name="target_start_date" value="{{ $draft['details']['target_start_date'] ?? old('target_start_date') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target End Date</label>
                    <input type="date" name="target_end_date" value="{{ $draft['details']['target_end_date'] ?? old('target_end_date') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Indicators / Output</label>
                    <textarea name="indicators_output" rows="2"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition resize-y">{{ $draft['details']['indicators_output'] ?? old('indicators_output') }}</textarea>
                </div>
            </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between py-2">
            <a href="{{ route('proposal.wizard.upload', $type) }}"
               class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <div class="flex items-center gap-3">
                <button type="submit" formaction="{{ route('proposal.wizard.save-draft', $type) }}"
                        class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    Save Draft
                </button>
                <button type="submit"
                        class="px-6 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-medium rounded-lg shadow-sm transition inline-flex items-center gap-2">
                    Save and Continue
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection



