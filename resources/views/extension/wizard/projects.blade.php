@extends('layouts.app')

@section('title', 'Submit Proposal — Add Projects')
@section('page-title', 'Submit Program Proposal')

@section('content')
<div class="max-w-4xl mx-auto" x-data="projectsForm()">

    {{-- Stepper --}}
    @include('extension.wizard._stepper', ['currentStep' => 4, 'type' => $type])

    <form method="POST" action="{{ route('proposal.wizard.save-projects', $type) }}">
        @csrf
        <input type="hidden" name="_current_step" value="4">

        {{-- Intro --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-academic-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-academic-heading">Add Projects Under This Program</h3>
                        <p class="text-xs text-gray-500">Optionally define projects that will be created under this program. You can also add projects later.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Project Cards --}}
        <template x-for="(project, index) in projects" :key="index">
            <div class="bg-white border border-gray-200 mb-4">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h4 class="text-sm font-bold text-academic-heading flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-blue-50 flex items-center justify-center text-xs font-bold text-academic-600" x-text="index + 1"></span>
                        <span>Project</span>
                    </h4>
                    <button type="button" @click="removeProject(index)" x-show="projects.length > 1"
                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>

                <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Title --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Project Title <span class="text-red-500">*</span></label>
                        <input type="text"
                               :name="'projects[' + index + '][title]'"
                               x-model="project.title"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition"
                               placeholder="Enter project title">
                    </div>

                    {{-- Description --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                        <textarea :name="'projects[' + index + '][description]'"
                                  x-model="project.description"
                                  rows="2"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition resize-none"
                                  placeholder="Brief description of the project"></textarea>
                    </div>

                    {{-- Persons Responsible --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Person(s) Responsible</label>
                        <input type="text"
                               :name="'projects[' + index + '][persons_responsible]'"
                               x-model="project.persons_responsible"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition"
                               placeholder="e.g. Dr. Juan Dela Cruz">
                    </div>

                    {{-- Budget --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Budget Requirement (₱)</label>
                        <input type="number" step="0.01" min="0"
                               :name="'projects[' + index + '][budget_requirement]'"
                               x-model="project.budget_requirement"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition"
                               placeholder="0.00">
                    </div>

                    {{-- Budget Source --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Budget Source</label>
                        <input type="text"
                               :name="'projects[' + index + '][budget_source]'"
                               x-model="project.budget_source"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition"
                               placeholder="e.g. CHMSU GAA, STF">
                    </div>

                    {{-- Placeholder for alignment --}}
                    <div class="hidden md:block"></div>

                    {{-- Start Date --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Target Start Date</label>
                        <input type="date"
                               :name="'projects[' + index + '][target_start_date]'"
                               x-model="project.target_start_date"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                    </div>

                    {{-- End Date --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Target End Date</label>
                        <input type="date"
                               :name="'projects[' + index + '][target_end_date]'"
                               x-model="project.target_end_date"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none transition">
                    </div>
                </div>
                </div>
            </div>
        </template>

        {{-- Add Project Button --}}
        <button type="button" @click="addProject()"
                class="w-full mb-6 px-4 py-3 border-2 border-dashed border-gray-300 hover:border-academic-500/50 rounded-lg text-sm font-medium text-gray-500 hover:text-academic-500 transition flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Another Project
        </button>

        {{-- Actions --}}
        <div class="flex items-center justify-between py-2">
            <a href="{{ route('proposal.wizard.details', $type) }}"
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
    function projectsForm() {
        const existing = @json($draftProjects);
        const blank = { title: '', description: '', persons_responsible: '', budget_requirement: '', budget_source: '', target_start_date: '', target_end_date: '' };

        return {
            projects: existing.length > 0 ? existing : [{ ...blank }],
            addProject() {
                this.projects.push({ ...blank });
            },
            removeProject(index) {
                this.projects.splice(index, 1);
            }
        }
    }
</script>
@endsection




