@extends('layouts.app')

@section('title', 'Evaluation Results — ' . $form->title)
@section('page-title', 'Evaluation Results')

@section('content')
<style>
    .gf-card { background: #fff; border: 1px solid #dadce0; border-radius: 8px; padding: 20px; margin-bottom: 12px; }
    @media (min-width: 640px) { .gf-card { padding: 28px 32px; } }
    .gf-header-card { border-top: 10px solid #1a73e8; }
    .gf-stat { text-align: center; padding: 20px 12px; background: #fff; }
    .gf-stat-value { font-size: 32px; font-weight: 400; color: #202124; }
    .gf-stat-label { font-size: 12px; color: #70757a; margin-top: 4px; }
    .gf-select { padding: 10px 12px; border: 1px solid #dadce0; border-radius: 4px; font-size: 14px; background: #fff; outline: none; cursor: pointer; }
    .gf-select:focus { border-color: #1a73e8; box-shadow: 0 0 0 1px #1a73e8; }
    .gf-progress-bar { height: 10px; background: #e3f2fd; border-radius: 5px; overflow: hidden; flex: 1; }
    .gf-progress-fill { height: 100%; border-radius: 5px; transition: width 0.5s ease; }
</style>

<div class="w-full">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm mb-4" style="color: #5f6368;">
        <a href="{{ route('evaluation.forms.index') }}" class="hover:underline" style="color: #1a73e8;">Evaluation Forms</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('evaluation.forms.show', $form) }}" class="hover:underline" style="color: #1a73e8;">{{ Str::limit($form->title, 30) }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color: #202124; font-weight: 500;">Results</span>
    </div>

    {{-- Header Card --}}
    <div class="gf-card gf-header-card">
        <h1 style="font-size: 24px; font-weight: 400; color: #202124; margin: 0 0 4px;" class="sm:!text-[32px]">{{ $form->title }}</h1>
        <p style="font-size: 14px; color: #70757a; margin: 0;">Evaluation Results & Analytics</p>
    </div>

    {{-- Summary Stats --}}
    <div class="gf-card" style="padding: 0; overflow: hidden;">
        <div style="display: grid; gap: 1px; background: #dadce0;" class="grid-cols-2 sm:grid-cols-4">
            <div class="gf-stat">
                <div class="gf-stat-value" style="color: #1a73e8;">{{ $totalResponses }}</div>
                <div class="gf-stat-label">Total Responses</div>
            </div>
            <div class="gf-stat">
                <div class="gf-stat-value" style="color: #4caf50;">{{ $overallAverage }}</div>
                <div class="gf-stat-label">Overall Average</div>
            </div>
            <div class="gf-stat">
                <div class="gf-stat-value" style="color: #2196f3;">
                    {{ $responses->where('submission_type', 'online')->count() ?: '—' }}
                </div>
                <div class="gf-stat-label">Online</div>
            </div>
            <div class="gf-stat">
                <div class="gf-stat-value" style="color: #ff9800;">
                    {{ $responses->where('submission_type', 'encoded')->count() ?: '—' }}
                </div>
                <div class="gf-stat-label">Encoded</div>
            </div>
        </div>
    </div>

    {{-- Per-Criterion Scores --}}
    <div class="gf-card">
        <p style="font-size: 16px; color: #202124; font-weight: 400; margin-bottom: 20px;">Score Summary by Criterion</p>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            @foreach($form->criteria as $criterion)
                @if($criterion->type === 'rating' && isset($criteriaStats[$criterion->id]))
                    @php $stat = $criteriaStats[$criterion->id]; $pct = ($stat['average'] / 5) * 100; @endphp
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <p style="font-size: 14px; color: #202124; flex: 1; padding-right: 16px;">
                                <span style="color: #70757a; font-size: 12px; margin-right: 4px;">{{ $loop->iteration }}.</span>
                                {{ $criterion->label }}
                            </p>
                            <span style="font-size: 14px; font-weight: 500; color: #202124; white-space: nowrap;">
                                {{ $stat['average'] }}<span style="color: #70757a; font-weight: 400;">/5</span>
                                <span style="font-size: 11px; color: #70757a; margin-left: 4px;">({{ $stat['count'] }})</span>
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="gf-progress-bar">
                                <div class="gf-progress-fill"
                                     style="width: {{ $pct }}%; background:
                                        {{ $pct >= 80 ? '#4caf50' : ($pct >= 60 ? '#1a73e8' : ($pct >= 40 ? '#ff9800' : '#f44336')) }};"></div>
                            </div>
                            <span style="font-size: 11px; color: #70757a; width: 32px; text-align: right;">{{ round($pct) }}%</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Filters --}}
    <div class="gf-card" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <form method="GET" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex: 1; width: 100%;">
            <select name="activity_id" class="gf-select" style="min-width: 180px; flex: 1;" onchange="this.form.submit()">
                <option value="">All Activities</option>
                @foreach($activities as $activity)
                    <option value="{{ $activity->id }}" {{ request('activity_id') == $activity->id ? 'selected' : '' }}>
                        {{ $activity->title }}
                    </option>
                @endforeach
            </select>

            <select name="submission_type" class="gf-select" style="min-width: 140px;" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="online" {{ request('submission_type') === 'online' ? 'selected' : '' }}>Online</option>
                <option value="encoded" {{ request('submission_type') === 'encoded' ? 'selected' : '' }}>Encoded</option>
            </select>

            @if(request()->anyFilled(['activity_id', 'submission_type']))
                <a href="{{ route('evaluation.forms.results', $form) }}" style="font-size: 13px; color: #1a73e8; text-decoration: none; font-weight: 500;">Clear filters</a>
            @endif
        </form>
    </div>

    {{-- Individual Responses --}}
    <div class="gf-card" style="padding: 0; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #dadce0;">
            <p style="font-size: 16px; color: #202124; font-weight: 400;">Individual Responses</p>
        </div>

        @if($responses->count())
            <div style="overflow-x: auto;">
                <table style="width: 100%; font-size: 14px; border-collapse: collapse; min-width: 700px;" x-data="{ expandedRow: null }">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 1px solid #dadce0;">
                            <th style="text-align: left; padding: 12px 24px; font-weight: 500; color: #5f6368; font-size: 12px; width: 40px;">#</th>
                            <th style="text-align: left; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Respondent</th>
                            <th style="text-align: left; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Activity</th>
                            <th style="text-align: center; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Sex</th>
                            <th style="text-align: center; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Type</th>
                            <th style="text-align: center; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Score</th>
                            <th style="text-align: right; padding: 12px 24px; font-weight: 500; color: #5f6368; font-size: 12px;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($responses as $response)
                            {{-- Main row --}}
                            <tr style="border-bottom: 1px solid #f1f3f4; cursor: pointer; transition: background 0.15s;"
                                @click="expandedRow = expandedRow === {{ $response->id }} ? null : {{ $response->id }}"
                                onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 12px 24px; color: #70757a; font-size: 12px;">{{ $loop->iteration + ($responses->currentPage() - 1) * $responses->perPage() }}</td>
                                <td style="padding: 12px 16px;">
                                    <p style="color: #202124; font-weight: 500; margin: 0;">{{ $response->respondent_name ?: 'Anonymous' }}</p>
                                    @if($response->respondent_organization)
                                        <p style="font-size: 12px; color: #70757a; margin: 2px 0 0;">{{ $response->respondent_organization }}</p>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px; color: #5f6368; font-size: 13px;">{{ Str::limit($response->activity->title ?? '—', 30) }}</td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    @if($response->respondent_gender)
                                        <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;
                                            {{ $response->respondent_gender === 'male' ? 'background: #e3f2fd; color: #1565c0;' : 'background: #fce4ec; color: #c62828;' }}">
                                            {{ strtoupper(substr($response->respondent_gender, 0, 1)) }}
                                        </span>
                                    @else
                                        <span style="color: #bdbdbd;">—</span>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; text-transform: uppercase;
                                        {{ $response->submission_type === 'online' ? 'background: #e3f2fd; color: #1565c0;' : 'background: #fff3e0; color: #ef6c00;' }}">
                                        {{ $response->submission_type }}
                                    </span>
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <span style="font-weight: 500; color: #202124;">{{ $response->average_score }}</span>
                                    <span style="font-size: 11px; color: #70757a;">/5</span>
                                </td>
                                <td style="padding: 12px 24px; text-align: right; font-size: 12px; color: #70757a;">{{ $response->created_at->format('M d, Y h:i A') }}</td>
                            </tr>

                            {{-- Expandable detail row --}}
                            <tr x-show="expandedRow === {{ $response->id }}" x-collapse x-cloak>
                                <td colspan="7" style="padding: 16px 24px; background: #fafafa; border-bottom: 1px solid #dadce0;">
                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
                                        @foreach($response->answers as $answer)
                                            <div style="padding: 12px; background: #fff; border: 1px solid #e3f2fd; border-radius: 8px;">
                                                <p style="font-size: 12px; color: #5f6368; margin: 0 0 8px;">
                                                    <span style="color: #70757a;">{{ $loop->iteration }}.</span>
                                                    {{ $answer->criteria->label ?? '—' }}
                                                </p>
                                                @if($answer->numeric_value)
                                                    <div style="display: flex; align-items: center; gap: 4px;">
                                                        @for($s = 1; $s <= 5; $s++)
                                                            <span style="width: 18px; height: 18px; border-radius: 50%; font-size: 9px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center;
                                                                {{ $s <= $answer->numeric_value ? 'background: #1a73e8; color: #fff;' : 'background: #e3f2fd; color: #9e9e9e;' }}">
                                                                {{ $s }}
                                                            </span>
                                                        @endfor
                                                        <span style="margin-left: 4px; font-size: 13px; font-weight: 500; color: #202124;">{{ $answer->numeric_value }}/5</span>
                                                    </div>
                                                @elseif($answer->text_value)
                                                    <p style="font-size: 13px; color: #202124; margin: 0; font-style: italic;">"{{ $answer->text_value }}"</p>
                                                @else
                                                    <p style="font-size: 13px; color: #bdbdbd; margin: 0;">No answer</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($response->submission_type === 'encoded' && $response->encoder)
                                        <p style="font-size: 11px; color: #70757a; margin: 12px 0 0; padding-top: 12px; border-top: 1px solid #e3f2fd;">
                                            Encoded by {{ $response->encoder->full_name }} on {{ $response->created_at->format('M d, Y h:i A') }}
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($responses->hasPages())
                <div style="padding: 16px 24px; border-top: 1px solid #dadce0;">
                    {{ $responses->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 48px 24px;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#bdbdbd" stroke-width="1.5" style="margin: 0 auto 12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p style="font-size: 14px; color: #5f6368;">No responses match your filters.</p>
            </div>
        @endif
    </div>
</div>
@endsection
