@extends('layouts.app')
@section('title', 'Edit Project')
@section('page-title', 'Edit Extension Project')
@section('content')
<div class="max-w-3xl">
    <nav class="text-sm text-gray-500 mb-6 flex items-center gap-1">
        <a href="{{ route('extension.projects.index') }}" class="hover:text-green-700">Projects</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('extension.projects.show', $project) }}" class="hover:text-green-700 truncate max-w-[200px]">{{ $project->title }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-700 font-medium">Edit</span>
    </nav>

    @if($errors->any())
        <div class="mb-6 px-4 py-3 rounded-lg bg-red-50 border border-red-200">
            <ul class="list-disc list-inside text-sm text-red-600">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('extension.projects.update', $project) }}" onsubmit="return confirmSubmit(event, 'Update Project', 'Are you sure you want to save these changes?', 'info', 'Save Changes')">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Project Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Title *</label><input type="text" name="title" value="{{ old('title', $project->title) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent Program</label>
                    <select name="extension_program_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                        <option value="">— Standalone —</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ old('extension_program_id', $project->extension_program_id)==$prog->id?'selected':'' }}>{{ $prog->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campus *</label>
                    <select name="campus_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ old('campus_id',$project->campus_id)==$campus->id?'selected':'' }}>{{ $campus->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                        @foreach(['proposal','ongoing','completed'] as $s)
                            <option value="{{ $s }}" {{ old('status',$project->status)===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Persons Responsible</label><input type="text" name="persons_responsible" value="{{ old('persons_responsible', $project->persons_responsible) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Description</label><textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">{{ old('description', $project->description) }}</textarea></div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Budget & Timeline</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Budget (₱)</label><input type="number" step="0.01" name="budget_requirement" value="{{ old('budget_requirement',$project->budget_requirement) }}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Budget Source</label><input type="text" name="budget_source" value="{{ old('budget_source',$project->budget_source) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label><input type="date" name="target_start_date" value="{{ old('target_start_date',$project->target_start_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">End Date</label><input type="date" name="target_end_date" value="{{ old('target_end_date',$project->target_end_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"></div>
                <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Indicators / Output</label><textarea name="indicators_output" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">{{ old('indicators_output',$project->indicators_output) }}</textarea></div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold rounded-lg transition shadow-sm">Update Project</button>
            <a href="{{ route('extension.projects.show', $project) }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
