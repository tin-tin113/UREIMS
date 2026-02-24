@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- ===== SUMMARY CARDS ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('extension.programs.index') }}" class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </span>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-800">{{ $totalPrograms }}</p>
        <p class="text-[13px] text-gray-500 mt-0.5">Programs</p>
        <div class="flex items-center gap-1.5 mt-3 text-[11px] flex-wrap">
            <span class="px-1.5 py-0.5 rounded bg-yellow-50 text-yellow-700">{{ $programsByStatus['proposal'] }} Proposal</span>
            <span class="px-1.5 py-0.5 rounded bg-purple-50 text-purple-700">{{ $programsByStatus['under_review'] }} Review</span>
            <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700">{{ $programsByStatus['approved'] }} Approved</span>
            <span class="px-1.5 py-0.5 rounded bg-cyan-50 text-cyan-700">{{ $programsByStatus['ongoing'] }} Ongoing</span>
            <span class="px-1.5 py-0.5 rounded bg-green-50 text-green-700">{{ $programsByStatus['completed'] }} Done</span>
        </div>
    </a>

    <a href="{{ route('extension.projects.index') }}" class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-emerald-300 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="w-10 h-10 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </span>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-emerald-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-800">{{ $totalProjects }}</p>
        <p class="text-[13px] text-gray-500 mt-0.5">Projects</p>
        <div class="flex items-center gap-1.5 mt-3 text-[11px] flex-wrap">
            <span class="px-1.5 py-0.5 rounded bg-yellow-50 text-yellow-700">{{ $projectsByStatus['proposal'] }} Proposal</span>
            <span class="px-1.5 py-0.5 rounded bg-purple-50 text-purple-700">{{ $projectsByStatus['under_review'] }} Review</span>
            <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700">{{ $projectsByStatus['approved'] }} Approved</span>
            <span class="px-1.5 py-0.5 rounded bg-cyan-50 text-cyan-700">{{ $projectsByStatus['ongoing'] }} Ongoing</span>
            <span class="px-1.5 py-0.5 rounded bg-green-50 text-green-700">{{ $projectsByStatus['completed'] }} Done</span>
        </div>
    </a>

    <a href="{{ route('extension.activities.index') }}" class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-violet-300 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="w-10 h-10 flex items-center justify-center rounded-lg bg-violet-50 text-violet-600 group-hover:bg-violet-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </span>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-violet-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-800">{{ $totalActivities }}</p>
        <p class="text-[13px] text-gray-500 mt-0.5">Activities</p>
        <div class="flex items-center gap-1.5 mt-3 text-[11px] flex-wrap">
            <span class="px-1.5 py-0.5 rounded bg-yellow-50 text-yellow-700">{{ $activitiesByStatus['proposal'] }} Proposal</span>
            <span class="px-1.5 py-0.5 rounded bg-purple-50 text-purple-700">{{ $activitiesByStatus['under_review'] }} Review</span>
            <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700">{{ $activitiesByStatus['approved'] }} Approved</span>
            <span class="px-1.5 py-0.5 rounded bg-cyan-50 text-cyan-700">{{ $activitiesByStatus['ongoing'] }} Ongoing</span>
            <span class="px-1.5 py-0.5 rounded bg-green-50 text-green-700">{{ $activitiesByStatus['completed'] }} Done</span>
        </div>
    </a>

    <a href="{{ route('extension.beneficiaries.index') }}" class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-amber-300 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="w-10 h-10 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9.12 0A4 4 0 0012 8a4 4 0 00-4.12 6.13M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-amber-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-800">{{ $totalBeneficiaries }}</p>
        <p class="text-[13px] text-gray-500 mt-0.5">Beneficiaries</p>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ===== LEFT COLUMN — OVERDUE & RECENT ===== --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Overdue Activities --}}
        @if($overdueActivities->count())
        <div class="bg-white rounded-xl border border-red-200">
            <div class="flex items-center justify-between px-5 py-3 border-b border-red-100">
                <h3 class="text-[13px] font-bold text-red-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    Overdue Activities
                </h3>
                <span class="px-2 py-0.5 text-[11px] font-semibold bg-red-100 text-red-700 rounded-full">{{ $overdueActivities->count() }}</span>
            </div>
            @foreach($overdueActivities as $act)
            <div class="flex items-start gap-4 px-5 py-3 border-b border-gray-100 last:border-b-0 hover:bg-red-50/30 transition-colors">
                <span class="flex-shrink-0 w-9 h-7 flex items-center justify-center text-xs font-bold text-red-400 bg-red-50 rounded mt-0.5">{{ $act->id }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-gray-800 truncate">{{ $act->title }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $act->project->title ?? 'No Project' }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 pt-0.5">
                    <span class="text-[11px] text-red-500 font-medium whitespace-nowrap">Due {{ \Carbon\Carbon::parse($act->target_date)->diffForHumans() }}</span>
                    <a href="{{ route('extension.activities.edit', $act) }}" class="px-3 py-1.5 text-[11px] font-medium text-white bg-red-500 hover:bg-red-600 rounded transition">View</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Overdue Projects --}}
        @if($overdueProjects->count())
        <div class="bg-white rounded-xl border border-orange-200">
            <div class="flex items-center justify-between px-5 py-3 border-b border-orange-100">
                <h3 class="text-[13px] font-bold text-orange-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Overdue Projects
                </h3>
                <span class="px-2 py-0.5 text-[11px] font-semibold bg-orange-100 text-orange-700 rounded-full">{{ $overdueProjects->count() }}</span>
            </div>
            @foreach($overdueProjects as $proj)
            <div class="flex items-start gap-4 px-5 py-3 border-b border-gray-100 last:border-b-0 hover:bg-orange-50/30 transition-colors">
                <span class="flex-shrink-0 w-9 h-7 flex items-center justify-center text-xs font-bold text-orange-400 bg-orange-50 rounded mt-0.5">{{ $proj->id }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-gray-800 truncate">{{ $proj->title }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $proj->persons_responsible ?? '' }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 pt-0.5">
                    <span class="text-[11px] text-orange-500 font-medium whitespace-nowrap">Due {{ \Carbon\Carbon::parse($proj->target_end_date)->diffForHumans() }}</span>
                    <a href="{{ route('extension.projects.show', $proj) }}" class="px-3 py-1.5 text-[11px] font-medium text-white bg-orange-500 hover:bg-orange-600 rounded transition">View</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Recent Programs --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h3 class="text-[13px] font-bold text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Recent Programs
                </h3>
                <a href="{{ route('extension.programs.index') }}" class="text-[11px] text-blue-600 hover:text-blue-700 font-medium">View All →</a>
            </div>
            @forelse($recentPrograms as $prog)
            <div class="flex items-center gap-4 px-5 py-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-50/50 transition-colors">
                <span class="flex-shrink-0 w-9 h-7 flex items-center justify-center text-xs font-bold text-gray-400 bg-gray-100 rounded">{{ $prog->id }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-gray-800 truncate">{{ $prog->title }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $prog->campus->name ?? 'N/A' }} · {{ $prog->proponent }}</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-medium rounded-full border
                    @if($prog->status === 'proposal') border-yellow-200 bg-yellow-50 text-yellow-700
                    @elseif($prog->status === 'under_review') border-purple-200 bg-purple-50 text-purple-700
                    @elseif($prog->status === 'approved') border-blue-200 bg-blue-50 text-blue-700
                    @elseif($prog->status === 'ongoing') border-cyan-200 bg-cyan-50 text-cyan-700
                    @else border-green-200 bg-green-50 text-green-700 @endif">{{ str_replace('_', ' ', ucfirst($prog->status)) }}</span>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-[13px] text-gray-400">No programs yet.</div>
            @endforelse
        </div>
    </div>

    {{-- ===== RIGHT COLUMN — STATUS OVERVIEW ===== --}}
    <div class="space-y-6">

        {{-- Programs Status --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-[13px] font-bold text-gray-700 mb-4">Programs by Status</h3>
            @php $pTotal = max($totalPrograms, 1); @endphp
            <div class="space-y-3">
                @foreach([
                    'proposal'     => ['label' => 'Proposal',     'color' => 'yellow', 'bar' => 'bg-yellow-400'],
                    'under_review' => ['label' => 'Under Review', 'color' => 'purple', 'bar' => 'bg-purple-500'],
                    'approved'     => ['label' => 'Approved',     'color' => 'blue',   'bar' => 'bg-blue-500'],
                    'ongoing'      => ['label' => 'Ongoing',      'color' => 'cyan',   'bar' => 'bg-cyan-500'],
                    'completed'    => ['label' => 'Completed',    'color' => 'green',  'bar' => 'bg-green-500'],
                ] as $sKey => $sMeta)
                <div>
                    <div class="flex items-center justify-between text-[12px] mb-1"><span class="text-{{ $sMeta['color'] }}-700 font-medium">{{ $sMeta['label'] }}</span><span class="text-gray-500">{{ $programsByStatus[$sKey] }}</span></div>
                    <div class="w-full bg-gray-100 rounded-full h-2"><div class="{{ $sMeta['bar'] }} h-2 rounded-full transition-all" style="width: {{ ($programsByStatus[$sKey]/$pTotal)*100 }}%"></div></div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Projects Status --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-[13px] font-bold text-gray-700 mb-4">Projects by Status</h3>
            @php $prjTotal = max($totalProjects, 1); @endphp
            <div class="space-y-3">
                @foreach([
                    'proposal'     => ['label' => 'Proposal',     'color' => 'yellow', 'bar' => 'bg-yellow-400'],
                    'under_review' => ['label' => 'Under Review', 'color' => 'purple', 'bar' => 'bg-purple-500'],
                    'approved'     => ['label' => 'Approved',     'color' => 'blue',   'bar' => 'bg-blue-500'],
                    'ongoing'      => ['label' => 'Ongoing',      'color' => 'cyan',   'bar' => 'bg-cyan-500'],
                    'completed'    => ['label' => 'Completed',    'color' => 'green',  'bar' => 'bg-green-500'],
                ] as $sKey => $sMeta)
                <div>
                    <div class="flex items-center justify-between text-[12px] mb-1"><span class="text-{{ $sMeta['color'] }}-700 font-medium">{{ $sMeta['label'] }}</span><span class="text-gray-500">{{ $projectsByStatus[$sKey] }}</span></div>
                    <div class="w-full bg-gray-100 rounded-full h-2"><div class="{{ $sMeta['bar'] }} h-2 rounded-full transition-all" style="width: {{ ($projectsByStatus[$sKey]/$prjTotal)*100 }}%"></div></div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Activities Status --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-[13px] font-bold text-gray-700 mb-4">Activities by Status</h3>
            @php $actTotal = max($totalActivities, 1); @endphp
            <div class="space-y-3">
                @foreach([
                    'proposal'     => ['label' => 'Proposal',     'color' => 'yellow', 'bar' => 'bg-yellow-400'],
                    'under_review' => ['label' => 'Under Review', 'color' => 'purple', 'bar' => 'bg-purple-500'],
                    'approved'     => ['label' => 'Approved',     'color' => 'blue',   'bar' => 'bg-blue-500'],
                    'ongoing'      => ['label' => 'Ongoing',      'color' => 'cyan',   'bar' => 'bg-cyan-500'],
                    'completed'    => ['label' => 'Completed',    'color' => 'green',  'bar' => 'bg-green-500'],
                ] as $sKey => $sMeta)
                <div>
                    <div class="flex items-center justify-between text-[12px] mb-1"><span class="text-{{ $sMeta['color'] }}-700 font-medium">{{ $sMeta['label'] }}</span><span class="text-gray-500">{{ $activitiesByStatus[$sKey] }}</span></div>
                    <div class="w-full bg-gray-100 rounded-full h-2"><div class="{{ $sMeta['bar'] }} h-2 rounded-full transition-all" style="width: {{ ($activitiesByStatus[$sKey]/$actTotal)*100 }}%"></div></div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Recent Projects --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                <h3 class="text-[13px] font-bold text-gray-700">Recent Projects</h3>
                <a href="{{ route('extension.projects.index') }}" class="text-[11px] text-blue-600 hover:text-blue-700 font-medium">View All →</a>
            </div>
            @forelse($recentProjects as $proj)
            <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-50/50 transition-colors">
                <span class="flex-shrink-0 w-7 h-6 flex items-center justify-center text-[10px] font-bold text-gray-400 bg-gray-100 rounded">{{ $proj->id }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-[12px] font-semibold text-gray-800 truncate">{{ $proj->title }}</p>
                    <p class="text-[11px] text-gray-400">{{ $proj->campus->name ?? '' }}</p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium rounded-full border
                    @if($proj->status === 'proposal') border-yellow-200 bg-yellow-50 text-yellow-700
                    @elseif($proj->status === 'under_review') border-purple-200 bg-purple-50 text-purple-700
                    @elseif($proj->status === 'approved') border-blue-200 bg-blue-50 text-blue-700
                    @elseif($proj->status === 'ongoing') border-cyan-200 bg-cyan-50 text-cyan-700
                    @else border-green-200 bg-green-50 text-green-700 @endif">{{ str_replace('_', ' ', ucfirst($proj->status)) }}</span>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-[13px] text-gray-400">No projects yet.</div>
            @endforelse
        </div>

    </div>
</div>
@endsection
