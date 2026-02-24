@extends('layouts.app')
@section('title', 'Beneficiaries')
@section('page-title', 'Extension Beneficiaries')

@section('content')
{{-- ===== TOP TABS ===== --}}
<div class="bg-white border-b border-gray-200 -mx-6 -mt-6 px-6 mb-6">
    <nav class="flex items-center gap-0 overflow-x-auto">
        <a href="{{ route('extension.beneficiaries.index', request()->only('search')) }}"
           class="relative px-5 py-3 text-[13px] font-medium whitespace-nowrap {{ !request('project_id') ? 'text-gray-800' : 'text-gray-500 hover:text-gray-700' }}">
            All Beneficiaries
            <span class="ml-1 px-1.5 py-0.5 text-[11px] font-semibold rounded-full {{ !request('project_id') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">{{ $totalBeneficiaries }}</span>
            @if(!request('project_id'))<span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600"></span>@endif
        </a>
        @foreach($projects->where('beneficiaries_count', '>', 0)->take(5) as $proj)
        <a href="{{ route('extension.beneficiaries.index', array_merge(request()->only('search'), ['project_id' => $proj->id])) }}"
           class="relative px-5 py-3 text-[13px] font-medium whitespace-nowrap {{ request('project_id') == $proj->id ? 'text-gray-800' : 'text-gray-500 hover:text-gray-700' }}">
            {{ Str::limit($proj->title, 20) }}
            <span class="ml-1 px-1.5 py-0.5 text-[11px] font-semibold rounded-full {{ request('project_id') == $proj->id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">{{ $proj->beneficiaries_count }}</span>
            @if(request('project_id') == $proj->id)<span class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600"></span>@endif
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

            {{-- Projects --}}
            <div>
                <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Projects</h4>
                <div class="space-y-0.5 max-h-64 overflow-y-auto">
                    <a href="{{ route('extension.beneficiaries.index', request()->only('search')) }}"
                       class="flex items-center justify-between px-3 py-1.5 text-[13px] rounded-md {{ !request('project_id') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                        <span>All Projects</span>
                    </a>
                    @foreach($projects as $proj)
                    <a href="{{ route('extension.beneficiaries.index', array_merge(request()->only('search'), ['project_id' => $proj->id])) }}"
                       class="flex items-center justify-between px-3 py-1.5 text-[13px] rounded-md {{ request('project_id') == $proj->id ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-50' }}" title="{{ $proj->title }}">
                        <span class="truncate">{{ Str::limit($proj->title, 18) }}</span>
                        <span class="flex-shrink-0 ml-2 text-[11px] text-gray-400 font-normal">{{ $proj->beneficiaries_count }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Quick stats --}}
            <div class="bg-white rounded-lg border border-gray-200 p-3 space-y-2 text-[13px]">
                <div class="flex justify-between text-gray-500"><span>Total</span><span class="font-semibold text-gray-700">{{ $totalBeneficiaries }}</span></div>
                <div class="flex justify-between text-gray-500"><span>Projects</span><span class="font-semibold text-gray-700">{{ $projects->count() }}</span></div>
                <div class="flex justify-between text-gray-500"><span>Showing</span><span class="font-semibold text-gray-700">{{ $beneficiaries->count() }}</span></div>
            </div>

            <a href="{{ route('extension.beneficiaries.create') }}" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Beneficiary
            </a>
        </div>
    </aside>

    {{-- CONTENT --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3 mb-5">
            <form method="GET" class="flex-1 flex items-center gap-3">
                @if(request('project_id'))<input type="hidden" name="project_id" value="{{ request('project_id') }}">@endif
                <div class="relative flex-1">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-[13px] bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filters
                </button>
            </form>
            @if(request()->hasAny(['search','project_id']))
                <a href="{{ route('extension.beneficiaries.index') }}" class="text-[13px] text-gray-400 hover:text-gray-600 whitespace-nowrap">Clear all</a>
            @endif
            <a href="{{ route('extension.beneficiaries.create') }}" class="lg:hidden inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-lg transition whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add
            </a>
        </div>

        {{-- LIST --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @forelse($beneficiaries as $ben)
                <div class="flex items-start gap-4 px-5 py-4 border-b border-gray-100 last:border-b-0 hover:bg-gray-50/50 transition-colors">
                    <span class="flex-shrink-0 w-9 h-7 flex items-center justify-center text-xs font-bold text-gray-400 bg-gray-100 rounded mt-0.5">{{ $ben->id }}</span>

                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-gray-800">{{ $ben->name }}</p>
                        <p class="text-[13px] text-gray-500 mt-0.5 truncate">{{ $ben->project->title ?? 'No Project' }}</p>
                        @if($ben->organization)
                            <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $ben->organization }}
                            </p>
                        @endif
                    </div>

                    <div class="hidden sm:flex items-center gap-2 flex-shrink-0 pt-0.5">
                        @if($ben->contact_no)
                            <span class="text-[11px] text-gray-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $ben->contact_no }}
                            </span>
                        @endif
                    </div>

                    <div class="flex-shrink-0 pt-0.5">
                        @if($ben->project && $ben->project->program)
                            <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-medium rounded-full border border-green-200 bg-green-50 text-green-700">{{ Str::limit($ben->project->program->title, 18) }}</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-medium rounded-full border border-orange-200 bg-orange-50 text-orange-600">Standalone</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-1.5 flex-shrink-0 pt-0.5">
                        <a href="{{ route('extension.beneficiaries.edit', $ben) }}" class="px-3 py-1.5 text-[11px] font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 border border-gray-200 rounded transition">Edit</a>
                        <a href="{{ route('extension.projects.show', $ben->project) }}" class="px-3 py-1.5 text-[11px] font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition">View</a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 border border-gray-200 rounded transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-20 py-1">
                                <a href="{{ route('extension.beneficiaries.edit', $ben) }}" class="block px-4 py-2 text-[13px] text-gray-700 hover:bg-gray-50">Edit</a>
                                <a href="{{ route('extension.projects.show', $ben->project) }}" class="block px-4 py-2 text-[13px] text-gray-700 hover:bg-gray-50">View Project</a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('extension.beneficiaries.destroy', $ben) }}" onsubmit="return confirmSubmit(event, 'Remove Beneficiary', 'Are you sure you want to remove this beneficiary?', 'danger', 'Remove')">
                                    @csrf @method('DELETE')
                                    <button class="w-full text-left px-4 py-2 text-[13px] text-red-600 hover:bg-red-50">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-16 text-center">
                    <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9.12 0A4 4 0 0012 8a4 4 0 00-4.12 6.13M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p class="text-gray-400 text-[13px]">No beneficiaries found.</p>
                    <a href="{{ route('extension.beneficiaries.create') }}" class="inline-flex items-center gap-1.5 mt-3 text-[13px] text-blue-600 hover:text-blue-700 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add your first beneficiary
                    </a>
                </div>
            @endforelse
        </div>

        @if($beneficiaries->hasPages())<div class="mt-4">{{ $beneficiaries->links() }}</div>@endif
    </div>
</div>
@endsection
