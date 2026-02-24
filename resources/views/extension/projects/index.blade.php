@extends('layouts.app')
@section('title', 'Extension Projects')
@section('page-title', 'Extension Projects')

@section('content')
{{-- ===== TOP TABS ===== --}}
<div class="bg-white border-b border-gray-200 -mx-6 -mt-6 px-6 mb-6">
    <nav class="flex items-center gap-0 overflow-x-auto">
        <a href="{{ route('extension.projects.index', request()->except('status')) }}"
           class="relative px-5 py-3 text-[13px] font-medium whitespace-nowrap {{ !request('status') ? 'text-gray-800' : 'text-gray-500 hover:text-gray-700' }}">
            All Projects
            <span class="ml-1 px-1.5 py-0.5 text-[11px] font-semibold rounded-full {{ !request('status') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">{{ $totalProjects }}</span>
            @if(!request('status'))<span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600"></span>@endif
        </a>
        @foreach(['proposal' => 'Proposal', 'under_review' => 'Under Review', 'approved' => 'Approved', 'ongoing' => 'Ongoing', 'completed' => 'Completed'] as $key => $label)
        <a href="{{ route('extension.projects.index', array_merge(request()->except('status'), ['status' => $key])) }}"
           class="relative px-5 py-3 text-[13px] font-medium whitespace-nowrap {{ request('status') === $key ? 'text-gray-800' : 'text-gray-500 hover:text-gray-700' }}">
            {{ $label }}
            <span class="ml-1 px-1.5 py-0.5 text-[11px] font-semibold rounded-full {{ request('status') === $key ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">{{ $statusCounts[$key] }}</span>
            @if(request('status') === $key)<span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600"></span>@endif
        </a>
        @endforeach
    </nav>
</div>

<div class="flex gap-6">

    {{-- LEFT SIDEBAR --}}
    <aside class="w-52 flex-shrink-0 hidden lg:block">
        <div class="sticky top-20 space-y-5">
            <h3 class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-wider">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filters
            </h3>

            {{-- Program --}}
            <div>
                <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Program</h4>
                <div class="space-y-0.5 max-h-48 overflow-y-auto">
                    <a href="{{ route('extension.projects.index', request()->except('program_id')) }}"
                       class="block px-3 py-1.5 text-[13px] rounded-md {{ !request('program_id') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">All Programs</a>
                    <a href="{{ route('extension.projects.index', array_merge(request()->all(), ['program_id' => 'standalone'])) }}"
                       class="block px-3 py-1.5 text-[13px] rounded-md {{ request('program_id') === 'standalone' ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">Standalone</a>
                    @foreach($programs as $prog)
                    <a href="{{ route('extension.projects.index', array_merge(request()->all(), ['program_id' => $prog->id])) }}"
                       class="block px-3 py-1.5 text-[13px] rounded-md truncate {{ request('program_id') == $prog->id ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}" title="{{ $prog->title }}">{{ Str::limit($prog->title, 22) }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Campus --}}
            <div>
                <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Campus</h4>
                <div class="space-y-0.5 max-h-48 overflow-y-auto">
                    <a href="{{ route('extension.projects.index', request()->except('campus_id')) }}"
                       class="block px-3 py-1.5 text-[13px] rounded-md {{ !request('campus_id') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">All Campuses</a>
                    @foreach($campuses as $campus)
                    <a href="{{ route('extension.projects.index', array_merge(request()->all(), ['campus_id' => $campus->id])) }}"
                       class="block px-3 py-1.5 text-[13px] rounded-md truncate {{ request('campus_id') == $campus->id ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">{{ $campus->name }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Quick stats --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 space-y-2 text-[13px]">
                <div class="flex justify-between text-gray-500"><span>Total</span><span class="font-semibold text-gray-700">{{ $totalProjects }}</span></div>
                <div class="flex justify-between text-gray-500"><span>Showing</span><span class="font-semibold text-gray-700">{{ $projects->count() }}</span></div>
            </div>

            <a href="{{ route('extension.projects.create') }}" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Project
            </a>
        </div>
    </aside>

    {{-- CONTENT --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3 mb-5">
            <form method="GET" class="flex-1 flex items-center gap-3">
                @foreach(request()->except('search') as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                <div class="relative flex-1">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-[13px] bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filters
                </button>
            </form>
            @if(request()->hasAny(['search','status','campus_id','program_id']))
                <a href="{{ route('extension.projects.index') }}" class="text-[13px] text-gray-400 hover:text-gray-600 whitespace-nowrap">Clear all</a>
            @endif
            <a href="{{ route('extension.projects.create') }}" class="lg:hidden inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-lg transition whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> New
            </a>
        </div>

        {{-- LIST --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @forelse($projects as $project)
                @php
                    $statusColors = ['proposal'=>'border-yellow-300 bg-yellow-50 text-yellow-700','under_review'=>'border-purple-300 bg-purple-50 text-purple-700','approved'=>'border-blue-300 bg-blue-50 text-blue-700','ongoing'=>'border-cyan-300 bg-cyan-50 text-cyan-700','completed'=>'border-green-300 bg-green-50 text-green-700'];
                    $isOverdue = $project->is_overdue;
                @endphp
                <div class="flex items-start gap-4 px-5 py-4 border-b border-gray-100 last:border-b-0 hover:bg-gray-50/50 transition-colors">
                    <span class="flex-shrink-0 w-9 h-7 flex items-center justify-center text-xs font-bold text-gray-400 bg-gray-100 rounded mt-0.5">{{ $project->id }}</span>

                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-gray-800">{{ $project->persons_responsible ?? 'Unassigned' }}</p>
                        <p class="text-[13px] text-gray-500 mt-0.5 truncate">{{ $project->title }}</p>
                        @if($isOverdue)
                            <p class="flex items-center gap-1 text-[11px] text-red-500 mt-1.5">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                Overdue — target end {{ $project->target_end_date->format('M d, Y') }}
                            </p>
                        @endif
                    </div>

                    <div class="hidden sm:flex items-center gap-3 flex-shrink-0 pt-0.5">
                        <span class="text-[11px] text-gray-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9.12 0A4 4 0 0012 8a4 4 0 00-4.12 6.13"/></svg>
                            {{ $project->activities->count() }}
                        </span>
                        @if($project->campus)
                            <span class="text-[11px] text-gray-400">{{ $project->campus->name }}</span>
                        @endif
                    </div>

                    <div class="flex-shrink-0 pt-0.5">
                        @if($project->program)
                            <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-medium rounded-full border border-gray-200 bg-gray-50 text-gray-600" title="{{ $project->program->title }}">{{ Str::limit($project->program->title, 16) }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-medium rounded-full border border-orange-200 bg-orange-50 text-orange-600">Standalone</span>
                        @endif
                    </div>

                    <div class="flex-shrink-0 pt-0.5">
                        @if($isOverdue)
                            <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-semibold rounded-full border border-red-300 bg-red-50 text-red-700">○ Overdue</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-semibold rounded-full border {{ $statusColors[$project->status] ?? '' }}">○ {{ str_replace('_', ' ', ucfirst($project->status)) }}</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-1.5 flex-shrink-0 pt-0.5">
                        <a href="{{ route('extension.projects.show', $project) }}" class="px-3 py-1.5 text-[11px] font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition">View</a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 border border-gray-200 rounded transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-20 py-1">
                                <a href="{{ route('extension.projects.show', $project) }}" class="block px-4 py-2 text-[13px] text-gray-700 hover:bg-gray-50">View Details</a>
                                <a href="{{ route('extension.projects.edit', $project) }}" class="block px-4 py-2 text-[13px] text-gray-700 hover:bg-gray-50">Edit</a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('extension.projects.destroy', $project) }}" onsubmit="return confirmSubmit(event, 'Delete Project', 'This will permanently delete this project and all its data.', 'danger', 'Delete')">
                                    @csrf @method('DELETE')
                                    <button class="w-full text-left px-4 py-2 text-[13px] text-red-600 hover:bg-red-50">Delete</button>
                                </form>
                            </div>
                        </div>
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
    </div>
</div>
@endsection
