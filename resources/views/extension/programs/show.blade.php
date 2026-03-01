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
    $canUpload   = auth()->user()->isAdmin() || $program->created_by === auth()->id();
    $reqDocs     = WorkflowService::PROGRAM_REQUIRED_DOCS[$program->status] ?? [];
    $phaseDocs   = $program->statusDocuments->where('phase', $program->status);
    $allDocs     = $program->statusDocuments;
    $isAdmin     = auth()->user()->isAdmin();
@endphp

{{-- Breadcrumb --}}
<nav class="text-sm text-gray-500 mb-5 flex items-center gap-1">
    <a href="{{ route('extension.programs.index') }}" class="hover:text-academic-500 transition">Programs</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-academic-heading font-medium truncate max-w-xs">{{ $program->title }}</span>
</nav>

{{-- ===== WORKFLOW STEPPER ===== --}}
<div class="border border-gray-200 bg-white mb-6">
    <div class="bg-academic-500 px-6 py-2.5">
        <h2 class="text-xs font-bold text-white uppercase tracking-wider">Workflow Progress</h2>
    </div>
    <div class="px-6 py-4">
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
                        <span class="mt-1.5 text-xs font-semibold whitespace-nowrap {{ $isCurrent ? $c['text'] : ($isCompleted ? 'text-green-600' : 'text-gray-400') }}">
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
</div>

{{-- Title Card --}}
<div class="border border-gray-200 bg-white mb-6">
    <div class="bg-academic-500 px-6 py-3">
        <h2 class="text-sm font-bold text-white">Program Overview</h2>
    </div>
    <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-xl font-bold text-academic-heading">{{ $program->title }}</h1>
                    @if($program->is_overdue)
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-red-100 text-red-700">Overdue</span>
                    @endif
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded {{ $colors[$program->status]['bg'] }} {{ $colors[$program->status]['text'] }}">
                        {{ $labels[$program->status] }}
                    </span>
                </div>
                @if($program->ic_no)
                    <p class="text-sm text-gray-600">I.C. No: <span class="font-medium">{{ $program->ic_no }}</span></p>
                @endif
                <p class="text-sm text-gray-600">Campus: <span class="font-medium">{{ $program->campus->name ?? '—' }}</span></p>
                <p class="text-xs text-gray-400 mt-1">Created by {{ $program->creator->name ?? '—' }} · {{ $program->created_at->format('M d, Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('extension.projects.create', ['program_id' => $program->id]) }}" class="inline-flex items-center gap-1 px-3 py-2 bg-academic-500 hover:bg-academic-600 text-white text-xs font-semibold rounded transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Project
                </a>
                <a href="{{ route('extension.programs.edit', $program) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded border border-gray-200 transition">Edit</a>
                <form method="POST" action="{{ route('extension.programs.destroy', $program) }}" onsubmit="return confirmSubmit(event, 'Delete Program', 'This will permanently delete this program and all its projects.', 'danger', 'Delete')">
                    @csrf @method('DELETE')
                    <button class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold rounded border border-red-200 transition">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- LEFT: Info panels --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Proponent & Location --}}
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                <h2 class="text-sm font-bold text-academic-heading">Proponent & Location</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500 text-xs font-medium uppercase tracking-wider">Proponent</dt><dd class="text-gray-800 mt-0.5">{{ $program->proponent_name }}</dd></div>
                    <div><dt class="text-gray-500 text-xs font-medium uppercase tracking-wider">Division/Unit</dt><dd class="text-gray-800 mt-0.5">{{ $program->division_unit ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs font-medium uppercase tracking-wider">Address</dt><dd class="text-gray-800 mt-0.5">{{ $program->proponent_address ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs font-medium uppercase tracking-wider">Contact No.</dt><dd class="text-gray-800 mt-0.5">{{ $program->contact_no ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500 text-xs font-medium uppercase tracking-wider">Program Location</dt><dd class="text-gray-800 mt-0.5">{{ $program->program_location ?? '—' }}</dd></div>
                </dl>
            </div>
        </div>

        @if($program->cooperating_entities)
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                <h2 class="text-sm font-bold text-academic-heading">Cooperating Entity</h2>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $program->cooperating_entities }}</p>
                @if($program->cooperating_entity_address)
                    <p class="text-xs text-gray-500 mt-2">Address: {{ $program->cooperating_entity_address }}</p>
                @endif
            </div>
        </div>
        @endif

        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                <h2 class="text-sm font-bold text-academic-heading">Objectives</h2>
            </div>
            <div class="p-6">
                @if($program->general_objective)
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">General Objective</h3>
                    <p class="text-sm text-gray-700 mb-4 leading-relaxed">{{ $program->general_objective }}</p>
                @endif
                @if($program->specific_objectives)
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Specific Objectives</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $program->specific_objectives }}</p>
                @endif
            </div>
        </div>

        @if($program->rationale)
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Rationale</h2></div>
            <div class="p-6"><p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $program->rationale }}</p></div>
        </div>
        @endif

        @if($program->methodology)
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Methodology</h2></div>
            <div class="p-6"><p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $program->methodology }}</p></div>
        </div>
        @endif

        {{-- Projects --}}
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-sm font-bold text-academic-heading">Projects Under This Program ({{ $program->projects->count() }})</h2>
                <a href="{{ route('extension.projects.create', ['program_id' => $program->id]) }}" class="text-xs text-academic-500 hover:text-academic-600 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Project
                </a>
            </div>
            @forelse($program->projects as $project)
                @php
                    $pc = $colors[$project->status] ?? $colors['proposal'];
                    $pLabel = $project->is_overdue ? 'Overdue' : $labels[$project->status];
                    $pColor = $project->is_overdue ? 'bg-red-100 text-red-700' : $pc['bg'] . ' ' . $pc['text'];
                @endphp
                <a href="{{ route('extension.projects.show', $project) }}" class="block px-6 py-4 hover:bg-academic-50/30 transition border-b border-gray-100 last:border-b-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-academic-heading">{{ $project->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $project->activities->count() }} activities · {{ $project->campus->name ?? '—' }}</p>
                        </div>
                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded {{ $pColor }}">{{ $pLabel }}</span>
                    </div>
                </a>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-400">
                    No projects yet. <a href="{{ route('extension.projects.create', ['program_id' => $program->id]) }}" class="text-academic-500 hover:underline">Add the first project</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- RIGHT SIDEBAR --}}
    <div class="space-y-6">

        {{-- Current Phase Panel --}}
        <div class="border-2 {{ $colors[$program->status]['border'] }} bg-white overflow-hidden">
            <div class="px-5 py-3 {{ $colors[$program->status]['bg'] }} border-b {{ $colors[$program->status]['border'] }}">
                <h3 class="text-sm font-bold {{ $colors[$program->status]['text'] }} flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$program->status] }}"/></svg>
                    Current Phase: {{ $labels[$program->status] }}
                </h3>
            </div>
            <div class="p-5 space-y-4">
                @if(count($req['fields']) || count($req['documents']))
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Requirements to Advance</h4>
                    <ul class="space-y-1.5">
                        @foreach($req['fields'] as $field => $info)
                        <li class="flex items-center gap-2 text-sm">
                            @if($info['met'])
                                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span class="text-gray-500 line-through">{{ $info['label'] }}</span>
                            @else
                                <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                <span class="text-gray-800 font-medium">{{ $info['label'] }}</span>
                            @endif
                        </li>
                        @endforeach
                        @foreach($req['documents'] as $docLabel => $info)
                        <li class="flex items-center gap-2 text-sm">
                            @if($info['met'])
                                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span class="text-gray-500 line-through">{{ $info['label'] }} (doc)</span>
                            @else
                                <svg class="w-4 h-4 text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <span class="text-gray-800 font-medium">{{ $info['label'] }} (upload)</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if($nextPhase)
                    @if($workflowCheck['can_advance'])
                        <form method="POST" action="{{ route('workflow.advance', ['type' => 'program', 'id' => $program->id]) }}" onsubmit="return confirmSubmit(event, 'Advance Phase', 'Move this program to {{ $labels[$nextPhase] }} phase?', 'info', 'Advance')">
                            @csrf
                            <button class="w-full px-4 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-semibold rounded transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                Advance to {{ $labels[$nextPhase] }}
                            </button>
                        </form>
                    @else
                        <button disabled class="w-full px-4 py-2.5 bg-gray-200 text-gray-400 text-sm font-semibold rounded cursor-not-allowed flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Complete requirements to advance
                        </button>
                    @endif
                @else
                    <div class="text-center py-2">
                        <span class="inline-flex items-center gap-2 text-sm text-green-600 font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Program Completed
                        </span>
                    </div>
                @endif

                @if($isAdmin && $nextPhase)
                <div x-data="{ showBypass: false }">
                    <button @click="showBypass = !showBypass" class="w-full px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-medium rounded border border-amber-200 transition flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Admin: Bypass Phase
                    </button>
                    <div x-show="showBypass" x-transition class="mt-3 p-3 bg-amber-50 rounded border border-amber-200" x-cloak>
                        <form method="POST" action="{{ route('workflow.bypass', ['type' => 'program', 'id' => $program->id]) }}">
                            @csrf
                            <label class="block text-xs font-medium text-gray-700 mb-1">Skip to Phase</label>
                            <select name="target_phase" class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 mb-2 focus:ring-2 focus:ring-academic-500 outline-none">
                                @foreach($phases as $i => $p)
                                    @if($i > $currentIdx)
                                        <option value="{{ $p }}">{{ $labels[$p] }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Reason (min 10 chars)</label>
                            <textarea name="bypass_reason" required minlength="10" rows="2" class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 mb-2 focus:ring-2 focus:ring-academic-500 outline-none" placeholder="Explain why this phase is being bypassed..."></textarea>
                            <button type="submit" class="w-full px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded transition">Bypass Phase</button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Document Upload --}}
        <div class="border border-gray-200 bg-white overflow-hidden">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                <h3 class="text-sm font-bold text-academic-heading flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    Phase Documents
                </h3>
            </div>
            <div class="p-5 space-y-4">
                @if($phaseDocs->count())
                    <div class="space-y-2">
                        @foreach($phaseDocs as $doc)
                        <div x-data="{ editing: false }" class="space-y-0">
                            <div class="flex items-center gap-2 p-2 bg-gray-50 rounded group">
                                <div class="w-8 h-8 rounded flex items-center justify-center flex-shrink-0 @if($doc->file_extension === 'pdf') bg-red-100 @elseif(in_array($doc->file_extension, ['doc','docx','rtf'])) bg-blue-100 @elseif(in_array($doc->file_extension, ['xls','xlsx'])) bg-green-100 @elseif(in_array($doc->file_extension, ['jpg','jpeg','png'])) bg-purple-100 @else bg-gray-100 @endif">
                                    <span class="text-[9px] font-bold uppercase @if($doc->file_extension === 'pdf') text-red-600 @elseif(in_array($doc->file_extension, ['doc','docx','rtf'])) text-blue-600 @elseif(in_array($doc->file_extension, ['xls','xlsx'])) text-green-700 @elseif(in_array($doc->file_extension, ['jpg','jpeg','png'])) text-purple-600 @else text-gray-500 @endif">{{ $doc->file_extension }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-700 truncate">{{ $doc->original_name }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $doc->label }} @if($doc->document_type) · {{ $doc->document_type_name }} @endif · {{ $doc->human_file_size }}</p>
                                </div>
                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-academic-500 hover:text-academic-600 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg></a>
                                @if($isAdmin || $doc->uploaded_by === auth()->id())
                                <button type="button" @click="editing = !editing" class="text-gray-400 hover:text-academic-500 p-1 opacity-0 group-hover:opacity-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <form method="POST" action="{{ route('workflow.delete-document', $doc) }}" onsubmit="return confirmSubmit(event, 'Delete Document', 'Are you sure?', 'danger', 'Delete')">@csrf @method('DELETE')
                                    <button class="text-red-400 hover:text-red-600 p-1 opacity-0 group-hover:opacity-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </form>
                                @endif
                            </div>
                            @if($isAdmin || $doc->uploaded_by === auth()->id())
                            <div x-show="editing" x-cloak x-transition class="ml-10 mt-1 mb-1">
                                <form method="POST" action="{{ route('workflow.update-document-type', $doc) }}" class="flex items-center gap-2 flex-wrap">@csrf @method('PATCH')
                                    <select name="document_type" class="text-xs border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-academic-500 outline-none">
                                        <option value="">— No type —</option>
                                        @foreach(\App\Models\StatusDocument::DOCUMENT_TYPES as $key => $name)<option value="{{ $key }}" {{ $doc->document_type === $key ? 'selected' : '' }}>{{ $name }}</option>@endforeach
                                    </select>
                                    <button type="submit" class="px-2 py-1 text-xs font-medium text-white bg-academic-500 hover:bg-academic-600 rounded transition">Save</button>
                                    <button type="button" @click="editing = false" class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded transition">Cancel</button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif

                @if($canUpload)
                <form method="POST" action="{{ route('workflow.upload-document', ['type' => 'program', 'id' => $program->id]) }}" enctype="multipart/form-data" class="space-y-2 pt-2 border-t border-gray-100">
                    @csrf
                    <select name="label" required class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-2 focus:ring-academic-500 outline-none">
                        <option value="">Select label...</option>
                        @foreach($reqDocs as $rl)<option value="{{ $rl }}">{{ $rl }}</option>@endforeach
                        <option value="Supporting Document">Supporting Document</option>
                        <option value="Other">Other</option>
                    </select>
                    <select name="document_type" class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-2 focus:ring-academic-500 outline-none">
                        <option value="">Document type (optional)...</option>
                        @foreach(\App\Models\StatusDocument::DOCUMENT_TYPES as $key => $name)<option value="{{ $key }}">{{ $name }}</option>@endforeach
                    </select>
                    <input type="file" name="document" required accept="{{ collect($formats)->map(fn($f) => '.' . $f)->implode(',') }}" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-academic-50 file:text-academic-500 hover:file:bg-academic-100">
                    <p class="text-[10px] text-gray-400">Allowed: {{ strtoupper(implode(', ', $formats)) }} · Max {{ (WorkflowService::MAX_FILE_SIZE[$program->status] ?? 10240) / 1024 }}MB</p>
                    <button type="submit" class="w-full px-3 py-2 bg-academic-500 hover:bg-academic-600 text-white text-xs font-semibold rounded transition">Upload Document</button>
                </form>
                @endif
            </div>
        </div>

        {{-- All Documents --}}
        @if($allDocs->count())
        <div class="border border-gray-200 bg-white overflow-hidden" x-data="{ open: false }">
            <button @click="open = !open" class="w-full px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                <h3 class="text-sm font-bold text-academic-heading">All Documents ({{ $allDocs->count() }})</h3>
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
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-xs text-academic-500 hover:underline truncate">{{ $doc->original_name }}</a>
                            <span class="text-[10px] text-gray-400">{{ $doc->human_file_size }}</span>
                        </div>
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Transition History --}}
        @if($program->transitionLogs->count())
        <div class="border border-gray-200 bg-white overflow-hidden" x-data="{ open: false }">
            <button @click="open = !open" class="w-full px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                <h3 class="text-sm font-bold text-academic-heading">Transition History</h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition x-cloak class="px-5 pb-4">
                <div class="space-y-3 border-l-2 border-gray-200 pl-4 ml-1">
                    @foreach($program->transitionLogs->sortByDesc('created_at') as $log)
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full {{ $log->is_bypass ? 'bg-amber-400' : 'bg-academic-500' }}"></div>
                        <p class="text-xs text-gray-700"><span class="font-semibold">{{ $labels[$log->from_status] ?? $log->from_status }}</span> → <span class="font-semibold">{{ $labels[$log->to_status] ?? $log->to_status }}</span> @if($log->is_bypass) <span class="text-amber-600 text-[10px] font-semibold">(BYPASS)</span> @endif</p>
                        <p class="text-[10px] text-gray-400">{{ $log->transitioner->name ?? '—' }} · {{ $log->created_at->format('M d, Y H:i') }}</p>
                        @if($log->bypass_reason)<p class="text-xs text-amber-600 mt-0.5">Reason: {{ $log->bypass_reason }}</p>@endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Quick Stats --}}
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Quick Stats</h2></div>
            <div class="p-5 space-y-3">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Projects</span><span class="font-semibold text-gray-700">{{ $program->projects->count() }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Target Recipients</span><span class="font-semibold text-gray-700">{{ $program->target_recipients ?? '—' }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Total Funding</span><span class="font-semibold text-academic-500">₱{{ number_format($program->funding_total, 2) }}</span></div>
            </div>
        </div>

        {{-- Duration --}}
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Duration</h2></div>
            <div class="p-5 space-y-2 text-sm">
                <div><span class="text-gray-500 text-xs font-medium uppercase tracking-wider">Start</span><p class="text-gray-700">{{ $program->target_start_date?->format('F Y') ?? '—' }}</p></div>
                <div><span class="text-gray-500 text-xs font-medium uppercase tracking-wider">End</span><p class="text-gray-700">{{ $program->target_end_date?->format('F Y') ?? '—' }}</p></div>
            </div>
        </div>

        {{-- Funding --}}
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Funding Breakdown</h2></div>
            <div class="p-5 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">CHMSU GAA</span><span class="text-gray-700">₱{{ number_format($program->funding_chmsu_gaa, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">CHMSU STF</span><span class="text-gray-700">₱{{ number_format($program->funding_chmsu_stf, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Collaborator</span><span class="text-gray-700">₱{{ number_format($program->funding_collaborator, 2) }}</span></div>
                <hr class="border-gray-100">
                <div class="flex justify-between font-semibold"><span class="text-gray-700">Total</span><span class="text-academic-500">₱{{ number_format($program->funding_total, 2) }}</span></div>
            </div>
        </div>

        {{-- Members --}}
        @if($program->members->count())
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Program Leader & Members</h2></div>
            <div class="p-5">
                @if($program->program_leader)
                    <p class="text-sm text-gray-700 font-medium mb-2">{{ $program->program_leader }} <span class="text-xs text-gray-400">(Leader)</span></p>
                @endif
                <ul class="space-y-1.5">
                    @foreach($program->members as $member)
                        <li class="text-sm text-gray-700">{{ $member->name }}@if($member->responsibility) <span class="text-xs text-gray-400">— {{ $member->responsibility }}</span>@endif</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
