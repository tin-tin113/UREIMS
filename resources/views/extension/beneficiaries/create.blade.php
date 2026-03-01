@extends('layouts.app')
@section('title', 'Add Beneficiaries — ' . $project->title)
@section('page-title', 'Add Beneficiaries')

@section('content')
@php
    $types = \App\Models\ExtensionBeneficiary::TYPES;
    $sectors = \App\Models\ExtensionBeneficiary::SECTORS;
@endphp

<nav class="text-sm text-gray-500 mb-5 flex items-center gap-1">
    <a href="{{ route('extension.projects.index') }}" class="hover:text-academic-500 transition">Projects</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('extension.projects.show', $project) }}" class="hover:text-academic-500 transition truncate max-w-xs">{{ $project->title }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-academic-heading font-medium">Add Beneficiaries</span>
</nav>

<form method="POST" action="{{ route('extension.beneficiaries.store', $project) }}" x-data="beneficiaryForm()">
    @csrf

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-academic-500 px-6 py-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-white">Beneficiary Entries</h2>
                <span class="text-academic-100 text-xs">Add one or multiple beneficiaries at once</span>
            </div>
        </div>

        {{-- Quick fill defaults --}}
        <div class="px-6 py-3 bg-academic-50/50 border-b border-gray-200">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Default Type</label>
                    <select x-model="defaultType" class="px-3 py-1.5 border border-gray-200 rounded text-xs bg-white focus:ring-2 focus:ring-academic-500 outline-none">
                        <option value="">— Select —</option>
                        @foreach($types as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Default Sector</label>
                    <select x-model="defaultSector" class="px-3 py-1.5 border border-gray-200 rounded text-xs bg-white focus:ring-2 focus:ring-academic-500 outline-none">
                        <option value="">— Select —</option>
                        @foreach($sectors as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach
                    </select>
                </div>
                <button type="button" @click="applyDefaults()" class="px-3 py-1.5 bg-academic-500 hover:bg-academic-600 text-white text-xs font-semibold rounded transition">
                    Apply to All Empty
                </button>
                <p class="text-[10px] text-gray-400 self-center">Set defaults to quickly fill type/sector across all rows</p>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <template x-for="(b, idx) in beneficiaries" :key="idx">
                <div class="p-4 bg-gray-50 rounded border border-gray-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-academic-heading uppercase flex items-center gap-2">
                            <svg class="w-4 h-4 text-academic-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Beneficiary #<span x-text="idx+1"></span>
                        </span>
                        <button type="button" @click="removeBeneficiary(idx)" x-show="beneficiaries.length > 1" class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" :name="'beneficiaries['+idx+'][name]'" x-model="b.name" required class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none" placeholder="Beneficiary name">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Organization</label>
                            <input type="text" :name="'beneficiaries['+idx+'][organization]'" x-model="b.organization" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none" placeholder="Organization name">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Contact No.</label>
                            <input type="text" :name="'beneficiaries['+idx+'][contact_no]'" x-model="b.contact_no" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none" placeholder="Contact number">
                        </div>
                        <div class="lg:col-span-3">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Address</label>
                            <input type="text" :name="'beneficiaries['+idx+'][address]'" x-model="b.address" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none" placeholder="Address">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Type <span class="text-red-500">*</span></label>
                            <select :name="'beneficiaries['+idx+'][type]'" x-model="b.type" required class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none">
                                <option value="">Select</option>
                                @foreach($types as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sector</label>
                            <select :name="'beneficiaries['+idx+'][sector]'" x-model="b.sector" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none">
                                <option value="">Select</option>
                                @foreach($sectors as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Male Count</label>
                            <input type="number" min="0" :name="'beneficiaries['+idx+'][male_count]'" x-model.number="b.male_count" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Female Count</label>
                            <input type="number" min="0" :name="'beneficiaries['+idx+'][female_count]'" x-model.number="b.female_count" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none" placeholder="0">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-xs text-gray-400 pt-1">
                        <span>Total: <strong class="text-gray-600" x-text="(b.male_count || 0) + (b.female_count || 0)"></strong></span>
                    </div>
                </div>
            </template>

            {{-- Add another --}}
            <div class="flex items-center justify-between pt-2">
                <button type="button" @click="addBeneficiary()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-academic-50 hover:bg-academic-100 text-academic-600 text-xs font-semibold rounded border border-academic-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Another Beneficiary
                </button>
                <span class="text-xs text-gray-400"><span x-text="beneficiaries.length"></span> beneficiary(ies) to add</span>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('extension.projects.show', $project) }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded border border-gray-200 transition">Cancel</a>
        <button type="submit" class="px-6 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-semibold rounded transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Save <span x-text="beneficiaries.length"></span> Beneficiary(ies)
        </button>
    </div>
</form>

<script>
function beneficiaryForm() {
    return {
        defaultType: '',
        defaultSector: '',
        beneficiaries: [emptyBeneficiary()],
        addBeneficiary() {
            this.beneficiaries.push(emptyBeneficiary());
        },
        removeBeneficiary(i) {
            this.beneficiaries.splice(i, 1);
        },
        applyDefaults() {
            this.beneficiaries.forEach(b => {
                if (!b.type && this.defaultType) b.type = this.defaultType;
                if (!b.sector && this.defaultSector) b.sector = this.defaultSector;
            });
        }
    }
}
function emptyBeneficiary() {
    return { name:'', organization:'', address:'', contact_no:'', type:'', sector:'', male_count:0, female_count:0 };
}
</script>
@endsection
