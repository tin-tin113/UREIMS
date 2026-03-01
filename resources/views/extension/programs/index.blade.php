@extends('layouts.app')
@section('title', 'Extension Programs')
@section('page-title', 'Extension Programs')

@section('content')
{{-- ===== PAGE HEADER ===== --}}
<div class="border border-gray-200 bg-white overflow-hidden mb-6">
    <div class="bg-academic-500 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-white tracking-tight">Extension Programs</h1>
                <p class="text-academic-100 text-xs mt-0.5">Manage and track all extension programs across campuses</p>
            </div>
            <a href="{{ route('proposal.wizard.start', 'program') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold rounded transition backdrop-blur">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Submit Proposal
            </a>
        </div>
    </div>

    {{-- TOOLBAR --}}
    <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[180px]">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search programs…" class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded text-sm bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
            </div>
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded text-sm bg-white focus:ring-2 focus:ring-academic-500 outline-none min-w-[140px]">
                <option value="">All Status ({{ $totalPrograms }})</option>
                @foreach(['draft' => 'Draft', 'proposal' => 'Proposal', 'ongoing' => 'Ongoing', 'completed' => 'Completed'] as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }} ({{ $statusCounts[$key] }})</option>
                @endforeach
            </select>
            <select name="campus_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded text-sm bg-white focus:ring-2 focus:ring-academic-500 outline-none min-w-[140px]">
                <option value="">All Campuses</option>
                @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-academic-500 hover:bg-academic-600 text-white text-sm font-medium rounded transition">Search</button>
            @if(request()->hasAny(['search','status','campus_id']))
                <a href="{{ route('extension.programs.index') }}" class="text-sm text-gray-400 hover:text-academic-500 transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-xs font-bold text-academic-heading uppercase tracking-wider">Program</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-academic-heading uppercase tracking-wider">Campus</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-academic-heading uppercase tracking-wider">Projects</th>
                    <th class="px-6 py-3 text-center text-xs font-bold text-academic-heading uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-academic-heading uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($programs as $program)
                    @php
                        $sc = ['draft'=>'bg-gray-100 text-gray-600','proposal'=>'bg-yellow-100 text-yellow-700','ongoing'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700'];
                        $sl = ['draft'=>'Draft','proposal'=>'Proposal','ongoing'=>'Ongoing','completed'=>'Completed'];
                        $isOverdue = $program->status !== 'draft' && $program->is_overdue;
                    @endphp
                    <tr class="hover:bg-academic-50/30 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('extension.programs.show', $program) }}" class="text-sm font-semibold text-academic-heading hover:text-academic-500 transition">{{ $program->title }}</a>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $program->proponent_name ?? 'No proponent' }}@if($program->ic_no) · IC {{ $program->ic_no }}@endif</p>
                            @if($isOverdue)
                                <p class="text-xs text-red-600 mt-1 flex items-center gap-1 font-medium">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    Overdue — {{ $program->target_end_date->format('M d, Y') }}
                                </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $program->campus->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center min-w-5 h-5 text-xs font-bold text-academic-500 bg-academic-50 rounded">{{ $program->projects->count() }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($isOverdue)
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded bg-red-100 text-red-700">Overdue</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded {{ $sc[$program->status] ?? '' }}">{{ $sl[$program->status] ?? ucfirst($program->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($program->status === 'draft')
                                    <a href="{{ route('proposal.wizard.continue-draft', ['type' => 'program', 'id' => $program->id]) }}" class="px-3 py-1.5 text-xs font-medium text-white bg-academic-500 hover:bg-academic-600 rounded transition">Continue</a>
                                    <form method="POST" action="{{ route('proposal.wizard.delete-draft', ['type' => 'program', 'id' => $program->id]) }}" onsubmit="return confirmSubmit(event, 'Delete Draft', 'This will permanently delete this draft.', 'danger', 'Delete')" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </form>
                                @else
                                    <a href="{{ route('extension.programs.show', $program) }}" class="px-3 py-1.5 text-xs font-medium text-white bg-academic-500 hover:bg-academic-600 rounded transition">View</a>
                                    <a href="{{ route('extension.programs.edit', $program) }}" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded transition">Edit</a>
                                    <form method="POST" action="{{ route('extension.programs.destroy', $program) }}" onsubmit="return confirmSubmit(event, 'Delete Program', 'This will permanently delete this program and all its projects.', 'danger', 'Delete')" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <p class="text-gray-400 text-sm">No programs found.</p>
                            <a href="{{ route('extension.programs.create') }}" class="inline-flex items-center gap-1.5 mt-3 text-sm text-academic-500 hover:text-academic-600 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Create one
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($programs->hasPages())<div class="mt-4">{{ $programs->links() }}</div>@endif
@endsection
