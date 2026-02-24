@extends('layouts.app')

@section('title', 'Create Program')
@section('page-title', 'Create Extension Program')

@section('content')

    <div class="max-w-4xl">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 mb-6 flex items-center gap-1">
            <a href="{{ route('extension.programs.index') }}" class="hover:text-green-700">Programs</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700 font-medium">New Program</span>
        </nav>

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

        <form method="POST" action="{{ route('extension.programs.store') }}" x-data="programForm()" onsubmit="return confirmSubmit(event, 'Create Program', 'Are you sure you want to create this extension program?', 'info', 'Create')">
            @csrf

            {{-- Section: Basic Information --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Basic Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Program Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">I.C. Number</label>
                        <input type="text" name="ic_no" value="{{ old('ic_no') }}" placeholder="CHMSU-ECS-XXXX-XXX-XXX"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            <option value="proposal" {{ old('status', 'proposal') === 'proposal' ? 'selected' : '' }}>Proposal</option>
                            <option value="ongoing" {{ old('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Campus <span class="text-red-500">*</span></label>
                        <select name="campus_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            <option value="">Select Campus</option>
                            @foreach($campuses as $campus)
                                <option value="{{ $campus->id }}" {{ old('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section: Proponent & Location --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Proponent & Location</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Program Proponent <span class="text-red-500">*</span></label>
                        <input type="text" name="proponent_name" value="{{ old('proponent_name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Division/Unit (College)</label>
                        <input type="text" name="division_unit" value="{{ old('division_unit') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Proponent Address</label>
                        <input type="text" name="proponent_address" value="{{ old('proponent_address') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                        <input type="text" name="contact_no" value="{{ old('contact_no') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Program Location</label>
                        <input type="text" name="program_location" value="{{ old('program_location') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                </div>
            </div>

            {{-- Section: Cooperating Entity --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Cooperating Entity</h2>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name of Institutions</label>
                        <textarea name="cooperating_entities" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">{{ old('cooperating_entities') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input type="text" name="cooperating_entity_address" value="{{ old('cooperating_entity_address') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                </div>
            </div>

            {{-- Section: Beneficiaries --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Beneficiaries</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Beneficiary Class</label>
                        <textarea name="beneficiary_class" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">{{ old('beneficiary_class') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Number of Target Recipients</label>
                        <input type="number" name="target_recipients" value="{{ old('target_recipients') }}" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                </div>
            </div>

            {{-- Section: Funding --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Resource/Funding Requirement</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CHMSU GAA (₱)</label>
                        <input type="number" step="0.01" name="funding_chmsu_gaa" value="{{ old('funding_chmsu_gaa', 0) }}" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                               x-model="gaa" @input="calcTotal()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">GAA Note</label>
                        <input type="text" name="funding_chmsu_gaa_note" value="{{ old('funding_chmsu_gaa_note') }}" placeholder="e.g. 100,000.00 per barangay"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CHMSU STF (₱)</label>
                        <input type="number" step="0.01" name="funding_chmsu_stf" value="{{ old('funding_chmsu_stf', 0) }}" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                               x-model="stf" @input="calcTotal()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Collaborator (₱)</label>
                        <input type="number" step="0.01" name="funding_collaborator" value="{{ old('funding_collaborator', 0) }}" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                               x-model="collab" @input="calcTotal()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Collaborator Note</label>
                        <input type="text" name="funding_collaborator_note" value="{{ old('funding_collaborator_note') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total (₱)</label>
                        <input type="number" step="0.01" name="funding_total" x-model="total" readonly
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-600">
                    </div>
                </div>
            </div>

            {{-- Section: Duration --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Duration</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Start Date</label>
                        <input type="date" name="target_start_date" value="{{ old('target_start_date') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target End Date</label>
                        <input type="date" name="target_end_date" value="{{ old('target_end_date') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    </div>
                </div>
            </div>

            {{-- Section: Program Leader & Members --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Program Leader & Members</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program Leader</label>
                    <input type="text" name="program_leader" value="{{ old('program_leader') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                </div>

                <label class="block text-sm font-medium text-gray-700 mb-2">Members</label>
                <template x-for="(member, index) in members" :key="index">
                    <div class="flex gap-2 mb-2">
                        <input type="text" x-model="member.name" :name="'members['+index+'][name]'" placeholder="Name"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                        <input type="text" x-model="member.responsibility" :name="'members['+index+'][responsibility]'" placeholder="Responsibility"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                        <button type="button" @click="members.splice(index, 1)" class="px-2 text-red-400 hover:text-red-600" title="Remove">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                <button type="button" @click="members.push({name:'', responsibility:''})"
                        class="text-sm text-green-600 hover:text-green-800 font-medium flex items-center gap-1 mt-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Member
                </button>
            </div>

            {{-- Section: Narrative --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Program Details</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rationale</label>
                        <textarea name="rationale" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">{{ old('rationale') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">General Objective</label>
                        <textarea name="general_objective" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">{{ old('general_objective') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Specific Objectives</label>
                        <textarea name="specific_objectives" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">{{ old('specific_objectives') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Methodology</label>
                        <textarea name="methodology" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">{{ old('methodology') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Section: Projects Under This Program --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-700">Projects <span class="text-xs font-normal text-gray-400">(optional)</span></h2>
                    <button type="button" @click="projects.push({title:'', description:'', persons_responsible:'', budget_requirement:0, budget_source:'', target_start_date:'', target_end_date:'', status:'proposal'})"
                            class="text-sm text-green-600 hover:text-green-800 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Project
                    </button>
                </div>

                <template x-if="projects.length === 0">
                    <p class="text-sm text-gray-400 text-center py-4">No projects added yet. Click "Add Project" to include projects under this program.</p>
                </template>

                <template x-for="(proj, pi) in projects" :key="pi">
                    <div class="border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50/50">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-gray-600" x-text="'Project #' + (pi + 1)"></span>
                            <button type="button" @click="projects.splice(pi, 1)" class="text-red-400 hover:text-red-600" title="Remove project">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Project Title *</label>
                                <input type="text" x-model="proj.title" :name="'projects['+pi+'][title]'" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                                <textarea x-model="proj.description" :name="'projects['+pi+'][description]'" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Persons Responsible</label>
                                <input type="text" x-model="proj.persons_responsible" :name="'projects['+pi+'][persons_responsible]'"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                                <select x-model="proj.status" :name="'projects['+pi+'][status]'"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                                    <option value="proposal">Proposal</option>
                                    <option value="ongoing">Ongoing</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Budget (₱)</label>
                                <input type="number" step="0.01" min="0" x-model="proj.budget_requirement" :name="'projects['+pi+'][budget_requirement]'"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Budget Source</label>
                                <input type="text" x-model="proj.budget_source" :name="'projects['+pi+'][budget_source]'"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Start Date</label>
                                <input type="date" x-model="proj.target_start_date" :name="'projects['+pi+'][target_start_date]'"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">End Date</label>
                                <input type="date" x-model="proj.target_end_date" :name="'projects['+pi+'][target_end_date]'"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-2.5 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold rounded-lg transition shadow-sm">
                    Create Program
                </button>
                <a href="{{ route('extension.programs.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        function programForm() {
            return {
                gaa: {{ old('funding_chmsu_gaa', 0) }},
                stf: {{ old('funding_chmsu_stf', 0) }},
                collab: {{ old('funding_collaborator', 0) }},
                total: {{ old('funding_total', 0) }},
                members: @json(old('members', [['name' => '', 'responsibility' => '']])),
                projects: @json(old('projects', [])),
                calcTotal() {
                    this.total = (parseFloat(this.gaa) || 0) + (parseFloat(this.stf) || 0) + (parseFloat(this.collab) || 0);
                }
            }
        }
    </script>

@endsection
