@extends('layouts.app')

@section('title', $form->title)
@section('page-title', 'Evaluation Form')

@section('content')
<style>
    .gf-card { background: #fff; border: 1px solid #dadce0; border-radius: 8px; padding: 20px; margin-bottom: 12px; }
    @media (min-width: 640px) { .gf-card { padding: 28px 32px; } }
    .gf-header-card { border-top: 10px solid #1a73e8; }
    .gf-stat { text-align: center; padding: 16px 12px; }
    .gf-stat-value { font-size: 28px; font-weight: 400; color: #202124; }
    .gf-stat-label { font-size: 12px; color: #70757a; margin-top: 4px; }
    .gf-btn-primary { background: #1a73e8; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; letter-spacing: 0.25px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .gf-btn-primary:hover { background: #1557b0; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
    .gf-btn-outline { background: transparent; color: #1a73e8; border: 1px solid #dadce0; padding: 10px 24px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .gf-btn-outline:hover { background: #e8f0fe; border-color: #1a73e8; }
</style>

<div class="w-full">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm mb-4" style="color: #5f6368;">
        <a href="{{ route('evaluation.forms.index') }}" class="hover:underline" style="color: #1a73e8;">Evaluation Forms</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color: #202124; font-weight: 500;">{{ Str::limit($form->title, 40) }}</span>
    </div>

    {{-- Header Card --}}
    <div class="gf-card gf-header-card">
        <div style="display: flex; justify-content: space-between; gap: 16px; margin-bottom: 16px;" class="flex-col sm:flex-row sm:items-start">
            <div style="min-width: 0;">
                <h1 style="font-size: 24px; font-weight: 400; color: #202124; margin: 0 0 4px;" class="sm:!text-[32px]">{{ $form->title }}</h1>
                <p style="font-size: 14px; color: #70757a; margin: 0;">{{ $form->program->title ?? '—' }}</p>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 500;
                    {{ $form->is_active ? 'background: #e8f5e9; color: #2e7d32;' : 'background: #f5f5f5; color: #757575;' }}">
                    <span style="width: 6px; height: 6px; border-radius: 50; {{ $form->is_active ? 'background: #4caf50;' : 'background: #bdbdbd;' }}"></span>
                    {{ $form->is_active ? 'Active' : 'Inactive' }}
                </span>
                <a href="{{ route('evaluation.forms.edit', $form) }}"
                   style="padding: 8px; color: #5f6368; border-radius: 50%; transition: background 0.2s; display: inline-flex;"
                   onmouseover="this.style.background='#f1f3f4'" onmouseout="this.style.background='transparent'" title="Edit form">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
            </div>
        </div>

        @if($form->description)
            <p style="font-size: 14px; color: #5f6368; line-height: 1.65; margin-bottom: 16px;">{{ $form->description }}</p>
        @endif

        {{-- Stats Row --}}
        <div style="display: grid; gap: 1px; background: #dadce0; border-radius: 8px; overflow: hidden; margin-top: 16px; border: 1px solid #dadce0;"
             class="grid-cols-2 sm:grid-cols-4">
            <div class="gf-stat" style="background: #fff;">
                <div class="gf-stat-value" style="color: #1a73e8;">{{ $form->criteria->count() }}</div>
                <div class="gf-stat-label">Criteria</div>
            </div>
            <div class="gf-stat" style="background: #fff;">
                <div class="gf-stat-value" style="color: #4caf50;">{{ $responseCount }}</div>
                <div class="gf-stat-label">Responses</div>
            </div>
            <div class="gf-stat" style="background: #fff;">
                <div class="gf-stat-value" style="color: #ff9800;">{{ $overallAverage }}</div>
                <div class="gf-stat-label">Avg Score</div>
            </div>
            <div class="gf-stat" style="background: #fff;">
                <div class="gf-stat-value" style="color: #2196f3;">{{ $form->criteria->where('type', 'rating')->count() }}</div>
                <div class="gf-stat-label">Rating Items</div>
            </div>
        </div>
    </div>

    {{-- Share / QR Card --}}
    <div class="gf-card" x-data="{ copied: false, copyLink() { const el = document.getElementById('publicUrl'); el.select(); el.setSelectionRange(0, 99999); document.execCommand('copy'); window.getSelection().removeAllRanges(); this.copied = true; setTimeout(() => this.copied = false, 2000); } }">
        <p style="font-size: 16px; color: #202124; font-weight: 400; margin-bottom: 16px;">Share Evaluation Link</p>

        @if($form->is_active)
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
                <input type="text" value="{{ $form->public_url }}" readonly id="publicUrl"
                       style="flex: 1; min-width: 0; padding: 10px 12px; border: 1px solid #dadce0; border-radius: 4px; font-size: 13px; background: #f8f9fa; color: #5f6368; outline: none;">
                <button type="button"
                        @click="copyLink()"
                        style="padding: 10px 16px; border-radius: 4px; font-size: 13px; font-weight: 500; cursor: pointer; transition: background 0.2s; border: none;"
                        :style="copied ? 'background: #e8f5e9; color: #2e7d32;' : 'background: #e8f0fe; color: #1a73e8;'">
                    <span x-show="!copied">Copy Link</span>
                    <span x-show="copied" x-cloak>✓ Copied!</span>
                </button>
            </div>

            <div style="text-align: center;">
                <div style="display: inline-block; padding: 16px; background: #fff; border: 2px solid #dadce0; border-radius: 8px;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($form->public_url) }}"
                         alt="QR Code" style="width: 160px; height: 160px; display: block;">
                </div>
                <p style="font-size: 12px; color: #70757a; margin-top: 8px;">Scan to open evaluation form</p>
            </div>
        @else
            <div style="text-align: center; padding: 24px 0; color: #9e9e9e;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#bdbdbd" stroke-width="1.5" style="margin: 0 auto 8px;"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                <p style="font-size: 14px;">Form is inactive</p>
                <p style="font-size: 12px; margin-top: 4px;">Activate the form to generate a shareable link.</p>
            </div>
        @endif
    </div>

    {{-- Quick Actions --}}
    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding: 8px 0 4px;">
        @if($responseCount > 0)
            <a href="{{ route('evaluation.forms.results', $form) }}" class="gf-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                View Results
            </a>
        @endif
        <a href="{{ route('evaluation.encode.create', ['form_id' => $form->id]) }}" class="gf-btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Encode Hardcopy
        </a>
    </div>

    {{-- Criteria List Card --}}
    <div class="gf-card" style="padding: 0; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #dadce0;">
            <p style="font-size: 16px; color: #202124; font-weight: 400;">Evaluation Criteria</p>
        </div>
        <div style="overflow-x: auto;">
        <table style="width: 100%; font-size: 14px; border-collapse: collapse; min-width: 500px;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 1px solid #dadce0;">
                    <th style="text-align: left; padding: 12px 24px; font-weight: 500; color: #5f6368; font-size: 12px; width: 40px;">#</th>
                    <th style="text-align: left; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Criterion / Question</th>
                    <th style="text-align: center; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px; width: 80px;">Type</th>
                    <th style="text-align: center; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px; width: 80px;">Required</th>
                    @if($responseCount > 0)
                        <th style="text-align: center; padding: 12px 24px; font-weight: 500; color: #5f6368; font-size: 12px; width: 100px;">Avg Score</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($form->criteria as $criterion)
                    <tr style="border-bottom: 1px solid #f1f3f4;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 12px 24px; color: #70757a; font-size: 12px;">{{ $loop->iteration }}</td>
                        <td style="padding: 12px 16px; color: #202124;">{{ $criterion->label }}</td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; text-transform: uppercase;
                                {{ $criterion->type === 'rating' ? 'background: #e3f2fd; color: #1a73e8;' : 'background: #f5f5f5; color: #757575;' }}">
                                {{ $criterion->type }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px; text-align: center; color: #5f6368;">
                            {{ $criterion->is_required ? '✓' : '—' }}
                        </td>
                        @if($responseCount > 0)
                            <td style="padding: 12px 24px; text-align: center;">
                                @if($criterion->type === 'rating' && isset($criteriaStats[$criterion->id]))
                                    <span style="font-weight: 500; color: #202124;">{{ $criteriaStats[$criterion->id]['average'] }}</span>
                                    <span style="font-size: 11px; color: #70757a;">/5</span>
                                @else
                                    <span style="color: #bdbdbd;">—</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @if($form->responses->count())
        <div class="gf-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 24px; border-bottom: 1px solid #dadce0; display: flex; align-items: center; justify-content: space-between;">
                <p style="font-size: 16px; color: #202124; font-weight: 400;">Recent Responses</p>
                <a href="{{ route('evaluation.forms.results', $form) }}" style="font-size: 13px; color: #1a73e8; text-decoration: none; font-weight: 500;">View all →</a>
            </div>
            <div style="overflow-x: auto;">
            <table style="width: 100%; font-size: 14px; border-collapse: collapse; min-width: 600px;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 1px solid #dadce0;">
                        <th style="text-align: left; padding: 12px 24px; font-weight: 500; color: #5f6368; font-size: 12px;">Respondent</th>
                        <th style="text-align: left; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Activity</th>
                        <th style="text-align: center; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Type</th>
                        <th style="text-align: center; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Score</th>
                        <th style="text-align: right; padding: 12px 24px; font-weight: 500; color: #5f6368; font-size: 12px;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($form->responses->take(5) as $response)
                        <tr style="border-bottom: 1px solid #f1f3f4;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 12px 24px; color: #202124;">{{ $response->respondent_name ?: 'Anonymous' }}</td>
                            <td style="padding: 12px 16px; color: #5f6368; font-size: 13px;">{{ Str::limit($response->activity->title ?? '—', 30) }}</td>
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
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif
</div>
@endsection
