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
    $canUpload   = auth()->user()->isAdmin() || $project->created_by === auth()->id();
    $reqDocs     = WorkflowService::PROJECT_REQUIRED_DOCS[$project->status] ?? [];
    $phaseDocs   = $project->statusDocuments->where('phase', $project->status);
    $allDocs     = $project->statusDocuments;
    $isAdmin     = auth()->user()->isAdmin();
@endphp

{{-- Breadcrumb --}}
<nav class="text-sm text-gray-500 mb-5 flex items-center gap-1">
    <a href="{{ route('extension.projects.index') }}" class="hover:text-academic-500 transition">Projects</a>
    @if($project->program)
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('extension.programs.show', $project->program) }}" class="hover:text-academic-500 transition truncate max-w-[160px]">{{ $project->program->title }}</a>
    @endif
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-academic-heading font-medium truncate max-w-xs">{{ $project->title }}</span>
</nav>

{{-- Workflow Stepper --}}
<div class="border border-gray-200 bg-white mb-6">
    <div class="bg-academic-500 px-6 py-2.5"><h2 class="text-xs font-bold text-white uppercase tracking-wider">Workflow Progress</h2></div>
    <div class="px-6 py-4">
        <div class="flex items-center justify-between overflow-x-auto">
            @foreach($phases as $i => $phase)
                @php $isCompleted=$i<$currentIdx; $isCurrent=$i===$currentIdx; $isFuture=$i>$currentIdx; $c=$colors[$phase]; @endphp
                <div class="flex items-center {{ $i < count($phases)-1 ? 'flex-1' : '' }} min-w-0">
                    <div class="flex flex-col items-center min-w-[72px]">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all {{ $isCompleted?'bg-green-500 text-white':'' }} {{ $isCurrent?$c['bg'].' '.$c['text'].' ring-2 '.$c['ring']:'' }} {{ $isFuture?'bg-gray-100 text-gray-400':'' }}">
                            @if($isCompleted)<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>@else{{ $i+1 }}@endif
                        </div>
                        <span class="mt-1.5 text-xs font-semibold whitespace-nowrap {{ $isCurrent?$c['text']:($isCompleted?'text-green-600':'text-gray-400') }}">{{ $labels[$phase] }}</span>
                    </div>
                    @if($i<count($phases)-1)<div class="flex-1 h-0.5 mx-2 mt-[-16px] rounded {{ $isCompleted?'bg-green-400':'bg-gray-200' }}"></div>@endif
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Title Card --}}
<div class="border border-gray-200 bg-white mb-6">
    <div class="bg-academic-500 px-6 py-3"><h2 class="text-sm font-bold text-white">Project Overview</h2></div>
    <div class="p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-xl font-bold text-academic-heading">{{ $project->title }}</h1>
                    @if($project->is_overdue)<span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-red-100 text-red-700">Overdue</span>@endif
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded {{ $colors[$project->status]['bg'] }} {{ $colors[$project->status]['text'] }}">{{ $labels[$project->status] }}</span>
                </div>
                <p class="text-sm text-gray-600">
                    @if($project->program)
                        Under: <a href="{{ route('extension.programs.show', $project->program) }}" class="text-academic-500 hover:underline font-medium">{{ $project->program->title }}</a>
                    @else
                        <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 rounded">Standalone Project</span>
                    @endif
                </p>
                <p class="text-sm text-gray-600">Campus: <span class="font-medium">{{ $project->campus->name ?? '—' }}</span></p>
                <p class="text-xs text-gray-400 mt-1">Created by {{ $project->creator->name ?? '—' }} · {{ $project->created_at->format('M d, Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('extension.activities.create', ['project_id' => $project->id]) }}" class="inline-flex items-center gap-1 px-3 py-2 bg-academic-500 hover:bg-academic-600 text-white text-xs font-semibold rounded transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Activity
                </a>
                <a href="{{ route('extension.beneficiaries.index', $project) }}" class="inline-flex items-center gap-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg> Manage Beneficiaries
                </a>
                <a href="{{ route('extension.projects.edit', $project) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded border border-gray-200 transition">Edit</a>
                <form method="POST" action="{{ route('extension.projects.destroy', $project) }}" onsubmit="return confirmSubmit(event, 'Delete Project', 'This will permanently delete this project.', 'danger', 'Delete')">@csrf @method('DELETE')
                    <button class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold rounded border border-red-200 transition">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- LEFT --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Description --}}
        @if($project->description)
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Description</h2></div>
            <div class="p-6"><p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $project->description }}</p></div>
        </div>
        @endif

        {{-- Activities --}}
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-sm font-bold text-academic-heading">Activities ({{ $project->activities->count() }})</h2>
                <a href="{{ route('extension.activities.create', ['project_id' => $project->id]) }}" class="text-xs text-academic-500 hover:text-academic-600 font-semibold flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add</a>
            </div>
            @if($project->activities->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-2.5 text-left text-xs font-bold text-academic-heading uppercase tracking-wider">Activity</th>
                        <th class="px-4 py-2.5 text-left text-xs font-bold text-academic-heading uppercase tracking-wider">Target Date</th>
                        <th class="px-4 py-2.5 text-center text-xs font-bold text-academic-heading uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-right text-xs font-bold text-academic-heading uppercase tracking-wider">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($project->activities as $activity)
                        @php
                            $asc=['proposal'=>'bg-yellow-100 text-yellow-700','ongoing'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700'];
                            $asl=['proposal'=>'Proposal','ongoing'=>'Ongoing','completed'=>'Completed'];
                            $aOverdue=$activity->is_overdue;
                        @endphp
                        <tr class="hover:bg-academic-50/30 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-academic-heading">{{ $activity->title }}</p>
                                @if($activity->persons_responsible)<p class="text-xs text-gray-400 mt-0.5">{{ $activity->persons_responsible }}</p>@endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $activity->target_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($aOverdue)<span class="px-2 py-0.5 text-xs font-semibold rounded bg-red-100 text-red-700">Overdue</span>
                                @else<span class="px-2 py-0.5 text-xs font-semibold rounded {{ $asc[$activity->status] ?? '' }}">{{ $asl[$activity->status] ?? ucfirst($activity->status) }}</span>@endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('extension.activities.edit', $activity) }}" class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded transition">Edit</a>
                                    <form method="POST" action="{{ route('extension.activities.destroy', $activity) }}" onsubmit="return confirmSubmit(event,'Delete Activity','Are you sure?','danger','Delete')" class="inline">@csrf @method('DELETE')
                                        <button class="p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="px-6 py-8 text-center text-sm text-gray-400">No activities yet. <a href="{{ route('extension.activities.create', ['project_id' => $project->id]) }}" class="text-academic-500 hover:underline">Add the first activity</a></div>
            @endif
        </div>

        {{-- Beneficiaries --}}
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-sm font-bold text-academic-heading">Beneficiaries / Participants ({{ $project->beneficiaries->count() }})</h2>
                <a href="{{ route('extension.beneficiaries.index', $project) }}" class="text-xs text-academic-500 hover:text-academic-600 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Manage List
                </a>
            </div>
            @if($project->beneficiaries->count())
            {{-- Impact Summary --}}
            <div class="px-6 py-3 bg-academic-50/50 border-b border-gray-100 flex items-center gap-6 text-sm">
                <span class="text-gray-600">Total: <strong class="text-academic-heading">{{ $project->beneficiaries->sum('total_count') }}</strong></span>
                <span class="text-gray-600">Male: <strong class="text-blue-600">{{ $project->beneficiaries->sum('male_count') }}</strong></span>
                <span class="text-gray-600">Female: <strong class="text-pink-600">{{ $project->beneficiaries->sum('female_count') }}</strong></span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-2.5 text-left text-xs font-bold text-academic-heading uppercase tracking-wider">Beneficiary</th>
                        <th class="px-4 py-2.5 text-left text-xs font-bold text-academic-heading uppercase tracking-wider">Type / Sector</th>
                        <th class="px-4 py-2.5 text-center text-xs font-bold text-academic-heading uppercase tracking-wider">Count</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($project->beneficiaries->take(5) as $ben)
                        <tr class="hover:bg-academic-50/30 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $ben->name }}</p>
                                @if($ben->organization)<p class="text-xs text-gray-400">{{ $ben->organization }}</p>@endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs font-medium rounded {{ $ben->type==='individual'?'bg-blue-50 text-blue-600':($ben->type==='organization'?'bg-purple-50 text-purple-600':'bg-green-50 text-green-600') }}">{{ ucfirst($ben->type) }}</span>
                                @if($ben->sector)<span class="text-xs text-gray-400 ml-1">{{ ucfirst($ben->sector) }}</span>@endif
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-bold text-gray-700">{{ $ben->total_count }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($project->beneficiaries->count() > 5)
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 text-center">
                <a href="{{ route('extension.beneficiaries.index', $project) }}" class="text-xs text-academic-500 hover:text-academic-600 font-semibold">View all {{ $project->beneficiaries->count() }} beneficiaries →</a>
            </div>
            @endif
            @else
                <div class="px-6 py-8 text-center text-sm text-gray-400">No beneficiaries yet. <a href="{{ route('extension.beneficiaries.index', $project) }}" class="text-academic-500 hover:underline">Manage beneficiaries</a></div>
            @endif
        </div>

        {{-- Budget Items --}}
        @if($project->budgetItems->count())
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Budget Items</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-2.5 text-left text-xs font-bold text-academic-heading uppercase tracking-wider">Item</th>
                        <th class="px-4 py-2.5 text-left text-xs font-bold text-academic-heading uppercase tracking-wider">Location</th>
                        <th class="px-4 py-2.5 text-right text-xs font-bold text-academic-heading uppercase tracking-wider">Budget</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($project->budgetItems as $item)
                        <tr class="hover:bg-academic-50/30 transition-colors">
                            <td class="px-4 py-3 text-gray-800">{{ $item->item_description }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->location ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-700">₱{{ number_format($item->total_budget, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-50"><td colspan="2" class="px-4 py-2.5 text-right text-xs font-bold text-gray-600 uppercase">Total</td><td class="px-4 py-2.5 text-right font-bold text-academic-500">₱{{ number_format($project->budgetItems->sum('total_budget'), 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- RIGHT SIDEBAR --}}
    <div class="space-y-6">

        {{-- Current Phase --}}
        <div class="border-2 {{ $colors[$project->status]['border'] }} bg-white overflow-hidden">
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
                        <li class="flex items-center gap-2 text-sm">
                            @if($info['met'])<svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg><span class="text-gray-500 line-through">{{ $info['label'] }}</span>
                            @else<svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg><span class="text-gray-800 font-medium">{{ $info['label'] }}</span>@endif
                        </li>
                        @endforeach
                        @foreach($req['documents'] as $docLabel => $info)
                        <li class="flex items-center gap-2 text-sm">
                            @if($info['met'])<svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg><span class="text-gray-500 line-through">{{ $info['label'] }} (doc)</span>
                            @else<svg class="w-4 h-4 text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg><span class="text-gray-800 font-medium">{{ $info['label'] }} (upload)</span>@endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if($nextPhase)
                    @if($workflowCheck['can_advance'])
                        <form method="POST" action="{{ route('workflow.advance', ['type' => 'project', 'id' => $project->id]) }}" onsubmit="return confirmSubmit(event, 'Advance Phase', 'Move this project to {{ $labels[$nextPhase] }} phase?', 'info', 'Advance')">@csrf
                            <button class="w-full px-4 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-semibold rounded transition flex items-center justify-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg> Advance to {{ $labels[$nextPhase] }}</button>
                        </form>
                    @else
                        <button disabled class="w-full px-4 py-2.5 bg-gray-200 text-gray-400 text-sm font-semibold rounded cursor-not-allowed flex items-center justify-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Complete requirements to advance</button>
                    @endif
                @else
                    <div class="text-center py-2"><span class="inline-flex items-center gap-2 text-sm text-green-600 font-semibold"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Project Completed</span></div>
                @endif

                @if($isAdmin && $nextPhase)
                <div x-data="{ showBypass: false }">
                    <button @click="showBypass = !showBypass" class="w-full px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-medium rounded border border-amber-200 transition flex items-center justify-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Admin: Bypass Phase</button>
                    <div x-show="showBypass" x-transition class="mt-3 p-3 bg-amber-50 rounded border border-amber-200" x-cloak>
                        <form method="POST" action="{{ route('workflow.bypass', ['type' => 'project', 'id' => $project->id]) }}">@csrf
                            <label class="block text-xs font-medium text-gray-700 mb-1">Skip to Phase</label>
                            <select name="target_phase" class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 mb-2 focus:ring-2 focus:ring-academic-500 outline-none">
                                @foreach($phases as $i => $p)@if($i > $currentIdx)<option value="{{ $p }}">{{ $labels[$p] }}</option>@endif @endforeach
                            </select>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Reason (min 10 chars)</label>
                            <textarea name="bypass_reason" required minlength="10" rows="2" class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 mb-2 focus:ring-2 focus:ring-academic-500 outline-none" placeholder="Explain why..."></textarea>
                            <button type="submit" class="w-full px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded transition">Bypass Phase</button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Document Upload --}}
        <div class="border border-gray-200 bg-white overflow-hidden">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200"><h3 class="text-sm font-bold text-academic-heading">Phase Documents</h3></div>
            <div class="p-5 space-y-4">
                @if($phaseDocs->count())
                <div class="space-y-2">
                    @foreach($phaseDocs as $doc)
                    <div class="flex items-center gap-2 p-2 bg-gray-50 rounded group">
                        <div class="w-8 h-8 rounded flex items-center justify-center flex-shrink-0 @if($doc->file_extension==='pdf')bg-red-100 @elseif(in_array($doc->file_extension,['doc','docx']))bg-blue-100 @else bg-gray-100 @endif">
                            <span class="text-[9px] font-bold uppercase @if($doc->file_extension==='pdf')text-red-600 @elseif(in_array($doc->file_extension,['doc','docx']))text-blue-600 @else text-gray-500 @endif">{{ $doc->file_extension }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-700 truncate">{{ $doc->original_name }}</p>
                            <p class="text-[10px] text-gray-400">{{ $doc->label }} · {{ $doc->human_file_size }}</p>
                        </div>
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-academic-500 hover:text-academic-600 p-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg></a>
                        @if($isAdmin || $doc->uploaded_by === auth()->id())
                        <form method="POST" action="{{ route('workflow.delete-document', $doc) }}" onsubmit="return confirmSubmit(event,'Delete','Are you sure?','danger','Delete')">@csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 p-1 opacity-0 group-hover:opacity-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                @if($canUpload)
                <form method="POST" action="{{ route('workflow.upload-document', ['type' => 'project', 'id' => $project->id]) }}" enctype="multipart/form-data" class="space-y-2 pt-2 border-t border-gray-100">@csrf
                    <select name="label" required class="w-full text-sm border border-gray-300 rounded px-3 py-1.5 focus:ring-2 focus:ring-academic-500 outline-none">
                        <option value="">Select label...</option>
                        @foreach($reqDocs as $rl)<option value="{{ $rl }}">{{ $rl }}</option>@endforeach
                        <option value="Supporting Document">Supporting Document</option><option value="Other">Other</option>
                    </select>
                    <input type="file" name="document" required accept="{{ collect($formats)->map(fn($f)=>'.'.$f)->implode(',') }}" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-academic-50 file:text-academic-500 hover:file:bg-academic-100">
                    <p class="text-[10px] text-gray-400">Allowed: {{ strtoupper(implode(', ', $formats)) }} · Max {{ (WorkflowService::MAX_FILE_SIZE[$project->status] ?? 10240) / 1024 }}MB</p>
                    <button type="submit" class="w-full px-3 py-2 bg-academic-500 hover:bg-academic-600 text-white text-xs font-semibold rounded transition">Upload Document</button>
                </form>
                @endif
            </div>
        </div>

        {{-- All Documents --}}
        @if($allDocs->count())
        <div class="border border-gray-200 bg-white overflow-hidden" x-data="{ open: false }">
            <button @click="open=!open" class="w-full px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                <h3 class="text-sm font-bold text-academic-heading">All Documents ({{ $allDocs->count() }})</h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open?'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition x-cloak class="px-5 pb-4 space-y-1.5">
                @foreach($phases as $p)@php $docs=$allDocs->where('phase',$p); @endphp
                    @if($docs->count())<p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-2">{{ $labels[$p] }}</p>
                    @foreach($docs as $doc)<div class="flex items-center gap-2 py-1"><span class="text-[9px] font-bold text-gray-400 uppercase bg-gray-100 rounded px-1.5 py-0.5">{{ $doc->file_extension }}</span><a href="{{ asset('storage/'.$doc->file_path) }}" target="_blank" class="text-xs text-academic-500 hover:underline truncate">{{ $doc->original_name }}</a><span class="text-[10px] text-gray-400">{{ $doc->human_file_size }}</span></div>@endforeach
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Transition History --}}
        @if($project->transitionLogs->count())
        <div class="border border-gray-200 bg-white overflow-hidden" x-data="{ open: false }">
            <button @click="open=!open" class="w-full px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                <h3 class="text-sm font-bold text-academic-heading">Transition History</h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open?'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition x-cloak class="px-5 pb-4">
                <div class="space-y-3 border-l-2 border-gray-200 pl-4 ml-1">
                    @foreach($project->transitionLogs->sortByDesc('created_at') as $log)
                    <div class="relative">
                        <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full {{ $log->is_bypass?'bg-amber-400':'bg-academic-500' }}"></div>
                        <p class="text-xs text-gray-700"><span class="font-semibold">{{ $labels[$log->from_status]??$log->from_status }}</span> → <span class="font-semibold">{{ $labels[$log->to_status]??$log->to_status }}</span> @if($log->is_bypass) <span class="text-amber-600 text-[10px] font-semibold">(BYPASS)</span>@endif</p>
                        <p class="text-[10px] text-gray-400">{{ $log->transitioner->name??'—' }} · {{ $log->created_at->format('M d, Y H:i') }}</p>
                        @if($log->bypass_reason)<p class="text-xs text-amber-600 mt-0.5">{{ $log->bypass_reason }}</p>@endif
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
                <div class="flex justify-between text-sm"><span class="text-gray-500">Activities</span><span class="font-semibold text-gray-700">{{ $project->activities->count() }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Beneficiaries</span><span class="font-semibold text-gray-700">{{ $project->beneficiaries->count() }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Budget</span><span class="font-semibold text-academic-500">₱{{ number_format($project->budget_requirement ?? 0, 2) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Persons Responsible</span><span class="font-semibold text-gray-700">{{ $project->persons_responsible ?? '—' }}</span></div>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Timeline</h2></div>
            <div class="p-5 space-y-2 text-sm">
                <div><span class="text-gray-500 text-xs font-medium uppercase tracking-wider">Start</span><p class="text-gray-700">{{ $project->target_start_date?->format('F d, Y') ?? '—' }}</p></div>
                <div><span class="text-gray-500 text-xs font-medium uppercase tracking-wider">End</span><p class="text-gray-700">{{ $project->target_end_date?->format('F d, Y') ?? '—' }}</p></div>
            </div>
        </div>

        {{-- Indicators --}}
        @if($project->indicators_output)
        <div class="border border-gray-200 bg-white">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200"><h2 class="text-sm font-bold text-academic-heading">Indicators / Output</h2></div>
            <div class="p-5"><p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $project->indicators_output }}</p></div>
        </div>
        @endif
    </div>
</div>
@endsection
