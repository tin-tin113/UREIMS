@extends('layouts.app')
@section('title', 'Edit: ' . $program->title)
@section('page-title', 'Edit Program')

@section('content')
<nav class="text-sm text-gray-500 mb-5 flex items-center gap-1">
    <a href="{{ route('extension.programs.index') }}" class="hover:text-academic-500 transition">Programs</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('extension.programs.show', $program) }}" class="hover:text-academic-500 transition truncate max-w-xs">{{ $program->title }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-academic-heading font-medium">Edit</span>
</nav>

@php
    $membersJson = $program->members->map(fn($m)=>['name'=>$m->name,'responsibility'=>$m->responsibility])->values()->toArray();
    $projectsJson = $program->projects->map(fn($p)=>['id'=>$p->id,'title'=>$p->title,'description'=>$p->description,'persons_responsible'=>$p->persons_responsible,'budget_requirement'=>$p->budget_requirement??'','budget_source'=>$p->budget_source??'','status'=>$p->status,'target_start_date'=>$p->target_start_date?->format('Y-m-d')??'','target_end_date'=>$p->target_end_date?->format('Y-m-d')??''])->values()->toArray();
@endphp

<form method="POST" action="{{ route('extension.programs.update', $program) }}" x-data="programEditForm()">
    @csrf @method('PUT')

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    {{-- Basic Info --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-academic-500 px-6 py-3"><h2 class="text-sm font-bold text-white">Basic Information</h2></div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Program Title <span class="text-red-500">*</span></label><input type="text" name="title" value="{{ old('title', $program->title) }}" required class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">I.C. No</label><input type="text" name="ic_no" value="{{ old('ic_no', $program->ic_no) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Campus <span class="text-red-500">*</span></label>
                    <select name="campus_id" required class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none">
                        <option value="">Select Campus</option>
                        @foreach($campuses as $campus)<option value="{{ $campus->id }}" {{ old('campus_id', $program->campus_id) == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>@endforeach
                    </select>
                </div>
                @if(auth()->user()->isAdmin())
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none">
                        @foreach(['proposal'=>'Proposal','ongoing'=>'Ongoing','completed'=>'Completed'] as $k=>$v)
                            <option value="{{ $k }}" {{ old('status', $program->status) === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Proponent & Location --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Proponent & Location</h2></div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Proponent Name <span class="text-red-500">*</span></label><input type="text" name="proponent_name" value="{{ old('proponent_name', $program->proponent_name) }}" required class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Division/Unit</label><input type="text" name="division_unit" value="{{ old('division_unit', $program->division_unit) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Address</label><input type="text" name="proponent_address" value="{{ old('proponent_address', $program->proponent_address) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Contact No.</label><input type="text" name="contact_no" value="{{ old('contact_no', $program->contact_no) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Program Location</label><input type="text" name="program_location" value="{{ old('program_location', $program->program_location) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
            </div>
        </div>
    </div>

    {{-- Cooperating Entity --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Cooperating Entity</h2></div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Cooperating Entities</label><textarea name="cooperating_entities" rows="2" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none">{{ old('cooperating_entities', $program->cooperating_entities) }}</textarea></div>
                <div class="md:col-span-2"><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Entity Address</label><input type="text" name="cooperating_entity_address" value="{{ old('cooperating_entity_address', $program->cooperating_entity_address) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
            </div>
        </div>
    </div>

    {{-- Beneficiaries --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Beneficiaries</h2></div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Beneficiary Class</label><input type="text" name="beneficiary_class" value="{{ old('beneficiary_class', $program->beneficiary_class) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Target Recipients</label><input type="text" name="target_recipients" value="{{ old('target_recipients', $program->target_recipients) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
            </div>
        </div>
    </div>

    {{-- Funding --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Funding</h2></div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">CHMSU GAA</label><input type="number" step="0.01" name="funding_chmsu_gaa" x-model.number="gaa" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">CHMSU STF</label><input type="number" step="0.01" name="funding_chmsu_stf" x-model.number="stf" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Collaborator</label><input type="number" step="0.01" name="funding_collaborator" x-model.number="collab" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
            </div>
            <div class="flex items-center gap-2 bg-academic-50 px-4 py-3 rounded"><span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Total Funding:</span><span class="text-sm font-bold text-academic-500" x-text="'₱' + (gaa + stf + collab).toLocaleString(undefined, {minimumFractionDigits:2})"></span></div>
        </div>
    </div>

    {{-- Duration --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Duration</h2></div>
        <div class="p-6"><div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Target Start Date</label><input type="date" name="target_start_date" value="{{ old('target_start_date', $program->target_start_date?->format('Y-m-d')) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
            <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Target End Date</label><input type="date" name="target_end_date" value="{{ old('target_end_date', $program->target_end_date?->format('Y-m-d')) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
        </div></div>
    </div>

    {{-- Leader & Members --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-academic-heading">Program Leader & Members</h2>
            <button type="button" @click="addMember()" class="text-xs text-academic-500 hover:text-academic-600 font-semibold flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Member</button>
        </div>
        <div class="p-6 space-y-4">
            <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Program Leader</label><input type="text" name="program_leader" value="{{ old('program_leader', $program->program_leader) }}" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
            <template x-for="(m, idx) in members" :key="idx">
                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded border border-gray-100">
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div><label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Name</label><input type="text" :name="'members['+idx+'][name]'" x-model="m.name" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                        <div><label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Responsibility</label><input type="text" :name="'members['+idx+'][responsibility]'" x-model="m.responsibility" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                    </div>
                    <button type="button" @click="removeMember(idx)" class="mt-5 p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
            </template>
        </div>
    </div>

    {{-- Program Details --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Program Details</h2></div>
        <div class="p-6 space-y-4">
            <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Rationale</label><textarea name="rationale" rows="4" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none leading-relaxed">{{ old('rationale', $program->rationale) }}</textarea></div>
            <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Conceptual Framework</label><textarea name="conceptual_framework" rows="3" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none leading-relaxed">{{ old('conceptual_framework', $program->conceptual_framework) }}</textarea></div>
            <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">General Objective</label><textarea name="general_objective" rows="3" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none leading-relaxed">{{ old('general_objective', $program->general_objective) }}</textarea></div>
            <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Specific Objectives</label><textarea name="specific_objectives" rows="4" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none leading-relaxed">{{ old('specific_objectives', $program->specific_objectives) }}</textarea></div>
            <div><label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Methodology</label><textarea name="methodology" rows="4" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none leading-relaxed">{{ old('methodology', $program->methodology) }}</textarea></div>
        </div>
    </div>

    {{-- Inline Projects --}}
    <div class="border border-gray-200 bg-white mb-6">
        <div class="bg-gray-50 px-6 py-3 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-academic-heading">Projects</h2>
            <button type="button" @click="addProject()" class="text-xs text-academic-500 hover:text-academic-600 font-semibold flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Project</button>
        </div>
        <div class="p-6 space-y-4">
            <template x-for="(p, idx) in projects" :key="idx">
                <div class="p-4 bg-gray-50 rounded border border-gray-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-academic-heading uppercase">Project #<span x-text="idx+1"></span></span>
                        <button type="button" @click="removeProject(idx)" class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <input type="hidden" :name="'projects['+idx+'][id]'" x-model="p.id">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="md:col-span-2"><label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Title</label><input type="text" :name="'projects['+idx+'][title]'" x-model="p.title" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                        <div class="md:col-span-2"><label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Description</label><textarea :name="'projects['+idx+'][description]'" x-model="p.description" rows="2" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></textarea></div>
                        <div><label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Persons Responsible</label><input type="text" :name="'projects['+idx+'][persons_responsible]'" x-model="p.persons_responsible" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                        <div><label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Budget Requirement</label><input type="number" step="0.01" :name="'projects['+idx+'][budget_requirement]'" x-model="p.budget_requirement" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                        <div><label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Budget Source</label><input type="text" :name="'projects['+idx+'][budget_source]'" x-model="p.budget_source" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                        <div><label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                            <select :name="'projects['+idx+'][status]'" x-model="p.status" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 outline-none"><option value="proposal">Proposal</option><option value="ongoing">Ongoing</option><option value="completed">Completed</option></select>
                        </div>
                        <div><label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Target Start</label><input type="date" :name="'projects['+idx+'][target_start_date]'" x-model="p.target_start_date" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                        <div><label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Target End</label><input type="date" :name="'projects['+idx+'][target_end_date]'" x-model="p.target_end_date" class="w-full border border-gray-200 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none"></div>
                    </div>
                </div>
            </template>
            <div x-show="projects.length === 0" class="text-center py-6 text-sm text-gray-400">No projects. Click "Add Project" above.</div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('extension.programs.show', $program) }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded border border-gray-200 transition">Cancel</a>
        <button type="submit" class="px-6 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-semibold rounded transition">Update Program</button>
    </div>
</form>

<script>
function programEditForm() {
    return {
        gaa: {{ old('funding_chmsu_gaa', $program->funding_chmsu_gaa ?? 0) }},
        stf: {{ old('funding_chmsu_stf', $program->funding_chmsu_stf ?? 0) }},
        collab: {{ old('funding_collaborator', $program->funding_collaborator ?? 0) }},
        members: @json(old('members', $membersJson)),
        projects: @json(old('projects', $projectsJson)),
        addMember() { this.members.push({name:'',responsibility:''}); },
        removeMember(i) { this.members.splice(i,1); },
        addProject() { this.projects.push({id:'',title:'',description:'',persons_responsible:'',budget_requirement:'',budget_source:'',status:'proposal',target_start_date:'',target_end_date:''}); },
        removeProject(i) { this.projects.splice(i,1); }
    }
}
</script>
@endsection
