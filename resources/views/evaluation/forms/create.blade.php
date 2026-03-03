@extends('layouts.app')

@section('title', 'Create Evaluation Form')
@section('page-title', 'Create Evaluation Form')

@section('content')
<style>
    .gf-card { background: #fff; border: 1px solid #dadce0; border-radius: 8px; padding: 20px; margin-bottom: 12px; }
    @media (min-width: 640px) { .gf-card { padding: 28px 32px; } }
    .gf-header-card { border-top: 10px solid #1a73e8; }
    .gf-label { font-size: 14px; color: #202124; display: block; margin-bottom: 8px; }
    .gf-input { border: none; border-bottom: 1px solid #dadce0; border-radius: 0; padding: 8px 0; font-size: 14px; width: 100%; outline: none; transition: border-color 0.2s; background: transparent; }
    .gf-input:focus { border-bottom: 2px solid #1a73e8; margin-bottom: -1px; }
    .gf-select { width: 100%; padding: 12px; border: 1px solid #dadce0; border-radius: 4px; font-size: 14px; background: #fff; outline: none; cursor: pointer; }
    .gf-select:focus { border-color: #1a73e8; box-shadow: 0 0 0 1px #1a73e8; }
    .gf-textarea { border: 1px solid #dadce0; border-radius: 4px; padding: 12px; font-size: 14px; width: 100%; min-height: 80px; resize: vertical; outline: none; font-family: inherit; }
    .gf-textarea:focus { border-color: #1a73e8; box-shadow: 0 0 0 1px #1a73e8; }
    .gf-required { color: #d93025; margin-left: 4px; }
    .gf-btn-primary { background: #1a73e8; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; letter-spacing: 0.25px; }
    .gf-btn-primary:hover { background: #1557b0; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
    .gf-btn-secondary { background: transparent; color: #1a73e8; border: none; padding: 8px 16px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; }
    .gf-btn-secondary:hover { background: #e8f0fe; }
    .gf-criterion-card { border: 1px solid #dadce0; border-radius: 8px; padding: 20px; background: #fff; margin-bottom: 12px; transition: border-color 0.2s, box-shadow 0.2s; }
    .gf-criterion-card:hover { border-color: #1a73e8; }
    .gf-criterion-card:focus-within { border-left: 6px solid #1a73e8; }
    .gf-chip-select { padding: 8px 12px; border: 1px solid #dadce0; border-radius: 4px; font-size: 13px; background: #fff; outline: none; cursor: pointer; }
    .gf-chip-select:focus { border-color: #1a73e8; }
</style>

<div x-data="formBuilder()" class="w-full max-w-3xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm mb-4" style="color: #5f6368;">
        <a href="{{ route('evaluation.forms.index') }}" class="hover:underline" style="color: #1a73e8;">Evaluation Forms</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color: #202124; font-weight: 500;">Create</span>
    </div>

    <form method="POST" action="{{ route('evaluation.forms.store') }}">
        @csrf

        {{-- Header Card --}}
        <div class="gf-card gf-header-card">
            <div style="margin-bottom: 20px;">
                <input type="text" name="title" value="{{ old('title') }}" required
                       placeholder="Untitled form"
                       class="gf-input" style="font-size: 32px; font-weight: 400; color: #202124; border-bottom-width: 0; padding: 0 0 8px;">
                @error('title')<p style="font-size: 12px; color: #d93025; margin-top: 4px;">{{ $message }}</p>@enderror
            </div>

            <div>
                <input type="text" name="description" value="{{ old('description') }}"
                       placeholder="Form description"
                       class="gf-input" style="font-size: 14px; color: #5f6368;">
            </div>
        </div>

        {{-- Program & Project Selection Card --}}
        <div class="gf-card">
            <div style="margin-bottom: 20px;">
                <p class="gf-label">Extension Program<span class="gf-required">*</span></p>
                <select name="extension_program_id" required class="gf-select"
                        x-model="selectedProgram" @change="loadProjects()">
                    <option value="">Select a program…</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ old('extension_program_id', $selectedProgram) == $program->id ? 'selected' : '' }}>
                            {{ $program->title }}
                        </option>
                    @endforeach
                </select>
                @error('extension_program_id')<p style="font-size: 12px; color: #d93025; margin-top: 4px;">{{ $message }}</p>@enderror
            </div>

            <div>
                <p class="gf-label">Extension Project <span style="font-size: 12px; color: #70757a;">(optional)</span></p>
                <select name="extension_project_id" class="gf-select"
                        x-model="selectedProject" :disabled="!selectedProgram || loadingProjects">
                    <option value="">All projects / Not specific to a project</option>
                    <template x-for="project in projects" :key="project.id">
                        <option :value="project.id" x-text="project.title"
                                :selected="project.id == selectedProject"></option>
                    </template>
                </select>
                <p x-show="loadingProjects" style="font-size: 12px; color: #70757a; margin-top: 4px;">Loading projects…</p>
                <p x-show="!loadingProjects && selectedProgram && projects.length === 0" style="font-size: 12px; color: #70757a; margin-top: 4px;">No projects found for this program.</p>
                @error('extension_project_id')<p style="font-size: 12px; color: #d93025; margin-top: 4px;">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Use Existing Form as Template --}}
        @if($templateForms->count())
        <div class="gf-card" style="border-left: 4px solid #fbbc04;">
            <div style="display: flex; align-items: start; gap: 12px; margin-bottom: 12px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fbbc04" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                <div>
                    <p style="font-size: 14px; color: #202124; font-weight: 500; margin: 0;">Reuse an existing form</p>
                    <p style="font-size: 12px; color: #70757a; margin: 4px 0 0;">Load criteria from a previous evaluation form as a starting point.</p>
                </div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <select x-model="selectedTemplate" class="gf-select" style="flex: 1; min-width: 200px;">
                    <option value="">Start from scratch</option>
                    @foreach($templateForms as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->title }} ({{ $tpl->criteria_count }} criteria)</option>
                    @endforeach
                </select>
                <button type="button" @click="loadTemplate()" :disabled="!selectedTemplate || loadingTemplate"
                        class="gf-btn-secondary"
                        style="display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; padding: 10px 16px; border: 1px solid #dadce0; border-radius: 4px;"
                        :style="selectedTemplate ? '' : 'opacity: 0.5; cursor: default;'">
                    <svg x-show="!loadingTemplate" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <svg x-show="loadingTemplate" x-cloak width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    <span x-text="loadingTemplate ? 'Loading…' : 'Load Criteria'"></span>
                </button>
            </div>
            <p x-show="templateLoaded" x-cloak x-transition
               style="font-size: 12px; color: #2e7d32; margin-top: 8px; display: flex; align-items: center; gap: 4px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Criteria loaded from template. You can edit them below before creating.
            </p>
        </div>
        @endif

        {{-- Criteria Section --}}
        <div style="margin-top: 24px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <p style="font-size: 16px; color: #202124; font-weight: 400;">Evaluation Criteria</p>
                <p style="font-size: 12px; color: #70757a; margin-top: 2px;">Rating items use a 1–5 Likert scale.</p>
            </div>
        </div>

        <template x-for="(criterion, index) in criteria" :key="index">
            <div class="gf-criterion-card"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0">

                <div style="display: flex; gap: 16px; margin-bottom: 16px;" class="flex-col sm:flex-row sm:items-start">
                    <div style="flex: 1;">
                        <input type="text" :name="'criteria[' + index + '][label]'" x-model="criterion.label" required
                               placeholder="Question"
                               class="gf-input" style="font-size: 15px;">
                    </div>

                    <select :name="'criteria[' + index + '][type]'" x-model="criterion.type" class="gf-chip-select">
                        <option value="rating">⭐ Rating (1-5)</option>
                        <option value="text">📝 Text</option>
                    </select>
                </div>

                {{-- Preview --}}
                <div x-show="criterion.type === 'rating'" style="padding: 12px 0 8px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0;">
                        <template x-for="n in 5" :key="n">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 6px; flex: 1; opacity: 0.5;">
                                <span style="font-size: 13px; color: #5f6368;" x-text="n"></span>
                                <span style="width: 20px; height: 20px; border-radius: 50%; border: 2px solid #5f6368; display: block;"></span>
                            </div>
                        </template>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0 0; font-size: 11px; color: #70757a; opacity: 0.5;">
                        <span>Poor</span><span>Excellent</span>
                    </div>
                </div>
                <div x-show="criterion.type === 'text'" style="padding: 12px 0 8px;">
                    <div style="border-bottom: 1px dotted #dadce0; padding: 8px 0; font-size: 14px; color: #bdc1c6; opacity: 0.5;">Long answer text</div>
                </div>

                {{-- Bottom toolbar --}}
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 16px; padding-top: 16px; border-top: 1px solid #f1f3f4; margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #5f6368; cursor: pointer;">
                        <input type="hidden" :name="'criteria[' + index + '][is_required]'" value="0">
                        <span>Required</span>
                        <div style="position: relative; width: 36px; height: 20px;"
                             @click="criterion.is_required = !criterion.is_required">
                            <input type="checkbox" :name="'criteria[' + index + '][is_required]'" value="1"
                                   x-model="criterion.is_required" class="sr-only">
                            <div style="width: 36px; height: 14px; border-radius: 7px; position: absolute; top: 3px; transition: background 0.2s;"
                                 :style="criterion.is_required ? 'background: #a8c7fa' : 'background: #dadce0'"></div>
                            <div style="width: 20px; height: 20px; border-radius: 50%; position: absolute; top: 0; transition: left 0.2s, background 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"
                                 :style="criterion.is_required ? 'left: 16px; background: #1a73e8;' : 'left: 0; background: #fafafa;'"></div>
                        </div>
                    </label>

                    <button type="button" @click="removeCriterion(index)"
                            x-show="criteria.length > 1"
                            style="padding: 8px; color: #5f6368; border: none; background: none; cursor: pointer; border-radius: 50%; transition: background 0.2s;"
                            onmouseover="this.style.background='#f1f3f4'" onmouseout="this.style.background='none'"
                            title="Delete">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </template>

        {{-- Add Criterion --}}
        <div style="text-align: center; padding: 8px 0 24px;">
            <button type="button" @click="addCriterion()" class="gf-btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1a73e8" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add question
            </button>
        </div>

        {{-- Actions --}}
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0;">
            <a href="{{ route('evaluation.forms.index') }}" style="font-size: 14px; color: #1a73e8; text-decoration: none; font-weight: 500;">← Back to list</a>
            <button type="submit" class="gf-btn-primary">Create Form</button>
        </div>
    </form>
</div>

<script>
function formBuilder() {
    return {
        selectedProgram: '{{ old('extension_program_id', $selectedProgram) }}',
        selectedProject: '{{ old('extension_project_id', $selectedProject) }}',
        projects: @json($projects),
        loadingProjects: false,
        selectedTemplate: '',
        loadingTemplate: false,
        templateLoaded: false,
        criteria: [
            { label: '', type: 'rating', is_required: true }
        ],
        init() {
            // If a program is pre-selected and no projects loaded yet, load them
            if (this.selectedProgram && this.projects.length === 0) {
                this.loadProjects();
            }
        },
        async loadProjects() {
            this.selectedProject = '';
            this.projects = [];
            if (!this.selectedProgram) return;

            this.loadingProjects = true;
            try {
                const response = await fetch(`/evaluation/projects-by-program/${this.selectedProgram}`);
                this.projects = await response.json();
            } catch (e) {
                console.error('Failed to load projects', e);
            } finally {
                this.loadingProjects = false;
            }
        },
        async loadTemplate() {
            if (!this.selectedTemplate) return;

            this.loadingTemplate = true;
            this.templateLoaded = false;
            try {
                const response = await fetch(`/evaluation/forms/${this.selectedTemplate}/criteria`);
                const data = await response.json();

                // Populate title and description if still empty
                const titleInput = document.querySelector('input[name="title"]');
                const descInput = document.querySelector('input[name="description"]');
                if (titleInput && !titleInput.value.trim()) {
                    titleInput.value = data.title || '';
                }
                if (descInput && !descInput.value.trim()) {
                    descInput.value = data.description || '';
                }

                // Load criteria
                if (data.criteria && data.criteria.length > 0) {
                    this.criteria = data.criteria.map(c => ({
                        label: c.label,
                        type: c.type,
                        is_required: c.is_required
                    }));
                }

                this.templateLoaded = true;
            } catch (e) {
                console.error('Failed to load template', e);
            } finally {
                this.loadingTemplate = false;
            }
        },
        addCriterion() {
            this.criteria.push({ label: '', type: 'rating', is_required: true });
        },
        removeCriterion(index) {
            if (this.criteria.length > 1) {
                this.criteria.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
