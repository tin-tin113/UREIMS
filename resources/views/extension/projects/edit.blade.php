@extends('layouts.app')
@section('title', 'Edit: ' . $project->title)
@section('page-title', 'Edit Project')

@section('content')
<nav class="text-sm text-gray-500 mb-5 flex items-center gap-1">
    <a href="{{ route('extension.projects.index') }}" class="hover:text-academic-500 transition">Projects</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('extension.projects.show', $project) }}" class="hover:text-academic-500 transition truncate max-w-xs">{{ $project->title }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-academic-heading font-medium">Edit</span>
</nav>

<form method="POST" action="{{ route('extension.projects.update', $project) }}">
    @csrf @method('PUT')

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    {{-- Project Information --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-academic-500 px-6 py-3"><h2 class="text-sm font-bold text-white">Project Information</h2></div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Project Title <span class="text-red-500">*</span></label><input type="text" name="title" value="{{ old('title', $project->title) }}" required class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Parent Program</label>
                    <select name="extension_program_id" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none">
                        <option value="">Standalone (No Program)</option>
                        @foreach($programs as $prog)<option value="{{ $prog->id }}" {{ old('extension_program_id', $project->extension_program_id) == $prog->id ? 'selected' : '' }}>{{ $prog->title }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Campus <span class="text-red-500">*</span></label>
                    <select name="campus_id" required class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none">
                        <option value="">Select Campus</option>
                        @foreach($campuses as $campus)<option value="{{ $campus->id }}" {{ old('campus_id', $project->campus_id) == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>@endforeach
                    </select>
                </div>
                @if(auth()->user()->isAdmin())
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none">
                        @foreach(['proposal'=>'Proposal','ongoing'=>'Ongoing','completed'=>'Completed'] as $k=>$v)
                            <option value="{{ $k }}" {{ old('status', $project->status) === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Persons Responsible</label><input type="text" name="persons_responsible" value="{{ old('persons_responsible', $project->persons_responsible) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Description</label><textarea name="description" rows="4" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none leading-relaxed">{{ old('description', $project->description) }}</textarea></div>
                <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Indicators / Output</label><textarea name="indicators_output" rows="3" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none leading-relaxed">{{ old('indicators_output', $project->indicators_output) }}</textarea></div>
            </div>
        </div>
    </div>

    {{-- Budget & Timeline --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Budget & Timeline</h2></div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Budget Requirement</label><input type="number" step="0.01" name="budget_requirement" value="{{ old('budget_requirement', $project->budget_requirement) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Budget Source</label><input type="text" name="budget_source" value="{{ old('budget_source', $project->budget_source) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Target Start Date</label><input type="date" name="target_start_date" value="{{ old('target_start_date', $project->target_start_date?->format('Y-m-d')) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Target End Date</label><input type="date" name="target_end_date" value="{{ old('target_end_date', $project->target_end_date?->format('Y-m-d')) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('extension.projects.show', $project) }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded border border-gray-200 transition">Cancel</a>
        <button type="submit" class="px-6 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-semibold rounded transition">Update Project</button>
    </div>
</form>
@endsection
