@extends('layouts.app')
@section('title', 'Extension Projects')
@section('page-title', 'Extension Projects')

@section('content')
{{-- ===== TOOLBAR ===== --}}
<div class="bg-white rounded-xl border border-gray-200 px-4 py-3 mb-5">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[180px]">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search projects..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        </div>
        <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none min-w-[140px]">
            <option value="">All Status ({{ $totalProjects }})</option>
            @foreach(['proposal' => 'Proposal', 'ongoing' => 'Ongoing', 'completed' => 'Completed'] as $key => $label)
                <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }} ({{ $statusCounts[$key] }})</option>
            @endforeach
        </select>
        <select name="program_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none min-w-[140px]">
            <option value="">All Programs</option>
            <option value="standalone" {{ request('program_id') === 'standalone' ? 'selected' : '' }}>Standalone</option>
            @foreach($programs as $prog)
                <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>{{ Str::limit($prog->title, 30) }}</option>
            @endforeach
        </select>
        <select name="campus_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none min-w-[140px]">
            <option value="">All Campuses</option>
            @foreach($campuses as $campus)
                <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-[13px] font-medium rounded-lg border border-gray-200 transition">Search</button>
        @if(request()->hasAny(['search','status','campus_id','program_id']))
            <a href="{{ route('extension.projects.index') }}" class="text-[13px] text-gray-400 hover:text-gray-600 transition">Clear</a>
        @endif
        <a href="{{ route('extension.projects.create') }}" class="ml-auto inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Project
        </a>
    </form>
</div>

{{-- ===== LIST ===== --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="hidden md:grid grid-cols-12 gap-4 px-5 py-2.5 bg-gray-50/80 border-b border-gray-100 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
        <div class="col-span-4">Project</div>
        <div class="col-span-2">Program</div>
        <div class="col-span-1">Campus</div>
        <div class="col-span-1 text-center">Activities</div>
        <div class="col-span-2 text-center">Status</div>
        <div class="col-span-2 text-right">Actions</div>
    </div>
    @forelse($projects as $project)
        @php
            $sc = ['proposal'=>'bg-yellow-50 text-yellow-700 border-yellow-200','ongoing'=>'bg-cyan-50 text-cyan-700 border-cyan-200','completed'=>'bg-green-50 text-green-700 border-green-200'];
            $sl = ['proposal'=>'Proposal','ongoing'=>'Ongoing','completed'=>'Completed'];
            $isOverdue = $project->is_overdue;
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-4 items-center px-5 py-3.5 border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
            <div class="col-span-4 min-w-0">
                <a href="{{ route('extension.projects.show', $project) }}" class="text-[13px] font-semibold text-gray-800 hover:text-blue-600 transition truncate block">{{ $project->title }}</a>
                <p class="text-[12px] text-gray-400 mt-0.5">{{ $project->persons_responsible ?? 'Unassigned' }}</p>
                @if($isOverdue)
                    <p class="text-[11px] text-red-500 mt-1 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Overdue — {{ $project->target_end_date->format('M d, Y') }}
                    </p>
                @endif
            </div>
            <div class="col-span-2 min-w-0">
                @if($project->program)
                    <span class="text-[12px] text-gray-500 truncate block" title="{{ $project->program->title }}">{{ Str::limit($project->program->title, 20) }}</span>
                @else
                    <span class="text-[11px] text-orange-600 font-medium">Standalone</span>
                @endif
            </div>
            <div class="col-span-1 text-[12px] text-gray-500">{{ $project->campus->name ?? '—' }}</div>
            <div class="col-span-1 text-center">
                <span class="inline-flex items-center justify-center w-7 h-5 text-[11px] font-semibold text-gray-500 bg-gray-100 rounded">{{ $project->activities->count() }}</span>
            </div>
            <div class="col-span-2 text-center">
                @if($isOverdue)
                    <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-semibold rounded-full border border-red-200 bg-red-50 text-red-700">Overdue</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-semibold rounded-full border {{ $sc[$project->status] ?? '' }}">{{ $sl[$project->status] ?? ucfirst($project->status) }}</span>
                @endif
            </div>
            <div class="col-span-2 flex items-center justify-end gap-1.5">
                <a href="{{ route('extension.projects.show', $project) }}" class="px-3 py-1.5 text-[11px] font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition">View</a>
                <a href="{{ route('extension.projects.edit', $project) }}" class="px-3 py-1.5 text-[11px] font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition">Edit</a>
                <form method="POST" action="{{ route('extension.projects.destroy', $project) }}" onsubmit="return confirmSubmit(event, 'Delete Project', 'This will permanently delete this project and all its data.', 'danger', 'Delete')" class="inline">
                    @csrf @method('DELETE')
                    <button class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="px-5 py-16 text-center">
            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-gray-400 text-[13px]">No projects found.</p>
            <a href="{{ route('extension.projects.create') }}" class="inline-flex items-center gap-1.5 mt-3 text-[13px] text-blue-600 hover:text-blue-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Create one
            </a>
        </div>
    @endforelse
</div>

@if($projects->hasPages())<div class="mt-4">{{ $projects->links() }}</div>@endif
@endsection
