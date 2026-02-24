@extends('layouts.app')
@section('title', 'Beneficiaries')
@section('page-title', 'Extension Beneficiaries')

@section('content')
{{-- ===== TOOLBAR ===== --}}
<div class="bg-white rounded-xl border border-gray-200 px-4 py-3 mb-5">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[180px]">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search beneficiaries..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        </div>
        <select name="project_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none min-w-[160px]">
            <option value="">All Projects ({{ $totalBeneficiaries }})</option>
            @foreach($projects as $proj)
                <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ Str::limit($proj->title, 30) }} ({{ $proj->beneficiaries_count }})</option>
            @endforeach
        </select>
        <select name="type" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none min-w-[120px]">
            <option value="">All Types</option>
            @foreach(\App\Models\ExtensionBeneficiary::TYPES as $t)
                <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <select name="sector" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none min-w-[140px]">
            <option value="">All Sectors</option>
            @foreach(\App\Models\ExtensionBeneficiary::SECTORS as $sKey => $sLabel)
                <option value="{{ $sKey }}" {{ request('sector') === $sKey ? 'selected' : '' }}>{{ $sLabel }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-[13px] font-medium rounded-lg border border-gray-200 transition">Search</button>
        @if(request()->hasAny(['search','project_id','type','sector']))
            <a href="{{ route('extension.beneficiaries.index') }}" class="text-[13px] text-gray-400 hover:text-gray-600 transition">Clear</a>
        @endif
        <a href="{{ route('extension.beneficiaries.create') }}" class="ml-auto inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Beneficiary
        </a>
    </form>
</div>

{{-- ===== IMPACT SUMMARY ===== --}}
<div class="flex flex-wrap items-center gap-4 mb-4 text-[12px] text-gray-500">
    <span class="font-medium text-gray-700">{{ $totalBeneficiaries }} records</span>
    <span class="text-gray-300">|</span>
    <span class="flex items-center gap-1">
        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        {{ number_format($totalHead) }} total headcount
    </span>
    <span class="flex items-center gap-1">♂ {{ number_format($totalMale) }} male</span>
    <span class="flex items-center gap-1">♀ {{ number_format($totalFemale) }} female</span>
</div>

{{-- ===== LIST ===== --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="hidden md:grid grid-cols-12 gap-4 px-5 py-2.5 bg-gray-50/80 border-b border-gray-100 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
        <div class="col-span-3">Beneficiary</div>
        <div class="col-span-2">Project</div>
        <div class="col-span-2">Type / Sector</div>
        <div class="col-span-1 text-center">Male</div>
        <div class="col-span-1 text-center">Female</div>
        <div class="col-span-1 text-center">Total</div>
        <div class="col-span-2 text-right">Actions</div>
    </div>
    @forelse($beneficiaries as $ben)
        <div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-4 items-center px-5 py-3.5 border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
            <div class="col-span-3 min-w-0">
                <p class="text-[13px] font-semibold text-gray-800 truncate">{{ $ben->name }}</p>
                @if($ben->organization)
                    <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $ben->organization }}</p>
                @endif
                @if($ben->contact_no)
                    <p class="text-[11px] text-gray-400 flex items-center gap-1 mt-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $ben->contact_no }}
                    </p>
                @endif
            </div>
            <div class="col-span-2 min-w-0">
                <a href="{{ route('extension.projects.show', $ben->project) }}" class="text-[12px] text-gray-500 hover:text-blue-600 transition truncate block">{{ Str::limit($ben->project->title ?? 'No Project', 25) }}</a>
                @if($ben->project && $ben->project->program)
                    <p class="text-[10px] text-gray-400 truncate">{{ Str::limit($ben->project->program->title, 25) }}</p>
                @endif
            </div>
            <div class="col-span-2 min-w-0 hidden md:block">
                @php
                    $typeColors = ['individual' => 'bg-blue-50 text-blue-600 border-blue-100', 'organization' => 'bg-purple-50 text-purple-600 border-purple-100', 'community' => 'bg-green-50 text-green-600 border-green-100'];
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-full border {{ $typeColors[$ben->type] ?? 'bg-gray-50 text-gray-600 border-gray-100' }}">{{ ucfirst($ben->type) }}</span>
                @if($ben->sector)
                    <p class="text-[10px] text-gray-400 mt-1">{{ \App\Models\ExtensionBeneficiary::SECTORS[$ben->sector] ?? ucfirst($ben->sector) }}</p>
                @endif
            </div>
            <div class="col-span-1 text-center hidden md:block">
                <span class="text-[12px] font-medium text-blue-600">{{ $ben->male_count }}</span>
            </div>
            <div class="col-span-1 text-center hidden md:block">
                <span class="text-[12px] font-medium text-pink-600">{{ $ben->female_count }}</span>
            </div>
            <div class="col-span-1 text-center hidden md:block">
                <span class="text-[12px] font-bold text-gray-700">{{ $ben->total_count }}</span>
            </div>
            <div class="col-span-2 flex items-center justify-end gap-1.5">
                <a href="{{ route('extension.beneficiaries.edit', $ben) }}" class="px-3 py-1.5 text-[11px] font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition">Edit</a>
                <form method="POST" action="{{ route('extension.beneficiaries.destroy', $ben) }}" onsubmit="return confirmSubmit(event, 'Remove Beneficiary', 'Are you sure you want to remove this beneficiary?', 'danger', 'Remove')" class="inline">
                    @csrf @method('DELETE')
                    <button class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
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
@endsection
