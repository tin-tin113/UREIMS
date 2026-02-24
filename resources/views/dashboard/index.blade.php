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
        <p class="text-[11px] text-gray-400 mt-2">{{ $programsByStatus['ongoing'] }} ongoing · {{ $programsByStatus['completed'] }} done</p>
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
        <p class="text-[11px] text-gray-400 mt-2">{{ $projectsByStatus['ongoing'] }} ongoing · {{ $projectsByStatus['completed'] }} done</p>
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
        <p class="text-[11px] text-gray-400 mt-2">{{ $activitiesByStatus['ongoing'] }} ongoing · {{ $activitiesByStatus['completed'] }} done</p>
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
        <p class="text-[11px] text-gray-400 mt-2">♂ {{ number_format($beneficiaryMaleTotal) }} · ♀ {{ number_format($beneficiaryFemaleTotal) }} · {{ number_format($beneficiaryHeadTotal) }} total</p>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ===== LEFT COLUMN ===== --}}
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
            <div class="flex items-center gap-4 px-5 py-3 border-b border-gray-100 last:border-b-0 hover:bg-red-50/30 transition-colors">
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-gray-800 truncate">{{ $act->title }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $act->project->title ?? 'No Project' }}</p>
                </div>
                <span class="text-[11px] text-red-500 font-medium whitespace-nowrap">{{ \Carbon\Carbon::parse($act->target_date)->diffForHumans() }}</span>
                <a href="{{ route('extension.activities.edit', $act) }}" class="px-3 py-1.5 text-[11px] font-medium text-white bg-red-500 hover:bg-red-600 rounded-md transition flex-shrink-0">View</a>
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
            <div class="flex items-center gap-4 px-5 py-3 border-b border-gray-100 last:border-b-0 hover:bg-orange-50/30 transition-colors">
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-gray-800 truncate">{{ $proj->title }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $proj->persons_responsible ?? '' }}</p>
                </div>
                <span class="text-[11px] text-orange-500 font-medium whitespace-nowrap">{{ \Carbon\Carbon::parse($proj->target_end_date)->diffForHumans() }}</span>
                <a href="{{ route('extension.projects.show', $proj) }}" class="px-3 py-1.5 text-[11px] font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-md transition flex-shrink-0">View</a>
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
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-gray-800 truncate">{{ $prog->title }}</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $prog->campus->name ?? 'N/A' }} · {{ $prog->proponent }}</p>
                </div>
                @php $psc = ['proposal'=>'border-yellow-200 bg-yellow-50 text-yellow-700','ongoing'=>'border-cyan-200 bg-cyan-50 text-cyan-700','completed'=>'border-green-200 bg-green-50 text-green-700']; @endphp
                <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-medium rounded-full border {{ $psc[$prog->status] ?? '' }} flex-shrink-0">{{ str_replace('_', ' ', ucfirst($prog->status)) }}</span>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-[13px] text-gray-400">No programs yet.</div>
            @endforelse
        </div>
    </div>

    {{-- ===== RIGHT COLUMN ===== --}}
    <div class="space-y-6">

        {{-- Status Overview (Combined) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-[13px] font-bold text-gray-700 mb-4">Status Overview</h3>

            @php
                $statusMeta = [
                    'proposal'  => ['label' => 'Proposal',  'bar' => 'bg-yellow-400'],
                    'ongoing'   => ['label' => 'Ongoing',   'bar' => 'bg-cyan-500'],
                    'completed' => ['label' => 'Completed', 'bar' => 'bg-green-500'],
                ];
                $pTotal = max($totalPrograms, 1);
                $prjTotal = max($totalProjects, 1);
                $actTotal = max($totalActivities, 1);
            @endphp

            {{-- Programs --}}
            <div class="mb-4">
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Programs</p>
                <div class="space-y-1.5">
                    @foreach($statusMeta as $sKey => $sMeta)
                    <div class="flex items-center gap-2">
                        <span class="w-16 text-[11px] text-gray-500">{{ $sMeta['label'] }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-1.5"><div class="{{ $sMeta['bar'] }} h-1.5 rounded-full transition-all" style="width: {{ ($programsByStatus[$sKey]/$pTotal)*100 }}%"></div></div>
                        <span class="w-6 text-right text-[11px] text-gray-500 font-medium">{{ $programsByStatus[$sKey] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Projects --}}
            <div class="mb-4 pt-3 border-t border-gray-100">
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Projects</p>
                <div class="space-y-1.5">
                    @foreach($statusMeta as $sKey => $sMeta)
                    <div class="flex items-center gap-2">
                        <span class="w-16 text-[11px] text-gray-500">{{ $sMeta['label'] }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-1.5"><div class="{{ $sMeta['bar'] }} h-1.5 rounded-full transition-all" style="width: {{ ($projectsByStatus[$sKey]/$prjTotal)*100 }}%"></div></div>
                        <span class="w-6 text-right text-[11px] text-gray-500 font-medium">{{ $projectsByStatus[$sKey] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Activities --}}
            <div class="pt-3 border-t border-gray-100">
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Activities</p>
                <div class="space-y-1.5">
                    @foreach($statusMeta as $sKey => $sMeta)
                    <div class="flex items-center gap-2">
                        <span class="w-16 text-[11px] text-gray-500">{{ $sMeta['label'] }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-1.5"><div class="{{ $sMeta['bar'] }} h-1.5 rounded-full transition-all" style="width: {{ ($activitiesByStatus[$sKey]/$actTotal)*100 }}%"></div></div>
                        <span class="w-6 text-right text-[11px] text-gray-500 font-medium">{{ $activitiesByStatus[$sKey] }}</span>
                    </div>
                    @endforeach
                </div>
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
                <div class="flex-1 min-w-0">
                    <p class="text-[12px] font-semibold text-gray-800 truncate">{{ $proj->title }}</p>
                    <p class="text-[11px] text-gray-400">{{ $proj->campus->name ?? '' }}</p>
                </div>
                @php $prsc = ['proposal'=>'border-yellow-200 bg-yellow-50 text-yellow-700','ongoing'=>'border-cyan-200 bg-cyan-50 text-cyan-700','completed'=>'border-green-200 bg-green-50 text-green-700']; @endphp
                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium rounded-full border {{ $prsc[$proj->status] ?? '' }} flex-shrink-0">{{ str_replace('_', ' ', ucfirst($proj->status)) }}</span>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-[13px] text-gray-400">No projects yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
