@extends('layouts.app')

@section('title', 'Edit Evaluation Form')
@section('page-title', 'Edit Evaluation Form')

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

<div x-data="formEditor()" class="w-full max-w-3xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm mb-4" style="color: #5f6368;">
        <a href="{{ route('evaluation.forms.index') }}" class="hover:underline" style="color: #1a73e8;">Evaluation Forms</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('evaluation.forms.show', $form) }}" class="hover:underline" style="color: #1a73e8;">{{ Str::limit($form->title, 30) }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color: #202124; font-weight: 500;">Edit</span>
    </div>

    <form method="POST" action="{{ route('evaluation.forms.update', $form) }}">
        @csrf @method('PUT')

        {{-- Header Card --}}
        <div class="gf-card gf-header-card">
            <div style="margin-bottom: 20px;">
                <input type="text" name="title" value="{{ old('title', $form->title) }}" required
                       placeholder="Untitled form"
                       class="gf-input" style="font-size: 32px; font-weight: 400; color: #202124; border-bottom-width: 0; padding: 0 0 8px;">
                @error('title')<p style="font-size: 12px; color: #d93025; margin-top: 4px;">{{ $message }}</p>@enderror
            </div>

            <div>
                <input type="text" name="description" value="{{ old('description', $form->description) }}"
                       placeholder="Form description"
                       class="gf-input" style="font-size: 14px; color: #5f6368;">
            </div>
        </div>

        {{-- Program, Project & Status Card --}}
        <div class="gf-card">
            <div style="margin-bottom: 20px;">
                <p class="gf-label">Extension Program<span class="gf-required">*</span></p>
                <select name="extension_program_id" required class="gf-select"
                        x-model="selectedProgram" @change="loadProjects()">
                    <option value="">Select a program…</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ old('extension_program_id', $form->extension_program_id) == $program->id ? 'selected' : '' }}>
                            {{ $program->title }}
                        </option>
                    @endforeach
                </select>
                @error('extension_program_id')<p style="font-size: 12px; color: #d93025; margin-top: 4px;">{{ $message }}</p>@enderror
            </div>

            <div style="margin-bottom: 20px;">
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

            <label style="display: flex; align-items: center; gap: 12px; font-size: 14px; color: #202124; cursor: pointer;">
                <input type="hidden" name="is_active" value="0">
                <div style="position: relative; width: 36px; height: 20px;"
                     x-data="{ active: {{ old('is_active', $form->is_active) ? 'true' : 'false' }} }"
                     @click="active = !active">
                    <input type="checkbox" name="is_active" value="1" x-bind:checked="active" class="sr-only"
                           {{ old('is_active', $form->is_active) ? 'checked' : '' }}>
                    <div style="width: 36px; height: 14px; border-radius: 7px; position: absolute; top: 3px; transition: background 0.2s;"
                         :style="active ? 'background: #a8c7fa' : 'background: #dadce0'"></div>
                    <div style="width: 20px; height: 20px; border-radius: 50%; position: absolute; top: 0; transition: left 0.2s, background 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"
                         :style="active ? 'left: 16px; background: #1a73e8;' : 'left: 0; background: #fafafa;'"></div>
                </div>
                Form is active (accepting responses)
            </label>
        </div>

        {{-- Criteria Section --}}
        <div style="margin-top: 24px; margin-bottom: 16px;">
            <p style="font-size: 16px; color: #202124; font-weight: 400;">Evaluation Criteria</p>
            <p style="font-size: 12px; color: #70757a; margin-top: 2px;">Edit, add, or remove criteria. Rating items use a 1–5 Likert scale.</p>
        </div>

        <template x-for="(criterion, index) in criteria" :key="criterion._key">
            <div class="gf-criterion-card"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0">

                {{-- Hidden ID for existing criteria --}}
                <input type="hidden" :name="'criteria[' + index + '][id]'" :value="criterion.id || ''">

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
                    <div style="display: flex; align-items: center; justify-content: space-between;">
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
            <a href="{{ route('evaluation.forms.show', $form) }}" style="font-size: 14px; color: #1a73e8; text-decoration: none; font-weight: 500;">← Back to form</a>
            <button type="submit" class="gf-btn-primary">Save Changes</button>
        </div>
    </form>
</div>

@php
    $criteriaJson = $form->criteria->map(fn ($c) => [
        'id'          => $c->id,
        'label'       => $c->label,
        'type'        => $c->type,
        'is_required' => $c->is_required,
        '_key'        => $c->id,
    ])->values();
@endphp

<script>
function formEditor() {
    let nextKey = 1000;
    const existingCriteria = @json($criteriaJson);

    return {
        selectedProgram: '{{ old('extension_program_id', $form->extension_program_id) }}',
        selectedProject: '{{ old('extension_project_id', $form->extension_project_id) }}',
        projects: @json($projects),
        loadingProjects: false,
        criteria: existingCriteria.length ? existingCriteria : [{ id: null, label: '', type: 'rating', is_required: true, _key: nextKey++ }],
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
        addCriterion() {
            this.criteria.push({ id: null, label: '', type: 'rating', is_required: true, _key: nextKey++ });
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
