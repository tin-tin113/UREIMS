@extends('layouts.app')

@section('title', 'Submit Proposal — Program Details')
@section('page-title', 'Submit Program Proposal')

@section('content')
<div class="max-w-4xl mx-auto" x-data="programDetailsForm()">

    {{-- Stepper --}}
    @include('extension.wizard._stepper', ['currentStep' => 3, 'type' => $type])

    @if($errors->any())
        <div class="mb-6 px-4 py-3 rounded-lg bg-red-50 border border-red-200">
            <p class="font-medium text-red-700 text-sm mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('proposal.wizard.save-details', $type) }}">
        @csrf
        <input type="hidden" name="_current_step" value="3">

        {{-- Section: Basic Information --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Basic Information</h3>
                <p class="text-xs text-gray-400 mt-0.5">Fields marked with <span class="text-red-500">*</span> are recommended but not required to proceed.</p>
            </div>
            <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ $draft['details']['title'] ?? old('title') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition"
                           placeholder="Enter the full program title">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">I.C. Number</label>
                    <input type="text" name="ic_no" value="{{ $draft['details']['ic_no'] ?? old('ic_no') }}"
                           placeholder="CHMSU-ECS-XXXX-XXX-XXX"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campus <span class="text-red-500">*</span></label>
                    <select name="campus_id"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                        <option value="">Select Campus</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ ($draft['details']['campus_id'] ?? old('campus_id')) == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            </div>
        </div>

        {{-- Section: Proponent & Location --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Proponent & Location</h3>
            </div>
            <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program Proponent <span class="text-red-500">*</span></label>
                    <input type="text" name="proponent_name" value="{{ $draft['details']['proponent_name'] ?? old('proponent_name') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Division/Unit (College)</label>
                    <input type="text" name="division_unit" value="{{ $draft['details']['division_unit'] ?? old('division_unit') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Proponent Address</label>
                    <input type="text" name="proponent_address" value="{{ $draft['details']['proponent_address'] ?? old('proponent_address') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                    <input type="text" name="contact_no" value="{{ $draft['details']['contact_no'] ?? old('contact_no') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program Location</label>
                    <input type="text" name="program_location" value="{{ $draft['details']['program_location'] ?? old('program_location') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
            </div>
            </div>
        </div>

        {{-- Section: Cooperating Entity --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Cooperating Entity</h3>
            </div>
            <div class="p-6">

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name of Institutions</label>
                    <textarea name="cooperating_entities" rows="2"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition resize-y">{{ $draft['details']['cooperating_entities'] ?? old('cooperating_entities') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" name="cooperating_entity_address" value="{{ $draft['details']['cooperating_entity_address'] ?? old('cooperating_entity_address') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
            </div>
            </div>
        </div>

        {{-- Section: Beneficiaries --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Beneficiaries</h3>
            </div>
            <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Beneficiary Class</label>
                    <textarea name="beneficiary_class" rows="2"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition resize-y">{{ $draft['details']['beneficiary_class'] ?? old('beneficiary_class') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Target Recipients</label>
                    <input type="number" name="target_recipients" value="{{ $draft['details']['target_recipients'] ?? old('target_recipients') }}" min="0"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
            </div>
            </div>
        </div>

        {{-- Section: Funding --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Resource / Funding Requirement</h3>
            </div>
            <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CHMSU GAA (₱)</label>
                    <input type="number" step="0.01" name="funding_chmsu_gaa"
                           x-model="gaa" @input="calcTotal()" min="0"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">GAA Note</label>
                    <input type="text" name="funding_chmsu_gaa_note" value="{{ $draft['details']['funding_chmsu_gaa_note'] ?? old('funding_chmsu_gaa_note') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CHMSU STF (₱)</label>
                    <input type="number" step="0.01" name="funding_chmsu_stf"
                           x-model="stf" @input="calcTotal()" min="0"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Collaborator (₱)</label>
                    <input type="number" step="0.01" name="funding_collaborator"
                           x-model="collab" @input="calcTotal()" min="0"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Collaborator Note</label>
                    <input type="text" name="funding_collaborator_note" value="{{ $draft['details']['funding_collaborator_note'] ?? old('funding_collaborator_note') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total (₱)</label>
                    <input type="number" step="0.01" name="funding_total" x-model="total" readonly
                           class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-600">
                </div>
            </div>
            </div>
        </div>

        {{-- Section: Duration --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Duration</h3>
            </div>
            <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Start Date</label>
                    <input type="date" name="target_start_date" value="{{ $draft['details']['target_start_date'] ?? old('target_start_date') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target End Date</label>
                    <input type="date" name="target_end_date" value="{{ $draft['details']['target_end_date'] ?? old('target_end_date') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                </div>
            </div>
            </div>
        </div>

        {{-- Section: Program Leader & Members --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Program Leader & Members</h3>
            </div>
            <div class="p-6">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Program Leader</label>
                <input type="text" name="program_leader" value="{{ $draft['details']['program_leader'] ?? old('program_leader') }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
            </div>

            <label class="block text-sm font-medium text-gray-700 mb-2">Members</label>
            <template x-for="(member, index) in members" :key="index">
                <div class="flex gap-2 mb-2">
                    <input type="text" x-model="member.name" :name="'members['+index+'][name]'" placeholder="Name"
                           class="flex-1 px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                    <input type="text" x-model="member.responsibility" :name="'members['+index+'][responsibility]'" placeholder="Responsibility"
                           class="flex-1 px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                    <button type="button" @click="members.splice(index, 1)" class="px-2 text-red-400 hover:text-red-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <button type="button" @click="members.push({name:'', responsibility:''})"
                    class="text-sm text-academic-500 hover:text-academic-600 font-medium flex items-center gap-1 mt-1 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Member
            </button>
            </div>
        </div>

        {{-- Section: Program Details / Narrative --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Program Details</h3>
            </div>
            <div class="p-6">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rationale</label>
                    <textarea name="rationale" rows="4"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition resize-y">{{ $draft['details']['rationale'] ?? old('rationale') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">General Objective</label>
                    <textarea name="general_objective" rows="2"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition resize-y">{{ $draft['details']['general_objective'] ?? old('general_objective') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Specific Objectives</label>
                    <textarea name="specific_objectives" rows="3"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition resize-y">{{ $draft['details']['specific_objectives'] ?? old('specific_objectives') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Methodology</label>
                    <textarea name="methodology" rows="4"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition resize-y">{{ $draft['details']['methodology'] ?? old('methodology') }}</textarea>
                </div>
            </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between py-2">
            <a href="{{ route('proposal.wizard.upload', $type) }}"
               class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <div class="flex items-center gap-3">
                <button type="submit" formaction="{{ route('proposal.wizard.save-draft', $type) }}"
                        class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    Save Draft
                </button>
                <button type="submit"
                        class="px-6 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-medium rounded-lg shadow-sm transition inline-flex items-center gap-2">
                    Save and Continue
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function programDetailsForm() {
        return {
            gaa: {{ $draft['details']['funding_chmsu_gaa'] ?? old('funding_chmsu_gaa', 0) }},
            stf: {{ $draft['details']['funding_chmsu_stf'] ?? old('funding_chmsu_stf', 0) }},
            collab: {{ $draft['details']['funding_collaborator'] ?? old('funding_collaborator', 0) }},
            total: {{ ($draft['details']['funding_chmsu_gaa'] ?? 0) + ($draft['details']['funding_chmsu_stf'] ?? 0) + ($draft['details']['funding_collaborator'] ?? 0) }},
            members: @json($draft['details']['members'] ?? old('members', [['name' => '', 'responsibility' => '']])),
            calcTotal() {
                this.total = (parseFloat(this.gaa) || 0) + (parseFloat(this.stf) || 0) + (parseFloat(this.collab) || 0);
            }
        }
    }
</script>
@endsection



