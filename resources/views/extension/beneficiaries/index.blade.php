@extends('layouts.app')
@section('title', 'Beneficiaries — ' . $project->title)
@section('page-title', 'Project Beneficiaries')

@section('content')
@php
    $types   = \App\Models\ExtensionBeneficiary::TYPES;
    $sectors = \App\Models\ExtensionBeneficiary::SECTORS;
    $canEdit = auth()->user()->isAdmin() || $project->created_by === auth()->id();
@endphp

<nav class="text-sm text-gray-500 mb-5 flex items-center gap-1">
    <a href="{{ route('extension.projects.index') }}" class="hover:text-academic-500 transition">Projects</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('extension.projects.show', $project) }}" class="hover:text-academic-500 transition truncate max-w-xs">{{ $project->title }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-academic-heading font-medium">Beneficiaries</span>
</nav>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-5 p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded">{{ session('error') }}</div>
@endif

<div x-data="beneficiaryManager()" class="space-y-0">

    {{-- MAIN CARD --}}
    <div class="border border-gray-200 bg-white overflow-hidden">

        {{-- Header --}}
        <div class="bg-academic-500 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-bold text-white tracking-tight">Beneficiaries / Participants</h1>
                    <p class="text-academic-100 text-xs mt-0.5">{{ $project->title }}</p>
                </div>
                @if($canEdit)
                <button type="button" @click="toggleAddForm()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold rounded transition backdrop-blur">
                    <svg class="w-4 h-4 transition-transform" :class="showAdd ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span x-text="showAdd ? 'Close' : 'Add Participants'"></span>
                </button>
                @endif
            </div>
        </div>

        {{-- Impact Summary --}}
        @if($beneficiaries->total() > 0)
        <div class="px-6 py-3 bg-academic-50/50 border-b border-gray-200">
            <div class="flex flex-wrap items-center gap-6 text-sm">
                @php
                    $allBen = $project->beneficiaries;
                    $totalM = $allBen->sum('male_count');
                    $totalF = $allBen->sum('female_count');
                    $totalH = $allBen->sum('total_count');
                @endphp
                <span class="text-gray-600 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-academic-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Total Participants: <strong class="text-academic-heading">{{ $totalH }}</strong>
                </span>
                <span class="text-gray-600 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Male: <strong class="text-blue-600">{{ $totalM }}</strong>
                </span>
                <span class="text-gray-600 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                    Female: <strong class="text-pink-600">{{ $totalF }}</strong>
                </span>
                <span class="text-gray-600">Records: <strong class="text-gray-700">{{ $beneficiaries->total() }}</strong></span>
            </div>
        </div>
        @endif

        {{-- ============================================================ --}}
        {{-- ROSTER-STYLE ADD FORM (slide-down)                           --}}
        {{-- ============================================================ --}}
        @if($canEdit)
        <div x-show="showAdd" x-collapse x-cloak class="border-b border-gray-200">
            <form method="POST" action="{{ route('extension.beneficiaries.store', $project) }}">
                @csrf
                <input type="hidden" name="redirect_to" value="index">

                {{-- Form Header --}}
                <div class="px-6 py-3 bg-green-50 border-b border-green-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <h3 class="text-sm font-bold text-academic-heading">Participant Roster</h3>
                        <span class="text-xs text-gray-400">— Add one or many participants at once</span>
                    </div>
                    <span class="text-xs font-semibold text-green-600 bg-green-100 px-2 py-0.5 rounded" x-text="rows.length + ' entr' + (rows.length === 1 ? 'y' : 'ies')"></span>
                </div>

                @if($errors->any())
                <div class="mx-6 mt-3 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded">
                    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                {{-- Shared defaults bar --}}
                <div class="px-6 py-3 bg-gray-50/70 border-b border-gray-100">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Shared Defaults <span class="font-normal normal-case">(applied to new rows)</span></p>
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="min-w-[140px]">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Type</label>
                            <select x-model="defaults.type" class="w-full px-2.5 py-1.5 border border-gray-200 rounded text-xs bg-white focus:ring-2 focus:ring-academic-500 outline-none">
                                @foreach($types as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="min-w-[160px]">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sector</label>
                            <select x-model="defaults.sector" class="w-full px-2.5 py-1.5 border border-gray-200 rounded text-xs bg-white focus:ring-2 focus:ring-academic-500 outline-none">
                                <option value="">— None —</option>
                                @foreach($sectors as $key => $label)<option value="{{ $label }}">{{ $label }}</option>@endforeach
                            </select>
                        </div>
                        <div class="min-w-[200px]">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Organization</label>
                            <input type="text" x-model="defaults.organization" placeholder="e.g. Brgy. San Jose Farmers Assoc." class="w-full px-2.5 py-1.5 border border-gray-200 rounded text-xs bg-white focus:ring-2 focus:ring-academic-500 outline-none">
                        </div>
                        <button type="button" @click="applyDefaults()" class="px-3 py-1.5 bg-academic-500 hover:bg-academic-600 text-white text-[10px] font-bold uppercase tracking-wider rounded transition">
                            Apply to All Empty
                        </button>
                    </div>
                </div>

                {{-- Roster table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-3 py-2.5 text-center text-[10px] font-bold text-academic-heading uppercase tracking-wider w-10">#</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-bold text-academic-heading uppercase tracking-wider">Full Name <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-bold text-academic-heading uppercase tracking-wider">Organization</th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-bold text-academic-heading uppercase tracking-wider w-[130px]">Contact No.</th>
                                <th class="px-3 py-2.5 text-center text-[10px] font-bold text-academic-heading uppercase tracking-wider w-[120px]">Sex <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2.5 text-left text-[10px] font-bold text-academic-heading uppercase tracking-wider hidden lg:table-cell">Sector</th>
                                <th class="px-3 py-2.5 text-center text-[10px] font-bold text-academic-heading uppercase tracking-wider w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(row, idx) in rows" :key="row._key">
                                <tr class="hover:bg-green-50/30 transition-colors group" :class="{ 'bg-red-50/30': row._error }">
                                    {{-- Row # --}}
                                    <td class="px-3 py-2 text-center">
                                        <span class="text-xs font-bold text-gray-300" x-text="idx + 1"></span>
                                    </td>

                                    {{-- Name --}}
                                    <td class="px-3 py-2">
                                        <input type="text"
                                            :name="'beneficiaries['+idx+'][name]'"
                                            x-model="row.name"
                                            required
                                            class="w-full border border-gray-200 rounded px-2.5 py-1.5 text-sm bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"
                                            placeholder="e.g. Juan Dela Cruz"
                                            @keydown.enter.prevent="handleEnter(idx, $event)">
                                        {{-- Hidden fields for type --}}
                                        <input type="hidden" :name="'beneficiaries['+idx+'][type]'" :value="row.type || defaults.type || 'individual'">
                                        <input type="hidden" :name="'beneficiaries['+idx+'][address]'" :value="row.address">
                                    </td>

                                    {{-- Organization --}}
                                    <td class="px-3 py-2">
                                        <input type="text"
                                            :name="'beneficiaries['+idx+'][organization]'"
                                            x-model="row.organization"
                                            class="w-full border border-gray-200 rounded px-2.5 py-1.5 text-sm bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"
                                            placeholder="Organization"
                                            @keydown.enter.prevent="handleEnter(idx, $event)">
                                    </td>

                                    {{-- Contact --}}
                                    <td class="px-3 py-2">
                                        <input type="text"
                                            :name="'beneficiaries['+idx+'][contact_no]'"
                                            x-model="row.contact_no"
                                            class="w-full border border-gray-200 rounded px-2.5 py-1.5 text-sm bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"
                                            placeholder="09xxxxxxxxx"
                                            @keydown.enter.prevent="handleEnter(idx, $event)">
                                    </td>

                                    {{-- Sex (Radio: M / F) --}}
                                    <td class="px-3 py-2">
                                        <div class="flex items-center justify-center gap-1">
                                            <input type="hidden" :name="'beneficiaries['+idx+'][gender]'" :value="row.gender">

                                            {{-- Male --}}
                                            <label class="relative cursor-pointer">
                                                <input type="radio"
                                                    :name="'_gender_radio_'+row._key"
                                                    value="male"
                                                    x-model="row.gender"
                                                    class="sr-only peer"
                                                    required>
                                                <span class="flex items-center gap-1 px-2.5 py-1.5 rounded text-xs font-semibold border transition-all
                                                    peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500
                                                    bg-white text-gray-400 border-gray-200 hover:border-blue-300 hover:text-blue-500">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="10" cy="8" r="5" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M15 3h6m0 0v6m0-6l-7 7"/></svg>
                                                    M
                                                </span>
                                            </label>

                                            {{-- Female --}}
                                            <label class="relative cursor-pointer">
                                                <input type="radio"
                                                    :name="'_gender_radio_'+row._key"
                                                    value="female"
                                                    x-model="row.gender"
                                                    class="sr-only peer"
                                                    required>
                                                <span class="flex items-center gap-1 px-2.5 py-1.5 rounded text-xs font-semibold border transition-all
                                                    peer-checked:bg-pink-500 peer-checked:text-white peer-checked:border-pink-500
                                                    bg-white text-gray-400 border-gray-200 hover:border-pink-300 hover:text-pink-500">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="8" r="5" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 13v8m-3-3h6"/></svg>
                                                    F
                                                </span>
                                            </label>
                                        </div>
                                    </td>

                                    {{-- Sector (hidden on smaller screens) --}}
                                    <td class="px-3 py-2 hidden lg:table-cell">
                                        <select :name="'beneficiaries['+idx+'][sector]'" x-model="row.sector"
                                            class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs bg-white focus:ring-2 focus:ring-academic-500 outline-none">
                                            <option value="">—</option>
                                            @foreach($sectors as $key => $label)<option value="{{ $label }}">{{ $label }}</option>@endforeach
                                        </select>
                                    </td>

                                    {{-- Remove --}}
                                    <td class="px-3 py-2 text-center">
                                        <button type="button" @click="removeRow(idx)" x-show="rows.length > 1"
                                            class="p-1 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded transition opacity-0 group-hover:opacity-100">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Add row + Submit bar --}}
                <div class="px-6 py-3 bg-gray-50/70 border-t border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="addRow()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-green-50 text-green-700 text-xs font-semibold rounded border border-green-200 hover:border-green-300 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Row
                        </button>
                        <button type="button" @click="addRows(5)" class="text-xs text-gray-400 hover:text-academic-500 transition">
                            + 5 rows
                        </button>
                        <button type="button" @click="addRows(10)" class="text-xs text-gray-400 hover:text-academic-500 transition">
                            + 10 rows
                        </button>
                        <span class="text-[10px] text-gray-300 hidden sm:inline">Press Enter on the last row to add another</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showAdd = false" class="px-4 py-2 text-xs font-semibold text-gray-500 hover:text-gray-700 transition">Cancel</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save <span x-text="rows.filter(r => r.name.trim()).length"></span> Participant(s)
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif

        {{-- Search / Filter Toolbar --}}
        @if($beneficiaries->total() > 0 || request()->hasAny(['search','type','sector']))
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, organization, sector…" class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded text-sm bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <select name="type" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded text-sm bg-white focus:ring-2 focus:ring-academic-500 outline-none min-w-[130px]">
                    <option value="">All Types</option>
                    @foreach($types as $t)<option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>@endforeach
                </select>
                <select name="sector" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded text-sm bg-white focus:ring-2 focus:ring-academic-500 outline-none min-w-[130px]">
                    <option value="">All Sectors</option>
                    @foreach($sectors as $key => $label)<option value="{{ $label }}" {{ request('sector') === $label ? 'selected' : '' }}>{{ $label }}</option>@endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-academic-500 hover:bg-academic-600 text-white text-sm font-medium rounded transition">Filter</button>
                @if(request()->hasAny(['search','type','sector']))
                    <a href="{{ route('extension.beneficiaries.index', $project) }}" class="text-sm text-gray-400 hover:text-academic-500 transition">Clear</a>
                @endif
            </form>
        </div>
        @endif

        {{-- ============================================================ --}}
        {{-- MAIN TABLE — Participant List                                 --}}
        {{-- ============================================================ --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-center text-xs font-bold text-academic-heading uppercase tracking-wider w-12">#</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-academic-heading uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-academic-heading uppercase tracking-wider">Organization</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-academic-heading uppercase tracking-wider w-16">Sex</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-academic-heading uppercase tracking-wider">Type / Sector</th>
                        @if($canEdit)
                        <th class="px-4 py-3 text-right text-xs font-bold text-academic-heading uppercase tracking-wider w-24">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($beneficiaries as $idx => $ben)
                    @php
                        $gender = $ben->male_count > 0 && $ben->female_count == 0 ? 'male' : ($ben->female_count > 0 && $ben->male_count == 0 ? 'female' : null);
                    @endphp
                    <tr class="hover:bg-academic-50/30 transition-colors group" id="ben-row-{{ $ben->id }}">
                        {{-- Row # --}}
                        <td class="px-4 py-3 text-center text-xs font-medium text-gray-300">{{ $beneficiaries->firstItem() + $idx }}</td>

                        {{-- Name + Contact --}}
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-800">{{ $ben->name }}</p>
                            @if($ben->contact_no)<p class="text-xs text-gray-400 mt-0.5">{{ $ben->contact_no }}</p>@endif
                            @if($ben->address)<p class="text-xs text-gray-400 truncate max-w-[200px]" title="{{ $ben->address }}">{{ $ben->address }}</p>@endif
                        </td>

                        {{-- Organization --}}
                        <td class="px-4 py-3 text-gray-600 text-sm">{{ $ben->organization ?? '—' }}</td>

                        {{-- Sex badge --}}
                        <td class="px-4 py-3 text-center">
                            @if($gender === 'male')
                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-100">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="10" cy="8" r="5" stroke-width="2.5"/><path stroke-linecap="round" stroke-width="2.5" d="M15 3h6m0 0v6m0-6l-7 7"/></svg>
                                    M
                                </span>
                            @elseif($gender === 'female')
                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-pink-50 text-pink-600 border border-pink-100">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="8" r="5" stroke-width="2.5"/><path stroke-linecap="round" stroke-width="2.5" d="M12 13v8m-3-3h6"/></svg>
                                    F
                                </span>
                            @else
                                <span class="text-xs text-gray-400" title="Male: {{ $ben->male_count }}, Female: {{ $ben->female_count }}">
                                    {{ $ben->male_count }}M / {{ $ben->female_count }}F
                                </span>
                            @endif
                        </td>

                        {{-- Type / Sector --}}
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs font-medium rounded {{ $ben->type==='individual'?'bg-blue-50 text-blue-600':($ben->type==='organization'?'bg-purple-50 text-purple-600':'bg-green-50 text-green-600') }}">{{ ucfirst($ben->type) }}</span>
                            @if($ben->sector)<span class="text-xs text-gray-400 ml-1">{{ $ben->sector }}</span>@endif
                        </td>

                        {{-- Actions --}}
                        @if($canEdit)
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button"
                                    @click="openEdit({{ json_encode([
                                        'id'           => $ben->id,
                                        'name'         => $ben->name,
                                        'organization' => $ben->organization,
                                        'address'      => $ben->address,
                                        'contact_no'   => $ben->contact_no,
                                        'type'         => $ben->type,
                                        'sector'       => $ben->sector,
                                        'gender'       => $gender,
                                        'male_count'   => $ben->male_count,
                                        'female_count' => $ben->female_count,
                                    ]) }})"
                                    class="px-2.5 py-1 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded transition">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('extension.beneficiaries.destroy', [$project, $ben]) }}" onsubmit="return confirmSubmit(event,'Remove Participant','Remove this entry from the list?','danger','Remove')" class="inline">@csrf @method('DELETE')
                                    <button class="p-1 text-gray-300 hover:text-red-600 hover:bg-red-50 rounded transition opacity-0 group-hover:opacity-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canEdit ? 6 : 5 }}" class="px-6 py-16 text-center">
                            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            <p class="text-gray-400 text-sm mb-1">No participants listed yet.</p>
                            @if($canEdit)
                            <p class="text-gray-400 text-xs">Click <strong>"Add Participants"</strong> above to start building your roster.</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($beneficiaries->hasPages())<div class="mt-4">{{ $beneficiaries->withQueryString()->links() }}</div>@endif

    {{-- ============================================================ --}}
    {{-- EDIT MODAL                                                    --}}
    {{-- ============================================================ --}}
    @if($canEdit)
    <div x-show="editId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeEdit()">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="closeEdit()"></div>

        <div x-show="editId" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-white border border-gray-200 shadow-xl w-full max-w-md rounded overflow-hidden">
            <div class="bg-academic-500 px-5 py-3 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Participant
                </h3>
                <button @click="closeEdit()" class="text-white/70 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="editAction" method="POST" class="p-5 space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PATCH">
                <input type="hidden" name="redirect_to" value="index">
                <input type="hidden" name="gender" :value="editForm.gender">

                {{-- Name --}}
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editForm.name" required class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none">
                </div>

                {{-- Organization + Contact --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Organization</label>
                        <input type="text" name="organization" x-model="editForm.organization" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Contact No.</label>
                        <input type="text" name="contact_no" x-model="editForm.contact_no" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none">
                    </div>
                </div>

                {{-- Address --}}
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Address</label>
                    <input type="text" name="address" x-model="editForm.address" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none">
                </div>

                {{-- Sex (Radio) + Type + Sector --}}
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Sex <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-1">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="_edit_gender_radio" value="male" x-model="editForm.gender" class="sr-only peer" required>
                                <span class="flex items-center gap-1 px-3 py-1.5 rounded text-xs font-semibold border transition-all
                                    peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-500
                                    bg-white text-gray-400 border-gray-200 hover:border-blue-300 hover:text-blue-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="10" cy="8" r="5" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M15 3h6m0 0v6m0-6l-7 7"/></svg>
                                    M
                                </span>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="_edit_gender_radio" value="female" x-model="editForm.gender" class="sr-only peer" required>
                                <span class="flex items-center gap-1 px-3 py-1.5 rounded text-xs font-semibold border transition-all
                                    peer-checked:bg-pink-500 peer-checked:text-white peer-checked:border-pink-500
                                    bg-white text-gray-400 border-gray-200 hover:border-pink-300 hover:text-pink-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="8" r="5" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 13v8m-3-3h6"/></svg>
                                    F
                                </span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Type</label>
                        <select name="type" x-model="editForm.type" required class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none">
                            @foreach($types as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sector</label>
                        <select name="sector" x-model="editForm.sector" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none">
                            <option value="">—</option>
                            @foreach($sectors as $key => $label)<option value="{{ $label }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="closeEdit()" class="px-4 py-2 text-xs font-semibold text-gray-500 hover:text-gray-700 transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-academic-500 hover:bg-academic-600 text-white text-xs font-semibold rounded transition">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

<script>
function beneficiaryManager() {
    let keyCounter = 0;
    return {
        showAdd: {{ $errors->any() ? 'true' : 'false' }},
        editId: null,
        editForm: { name:'', organization:'', address:'', contact_no:'', type:'individual', sector:'', gender:'' },
        defaults: { type:'individual', sector:'', organization:'' },
        rows: [makeRow()],

        toggleAddForm() {
            this.showAdd = !this.showAdd;
            if (this.showAdd) this.closeEdit();
        },

        addRow() {
            const row = makeRow();
            // Apply defaults to new row
            if (this.defaults.organization && !row.organization) row.organization = this.defaults.organization;
            if (this.defaults.sector && !row.sector) row.sector = this.defaults.sector;
            if (this.defaults.type)  row.type = this.defaults.type;
            this.rows.push(row);
            this.$nextTick(() => {
                // Focus the name input of the new row
                const inputs = this.$el.querySelectorAll('input[name$="[name]"]');
                if (inputs.length) inputs[inputs.length - 1].focus();
            });
        },

        addRows(count) {
            for (let i = 0; i < count; i++) {
                const row = makeRow();
                if (this.defaults.organization) row.organization = this.defaults.organization;
                if (this.defaults.sector) row.sector = this.defaults.sector;
                if (this.defaults.type) row.type = this.defaults.type;
                this.rows.push(row);
            }
        },

        removeRow(idx) {
            this.rows.splice(idx, 1);
        },

        applyDefaults() {
            this.rows.forEach(r => {
                if (!r.organization && this.defaults.organization) r.organization = this.defaults.organization;
                if (!r.sector && this.defaults.sector) r.sector = this.defaults.sector;
                if (!r.type || r.type === 'individual') r.type = this.defaults.type || 'individual';
            });
        },

        handleEnter(idx, event) {
            // If pressing Enter on the last row, add a new one
            if (idx === this.rows.length - 1) {
                this.addRow();
            }
        },

        get editAction() {
            return this.editId
                ? '{{ url("extension/projects/" . $project->id . "/beneficiaries") }}/' + this.editId
                : '#';
        },

        openEdit(ben) {
            this.editId = ben.id;
            this.editForm = {
                name:         ben.name || '',
                organization: ben.organization || '',
                address:      ben.address || '',
                contact_no:   ben.contact_no || '',
                type:         ben.type || 'individual',
                sector:       ben.sector || '',
                gender:       ben.gender || '',
            };
            this.showAdd = false;
            document.body.style.overflow = 'hidden';
        },

        closeEdit() {
            this.editId = null;
            document.body.style.overflow = '';
        },
    }

    function makeRow() {
        return {
            _key: ++keyCounter,
            _error: false,
            name: '',
            organization: '',
            contact_no: '',
            address: '',
            type: 'individual',
            sector: '',
            gender: '',
        };
    }
}
</script>
@endsection
