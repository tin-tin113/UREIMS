@extends('layouts.app')
@section('title', 'Edit Beneficiary')
@section('page-title', 'Edit Beneficiary')

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
    <span class="text-academic-heading font-medium">Edit Beneficiary</span>
</nav>

<form method="POST" action="{{ route('extension.beneficiaries.update', [$project, $beneficiary]) }}">
    @csrf @method('PATCH')

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    {{-- Beneficiary Information --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-academic-500 px-6 py-3"><h2 class="text-sm font-bold text-white">Beneficiary Information</h2></div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $beneficiary->name) }}" required class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Organization</label>
                    <input type="text" name="organization" value="{{ old('organization', $beneficiary->organization) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Address</label>
                    <input type="text" name="address" value="{{ old('address', $beneficiary->address) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Contact No.</label>
                    <input type="text" name="contact_no" value="{{ old('contact_no', $beneficiary->contact_no) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none">
                </div>
            </div>
        </div>
    </div>

    {{-- Impact Classification --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Impact Classification</h2></div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Type <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none">
                        <option value="">Select Type</option>
                        @foreach($types as $t)<option value="{{ $t }}" {{ old('type', $beneficiary->type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Sector</label>
                    <select name="sector" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none">
                        <option value="">Select Sector</option>
                        @foreach($sectors as $s)<option value="{{ $s }}" {{ old('sector', $beneficiary->sector) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Male Count</label>
                    <input type="number" min="0" name="male_count" value="{{ old('male_count', $beneficiary->male_count) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Female Count</label>
                    <input type="number" min="0" name="female_count" value="{{ old('female_count', $beneficiary->female_count) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none">
                </div>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('extension.projects.show', $project) }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded border border-gray-200 transition">Cancel</a>
        <button type="submit" class="px-6 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-semibold rounded transition">Update Beneficiary</button>
    </div>
</form>
@endsection
