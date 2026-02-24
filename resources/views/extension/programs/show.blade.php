@extends('layouts.app')
@section('title', $program->title)
@section('page-title', 'Program Details')

@section('content')
@php
    use App\Services\WorkflowService;
    $phases      = WorkflowService::PHASES;
    $labels      = WorkflowService::PHASE_LABELS;
    $colors      = WorkflowService::PHASE_COLORS;
    $icons       = WorkflowService::PHASE_ICONS;
    $currentIdx  = WorkflowService::getPhaseIndex($program->status);
    $req         = WorkflowService::getRequirementsStatus($program);
    $nextPhase   = WorkflowService::getNextPhase($program->status);
    $formats     = WorkflowService::ALLOWED_FORMATS[$program->status] ?? [];
    $canUpload   = WorkflowService::canUserUpload($program->status, auth()->user()->role);
    $reqDocs     = WorkflowService::PROGRAM_REQUIRED_DOCS[$program->status] ?? [];
    $phaseDocs   = $program->statusDocuments->where('phase', $program->status);
    $allDocs     = $program->statusDocuments;
    $isAdmin     = auth()->user()->isAdmin();
@endphp

{{-- Breadcrumb --}}
<nav class="text-sm text-gray-500 mb-5 flex items-center gap-1">
    <a href="{{ route('extension.programs.index') }}" class="hover:text-blue-700">Programs</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-700 font-medium truncate max-w-xs">{{ $program->title }}</span>
</nav>

{{-- ===== WORKFLOW STEPPER ===== --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <div class="flex items-center justify-between overflow-x-auto">
        @foreach($phases as $i => $phase)
            @php
                $isCompleted = $i < $currentIdx;
                $isCurrent   = $i === $currentIdx;
                $isFuture    = $i > $currentIdx;
                $c = $colors[$phase];
            @endphp
            <div class="flex items-center {{ $i < count($phases) - 1 ? 'flex-1' : '' }} min-w-0">
                <div class="flex flex-col items-center min-w-[72px]">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all
                        {{ $isCompleted ? 'bg-green-500 text-white' : '' }}
                        {{ $isCurrent ? $c['bg'] . ' ' . $c['text'] . ' ring-2 ' . $c['ring'] : '' }}
                        {{ $isFuture ? 'bg-gray-100 text-gray-400' : '' }}">
                        @if($isCompleted)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>
                    <span class="mt-1.5 text-[10px] font-semibold whitespace-nowrap {{ $isCurrent ? $c['text'] : ($isCompleted ? 'text-green-600' : 'text-gray-400') }}">
                        {{ $labels[$phase] }}
                    </span>
                </div>
                @if($i < count($phases) - 1)
                    <div class="flex-1 h-0.5 mx-2 mt-[-16px] rounded {{ $isCompleted ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- Title Card --}}
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-xl font-bold text-gray-800">{{ $program->title }}</h1>
                @if($program->is_overdue)
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-700">Overdue</span>
                @endif
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $colors[$program->status]['bg'] }} {{ $colors[$program->status]['text'] }} {{ $colors[$program->status]['border'] }} border">
                    {{ $labels[$program->status] }}
                </span>
            </div>
            @if($program->ic_no)
                <p class="text-sm text-gray-500">I.C. No: {{ $program->ic_no }}</p>
            @endif
            <p class="text-sm text-gray-500">Campus: {{ $program->campus->name ?? '—' }}</p>
            <p class="text-xs text-gray-400 mt-1">Created by {{ $program->creator->name ?? '—' }} · {{ $program->created_at->format('M d, Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('extension.projects.create', ['program_id' => $program->id]) }}"
               class="inline-flex items-center gap-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Project
            </a>
            <a href="{{ route('extension.programs.edit', $program) }}"
               class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg border border-gray-200 transition">Edit</a>
            <form method="POST" action="{{ route('extension.programs.destroy', $program) }}" onsubmit="return confirmSubmit(event, 'Delete Program', 'This will permanently delete this program and all its projects.', 'danger', 'Delete')">
                @csrf @method('DELETE')
                <button class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold rounded-lg border border-red-200 transition">Delete</button>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- LEFT: Info panels --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Proponent & Location --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">Proponent & Location</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div><dt class="text-gray-400 text-xs">Proponent</dt><dd class="text-gray-700">{{ $program->proponent_name }}</dd></div>
                <div><dt class="text-gray-400 text-xs">Division/Unit</dt><dd class="text-gray-700">{{ $program->division_unit ?? '—' }}</dd></div>
                <div><dt class="text-gray-400 text-xs">Address</dt><dd class="text-gray-700">{{ $program->proponent_address ?? '—' }}</dd></div>
                <div><dt class="text-gray-400 text-xs">Contact No.</dt><dd class="text-gray-700">{{ $program->contact_no ?? '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-gray-400 text-xs">Program Location</dt><dd class="text-gray-700">{{ $program->program_location ?? '—' }}</dd></div>
            </dl>
        </div>

        @if($program->cooperating_entities)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">Cooperating Entity</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $program->cooperating_entities }}</p>
            @if($program->cooperating_entity_address)
                <p class="text-xs text-gray-500 mt-2">Address: {{ $program->cooperating_entity_address }}</p>
            @endif
        </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">Objectives</h2>
            @if($program->general_objective)
                <h3 class="text-xs font-medium text-gray-500 mb-1">General Objective</h3>
                <p class="text-sm text-gray-700 mb-4">{{ $program->general_objective }}</p>
            @endif
            @if($program->specific_objectives)
                <h3 class="text-xs font-medium text-gray-500 mb-1">Specific Objectives</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $program->specific_objectives }}</p>
            @endif
        </div>

        @if($program->rationale)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">Rationale</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $program->rationale }}</p>
        </div>
        @endif

        @if($program->methodology)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b border-gray-100">Methodology</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $program->methodology }}</p>
        </div>
        @endif

        {{-- Projects --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-700">Projects Under This Program ({{ $program->projects->count() }})</h2>
                <a href="{{ route('extension.projects.create', ['program_id' => $program->id]) }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Project
                </a>
            </div>
            @forelse($program->projects as $project)
                @php
                    $pc = $colors[$project->status] ?? $colors['proposal'];
                    $pLabel = $project->is_overdue ? 'Overdue' : $labels[$project->status];
                    $pColor = $project->is_overdue ? 'bg-red-100 text-red-700' : $pc['bg'] . ' ' . $pc['text'];
                @endphp
                <a href="{{ route('extension.projects.show', $project) }}" class="block px-6 py-4 hover:bg-gray-50 transition border-b border-gray-50 last:border-b-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $project->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $project->activities->count() }} activities · {{ $project->campus->name ?? '—' }}</p>
                        </div>
                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full {{ $pColor }}">{{ $pLabel }}</span>
                    </div>
                </a>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-400">
                    No projects yet. <a href="{{ route('extension.projects.create', ['program_id' => $program->id]) }}" class="text-blue-600 hover:underline">Add the first project</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- RIGHT SIDEBAR: Workflow Panel --}}
    <div class="space-y-6">

        {{-- ===== CURRENT PHASE PANEL ===== --}}
        <div class="bg-white rounded-xl border-2 {{ $colors[$program->status]['border'] }} overflow-hidden">
            <div class="px-5 py-3 {{ $colors[$program->status]['bg'] }} border-b {{ $colors[$program->status]['border'] }}">
                <h3 class="text-sm font-bold {{ $colors[$program->status]['text'] }} flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$program->status] }}"/></svg>
                    Current Phase: {{ $labels[$program->status] }}
                </h3>
            </div>
            <div class="p-5 space-y-4">

                {{-- Requirements Checklist --}}
                @if(count($req['fields']) || count($req['documents']))
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Requirements to Advance</h4>
                    <ul class="space-y-1.5">
                        @foreach($req['fields'] as $field => $info)
                        <li class="flex items-center gap-2 text-[13px]">
                            @if($info['met'])
                                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span class="text-gray-600 line-through">{{ $info['label'] }}</span>
                            @else
                                <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                <span class="text-gray-800 font-medium">{{ $info['label'] }}</span>
                            @endif
                        </li>
                        @endforeach
                        @foreach($req['documents'] as $docLabel => $info)
                        <li class="flex items-center gap-2 text-[13px]">
                            @if($info['met'])
                                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span class="text-gray-600 line-through">{{ $info['label'] }} (doc)</span>
                            @else
                                <svg class="w-4 h-4 text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <span class="text-gray-800 font-medium">{{ $info['label'] }} (upload)</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Advance Button --}}
                @if($nextPhase)
                    @if($workflowCheck['can_advance'])
                        <form method="POST" action="{{ route('workflow.advance', ['type' => 'program', 'id' => $program->id]) }}" onsubmit="return confirmSubmit(event, 'Advance Phase', 'Move this program to {{ $labels[$nextPhase] }} phase?', 'info', 'Advance')">
                            @csrf
                            <button class="w-full px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-lg transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                Advance to {{ $labels[$nextPhase] }}
                            </button>
                        </form>
                    @else
                        <button disabled class="w-full px-4 py-2.5 bg-gray-200 text-gray-400 text-[13px] font-semibold rounded-lg cursor-not-allowed flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Complete requirements to advance
                        </button>
                    @endif
                @else
                    <div class="text-center py-2">
                        <span class="inline-flex items-center gap-2 text-[13px] text-green-600 font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Program Completed
                        </span>
                    </div>
                @endif

                {{-- Admin Bypass --}}
                @if($isAdmin && $nextPhase)
                <div x-data="{ showBypass: false }">
                    <button @click="showBypass = !showBypass" class="w-full px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 text-[12px] font-medium rounded-lg border border-amber-200 transition flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Admin: Bypass Phase
                    </button>
                    <div x-show="showBypass" x-transition class="mt-3 p-3 bg-amber-50 rounded-lg border border-amber-200" x-cloak>
                        <form method="POST" action="{{ route('workflow.bypass', ['type' => 'program', 'id' => $program->id]) }}">
                            @csrf
                            <label class="block text-[12px] font-medium text-gray-700 mb-1">Skip to Phase</label>
                            <select name="target_phase" class="w-full text-[13px] border border-gray-300 rounded-md px-3 py-1.5 mb-2 focus:ring-2 focus:ring-blue-500 outline-none">
                                @foreach($phases as $i => $p)
                                    @if($i > $currentIdx)
                                        <option value="{{ $p }}">{{ $labels[$p] }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <label class="block text-[12px] font-medium text-gray-700 mb-1">Reason (min 10 chars)</label>
                            <textarea name="bypass_reason" required minlength="10" rows="2" class="w-full text-[13px] border border-gray-300 rounded-md px-3 py-1.5 mb-2 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Explain why this phase is being bypassed..."></textarea>
                            <button type="submit" class="w-full px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-[12px] font-semibold rounded-md transition">Bypass Phase</button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ===== DOCUMENT UPLOAD ===== --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    Phase Documents
                </h3>
            </div>
            <div class="p-5 space-y-4">
                {{-- Current phase docs --}}
                @if($phaseDocs->count())
                    <div class="space-y-2">
                        @foreach($phaseDocs as $doc)
                        <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg group">
                            <div class="w-8 h-8 rounded bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-[9px] font-bold text-blue-600 uppercase">{{ $doc->file_extension }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12px] font-medium text-gray-700 truncate">{{ $doc->original_name }}</p>
                                <p class="text-[10px] text-gray-400">{{ $doc->label }} · {{ $doc->human_file_size }} · {{ $doc->uploader->name ?? '' }}</p>
                            </div>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-blue-500 hover:text-blue-700 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>
                            @if($isAdmin || $doc->uploaded_by === auth()->id())
                            <form method="POST" action="{{ route('workflow.delete-document', $doc) }}" onsubmit="return confirmSubmit(event, 'Delete Document', 'Are you sure?', 'danger', 'Delete')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-600 p-1 opacity-0 group-hover:opacity-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif

                {{-- Upload form --}}
                @if($canUpload)
                <form method="POST" action="{{ route('workflow.upload-document', ['type' => 'program', 'id' => $program->id]) }}" enctype="multipart/form-data" class="space-y-2 pt-2 border-t border-gray-100">
                    @csrf
                    <select name="label" required class="w-full text-[13px] border border-gray-300 rounded-md px-3 py-1.5 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">Select document type...</option>
                        @foreach($reqDocs as $rl)
                            <option value="{{ $rl }}">{{ $rl }}</option>
                        @endforeach
                        <option value="Supporting Document">Supporting Document</option>
                        <option value="Other">Other</option>
                    </select>
                    <input type="file" name="document" required accept="{{ collect($formats)->map(fn($f) => '.' . $f)->implode(',') }}" class="w-full text-[12px] text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-[12px] file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[10px] text-gray-400">Allowed: {{ strtoupper(implode(', ', $formats)) }} · Max {{ (WorkflowService::MAX_FILE_SIZE[$program->status] ?? 10240) / 1024 }}MB</p>
                    <button type="submit" class="w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[12px] font-semibold rounded-md transition">Upload Document</button>
                </form>
                @else
                <p class="text-[12px] text-gray-400 italic">You don't have permission to upload in this phase.</p>
                @endif
            </div>
        </div>

        {{-- ===== ALL DOCUMENTS (collapsed) ===== --}}
        @if($allDocs->count())
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ open: false }">
            <button @click="open = !open" class="w-full px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    All Documents ({{ $allDocs->count() }})
                </h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition x-cloak class="px-5 pb-4 space-y-1.5">
                @foreach($phases as $p)
                    @php $docs = $allDocs->where('phase', $p); @endphp
                    @if($docs->count())
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-2">{{ $labels[$p] }}</p>
                        @foreach($docs as $doc)
                        <div class="flex items-center gap-2 py-1">
                            <span class="text-[9px] font-bold text-gray-400 uppercase bg-gray-100 rounded px-1.5 py-0.5">{{ $doc->file_extension }}</span>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-[12px] text-blue-600 hover:underline truncate">{{ $doc->original_name }}</a>
                            <span class="text-[10px] text-gray-400">{{ $doc->human_file_size }}</span>
                        </div>
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- ===== TRANSITION HISTORY ===== --}}
        @if($program->transitionLogs->count())
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ open: false }">
            <button @click="open = !open" class="w-full px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Transition History
                </h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition x-cloak class="px-5 pb-4">
                <div class="space-y-3 border-l-2 border-gray-200 pl-4 ml-1">
                    @foreach($program->transitionLogs->sortByDesc('created_at') as $log)
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full {{ $log->is_bypass ? 'bg-amber-400' : 'bg-blue-400' }}"></div>
                        <p class="text-[12px] text-gray-700">
                            <span class="font-semibold">{{ $labels[$log->from_status] ?? $log->from_status }}</span>
                            → <span class="font-semibold">{{ $labels[$log->to_status] ?? $log->to_status }}</span>
                            @if($log->is_bypass) <span class="text-amber-600 text-[10px] font-semibold">(BYPASS)</span> @endif
                        </p>
                        <p class="text-[10px] text-gray-400">{{ $log->transitioner->name ?? '—' }} · {{ $log->created_at->format('M d, Y H:i') }}</p>
                        @if($log->bypass_reason)
                            <p class="text-[11px] text-amber-600 mt-0.5">Reason: {{ $log->bypass_reason }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Quick Stats --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Quick Stats</h2>
            <div class="space-y-3">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Projects</span><span class="font-semibold text-gray-700">{{ $program->projects->count() }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Target Recipients</span><span class="font-semibold text-gray-700">{{ $program->target_recipients ?? '—' }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Total Funding</span><span class="font-semibold text-gray-700">₱{{ number_format($program->funding_total, 2) }}</span></div>
            </div>
        </div>

        {{-- Duration --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Duration</h2>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-500 text-xs">Start</span><p class="text-gray-700">{{ $program->target_start_date?->format('F Y') ?? '—' }}</p></div>
                <div><span class="text-gray-500 text-xs">End</span><p class="text-gray-700">{{ $program->target_end_date?->format('F Y') ?? '—' }}</p></div>
            </div>
        </div>

        {{-- Funding --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Funding Breakdown</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">CHMSU GAA</span><span class="text-gray-700">₱{{ number_format($program->funding_chmsu_gaa, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">CHMSU STF</span><span class="text-gray-700">₱{{ number_format($program->funding_chmsu_stf, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Collaborator</span><span class="text-gray-700">₱{{ number_format($program->funding_collaborator, 2) }}</span></div>
                <hr class="border-gray-100">
                <div class="flex justify-between font-semibold"><span class="text-gray-700">Total</span><span class="text-blue-700">₱{{ number_format($program->funding_total, 2) }}</span></div>
            </div>
        </div>

        {{-- Members --}}
        @if($program->members->count())
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Program Leader & Members</h2>
            @if($program->program_leader)
                <p class="text-sm text-gray-700 font-medium mb-2">{{ $program->program_leader }} <span class="text-xs text-gray-400">(Leader)</span></p>
            @endif
            <ul class="space-y-1.5">
                @foreach($program->members as $member)
                    <li class="text-sm text-gray-700">{{ $member->name }}@if($member->responsibility) <span class="text-xs text-gray-400">— {{ $member->responsibility }}</span>@endif</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
