@extends('layouts.app')
@section('title', $project->title)
@section('page-title', 'Project Details')

@section('content')
@php
    use App\Services\WorkflowService;
    $phases      = WorkflowService::PHASES;
    $labels      = WorkflowService::PHASE_LABELS;
    $colors      = WorkflowService::PHASE_COLORS;
    $icons       = WorkflowService::PHASE_ICONS;
    $currentIdx  = WorkflowService::getPhaseIndex($project->status);
    $req         = WorkflowService::getRequirementsStatus($project);
    $nextPhase   = WorkflowService::getNextPhase($project->status);
    $formats     = WorkflowService::ALLOWED_FORMATS[$project->status] ?? [];
    $canUpload   = WorkflowService::canUserUpload($project->status, auth()->user()->role);
    $reqDocs     = WorkflowService::PROJECT_REQUIRED_DOCS[$project->status] ?? [];
    $phaseDocs   = $project->statusDocuments->where('phase', $project->status);
    $allDocs     = $project->statusDocuments;
    $isAdmin     = auth()->user()->isAdmin();
@endphp

{{-- Breadcrumb --}}
<nav class="text-sm text-gray-500 mb-5 flex items-center gap-1 flex-wrap">
    <a href="{{ route('extension.projects.index') }}" class="hover:text-blue-700">Projects</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-700 font-medium truncate max-w-xs">{{ $project->title }}</span>
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
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-xl font-bold text-gray-800">{{ $project->title }}</h1>
                @if($project->is_overdue)
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-700">Overdue</span>
                @endif
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $colors[$project->status]['bg'] }} {{ $colors[$project->status]['text'] }} {{ $colors[$project->status]['border'] }} border">
                    {{ $labels[$project->status] }}
                </span>
            </div>
            <p class="text-sm text-gray-500">
                @if($project->program)
                    Under: <a href="{{ route('extension.programs.show', $project->program) }}" class="text-blue-700 hover:underline">{{ $project->program->title }}</a>
                @else
                    <span class="italic text-gray-400">Standalone Project</span>
                @endif
            </p>
            <p class="text-sm text-gray-500">Campus: {{ $project->campus->name ?? '—' }}</p>
            <p class="text-xs text-gray-400 mt-1">Created by {{ $project->creator->name ?? '—' }} · {{ $project->created_at->format('M d, Y') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('extension.activities.create', ['project_id' => $project->id]) }}" class="inline-flex items-center gap-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Activity
            </a>
            <a href="{{ route('extension.beneficiaries.create', ['project_id' => $project->id]) }}" class="inline-flex items-center gap-1 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Beneficiary
            </a>
            <a href="{{ route('extension.projects.edit', $project) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg border border-gray-200 transition">Edit</a>
            <form method="POST" action="{{ route('extension.projects.destroy', $project) }}" onsubmit="return confirmSubmit(event, 'Delete Project', 'This will permanently delete this project and all related data.', 'danger', 'Delete')">@csrf @method('DELETE')
                <button class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold rounded-lg border border-red-200 transition">Delete</button>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- LEFT --}}
    <div class="lg:col-span-2 space-y-6">

        @if($project->description)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-2">Description</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $project->description }}</p>
        </div>
        @endif

        {{-- Activities --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-700">Activities ({{ $project->activities->count() }})</h2>
                <a href="{{ route('extension.activities.create', ['project_id' => $project->id]) }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">+ Add</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500">Activity</th>
                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500">Target Date</th>
                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500">Status</th>
                        <th class="px-5 py-2 text-right text-xs font-semibold text-gray-500">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($project->activities as $act)
                            @php $aOverdue = $act->is_overdue; @endphp
                            <tr class="hover:bg-gray-50 {{ $aOverdue ? 'bg-red-50/30' : '' }}">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800">{{ $act->title }}</p>
                                    <p class="text-xs text-gray-400">{{ $act->persons_responsible ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-3 text-gray-600 text-xs">{{ $act->target_date?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    @php $ac = $colors[$act->status] ?? $colors['proposal']; @endphp
                                    @if($aOverdue)
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-red-100 text-red-700">Overdue</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $ac['bg'] }} {{ $ac['text'] }}">{{ $labels[$act->status] ?? ucfirst($act->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('extension.activities.edit', $act) }}" class="text-gray-400 hover:text-yellow-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                        <form method="POST" action="{{ route('extension.activities.destroy', $act) }}" class="inline" onsubmit="return confirmSubmit(event, 'Delete Activity', 'Are you sure?', 'danger', 'Delete')">@csrf @method('DELETE')
                                            <button class="text-gray-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">No activities yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Beneficiaries --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-700">Beneficiaries ({{ $project->beneficiaries->count() }})</h2>
                <a href="{{ route('extension.beneficiaries.create', ['project_id' => $project->id]) }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">+ Add</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500">Name</th>
                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500">Organization</th>
                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500">Contact</th>
                        <th class="px-5 py-2 text-right text-xs font-semibold text-gray-500">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($project->beneficiaries as $ben)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-gray-700">{{ $ben->name }}</td>
                                <td class="px-5 py-3 text-gray-500 text-xs">{{ $ben->organization ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-500 text-xs">{{ $ben->contact_no ?? '—' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('extension.beneficiaries.edit', $ben) }}" class="text-gray-400 hover:text-yellow-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                                        <form method="POST" action="{{ route('extension.beneficiaries.destroy', $ben) }}" class="inline" onsubmit="return confirmSubmit(event, 'Remove', 'Remove this beneficiary?', 'danger', 'Remove')">@csrf @method('DELETE')
                                            <button class="text-gray-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">No beneficiaries recorded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Budget Items --}}
        @if($project->budgetItems->count())
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-700">Budget Line Items</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50"><tr>
                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500">Location</th>
                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500">Item</th>
                        <th class="px-5 py-2 text-right text-xs font-semibold text-gray-500">Amount</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($project->budgetItems as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-2 text-gray-600">{{ $item->location ?? '—' }}</td>
                            <td class="px-5 py-2 text-gray-700">{{ $item->item_description }}</td>
                            <td class="px-5 py-2 text-right text-gray-700">₱{{ number_format($item->total_budget, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-50 font-semibold">
                            <td colspan="2" class="px-5 py-2 text-right text-gray-700">Total</td>
                            <td class="px-5 py-2 text-right text-blue-700">₱{{ number_format($project->total_budget_spent, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT: Workflow Panel --}}
    <div class="space-y-6">

        {{-- ===== CURRENT PHASE PANEL ===== --}}
        <div class="bg-white rounded-xl border-2 {{ $colors[$project->status]['border'] }} overflow-hidden">
            <div class="px-5 py-3 {{ $colors[$project->status]['bg'] }} border-b {{ $colors[$project->status]['border'] }}">
                <h3 class="text-sm font-bold {{ $colors[$project->status]['text'] }} flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$project->status] }}"/></svg>
                    Current Phase: {{ $labels[$project->status] }}
                </h3>
            </div>
            <div class="p-5 space-y-4">

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

                @if($nextPhase)
                    @if($workflowCheck['can_advance'])
                        <form method="POST" action="{{ route('workflow.advance', ['type' => 'project', 'id' => $project->id]) }}" onsubmit="return confirmSubmit(event, 'Advance Phase', 'Move this project to {{ $labels[$nextPhase] }} phase?', 'info', 'Advance')">
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
                            Project Completed
                        </span>
                    </div>
                @endif

                @if($isAdmin && $nextPhase)
                <div x-data="{ showBypass: false }">
                    <button @click="showBypass = !showBypass" class="w-full px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 text-[12px] font-medium rounded-lg border border-amber-200 transition flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Admin: Bypass Phase
                    </button>
                    <div x-show="showBypass" x-transition class="mt-3 p-3 bg-amber-50 rounded-lg border border-amber-200" x-cloak>
                        <form method="POST" action="{{ route('workflow.bypass', ['type' => 'project', 'id' => $project->id]) }}">
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

                @if($canUpload)
                <form method="POST" action="{{ route('workflow.upload-document', ['type' => 'project', 'id' => $project->id]) }}" enctype="multipart/form-data" class="space-y-2 pt-2 border-t border-gray-100">
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
                    <p class="text-[10px] text-gray-400">Allowed: {{ strtoupper(implode(', ', $formats)) }} · Max {{ (WorkflowService::MAX_FILE_SIZE[$project->status] ?? 10240) / 1024 }}MB</p>
                    <button type="submit" class="w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[12px] font-semibold rounded-md transition">Upload Document</button>
                </form>
                @else
                <p class="text-[12px] text-gray-400 italic">You don't have permission to upload in this phase.</p>
                @endif
            </div>
        </div>

        {{-- All Documents --}}
        @if($allDocs->count())
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ open: false }">
            <button @click="open = !open" class="w-full px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                <h3 class="text-sm font-bold text-gray-700">All Documents ({{ $allDocs->count() }})</h3>
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

        {{-- Transition History --}}
        @if($project->transitionLogs->count())
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ open: false }">
            <button @click="open = !open" class="w-full px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                <h3 class="text-sm font-bold text-gray-700">Transition History</h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition x-cloak class="px-5 pb-4">
                <div class="space-y-3 border-l-2 border-gray-200 pl-4 ml-1">
                    @foreach($project->transitionLogs->sortByDesc('created_at') as $log)
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
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Activities</span><span class="font-semibold">{{ $project->activities->count() }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Beneficiaries</span><span class="font-semibold">{{ $project->beneficiaries->count() }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Budget</span><span class="font-semibold">₱{{ number_format($project->budget_requirement, 2) }}</span></div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">Timeline</h2>
            <div class="space-y-2 text-sm">
                <div><span class="text-gray-400 text-xs">Start</span><p class="text-gray-700">{{ $project->target_start_date?->format('M d, Y') ?? '—' }}</p></div>
                <div><span class="text-gray-400 text-xs">End</span><p class="text-gray-700">{{ $project->target_end_date?->format('M d, Y') ?? '—' }}</p></div>
                @if($project->persons_responsible)
                    <div><span class="text-gray-400 text-xs">Responsible</span><p class="text-gray-700">{{ $project->persons_responsible }}</p></div>
                @endif
                @if($project->budget_source)
                    <div><span class="text-gray-400 text-xs">Budget Source</span><p class="text-gray-700">{{ $project->budget_source }}</p></div>
                @endif
            </div>
        </div>

        @if($project->indicators_output)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-2">Indicators / Output</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $project->indicators_output }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
