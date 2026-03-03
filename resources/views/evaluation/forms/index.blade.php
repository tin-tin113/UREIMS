@extends('layouts.app')

@section('title', 'Evaluation Forms')
@section('page-title', 'Evaluation Forms')

@section('content')
<style>
    .gf-card { background: #fff; border: 1px solid #dadce0; border-radius: 8px; padding: 20px; margin-bottom: 12px; }
    @media (min-width: 640px) { .gf-card { padding: 28px 32px; } }
    .gf-header-card { border-top: 10px solid #1a73e8; }
    .gf-btn-primary { background: #1a73e8; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s; letter-spacing: 0.25px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .gf-btn-primary:hover { background: #1557b0; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
    .gf-select { padding: 10px 12px; border: 1px solid #dadce0; border-radius: 4px; font-size: 14px; background: #fff; outline: none; cursor: pointer; min-width: 220px; }
    .gf-select:focus { border-color: #1a73e8; box-shadow: 0 0 0 1px #1a73e8; }
</style>

<div class="w-full">

    {{-- Header Card --}}
    <div class="gf-card gf-header-card">
        <div style="display: flex; gap: 16px;" class="flex-col sm:flex-row sm:items-start sm:justify-between">
            <div style="min-width: 0;">
                <h1 style="font-size: 24px; font-weight: 400; color: #202124; margin: 0 0 4px;" class="sm:!text-[32px]">Evaluation Forms</h1>
                <p style="font-size: 14px; color: #70757a; margin: 0;">Create and manage evaluation forms for extension programs.</p>
            </div>
            <a href="{{ route('evaluation.forms.create') }}" class="gf-btn-primary" style="flex-shrink: 0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Form
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="gf-card" style="display: flex; align-items: center; gap: 12px;">
        <form method="GET" style="display: flex; align-items: center; gap: 12px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5f6368" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            <select name="program_id" class="gf-select" onchange="this.form.submit()">
                <option value="">All Programs</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                        {{ $program->title }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Forms Table Card --}}
    <div class="gf-card" style="padding: 0; overflow: hidden;">
        @if($forms->count())
            <div style="overflow-x: auto;">
            <table style="width: 100%; font-size: 14px; border-collapse: collapse; min-width: 700px;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 1px solid #dadce0;">
                        <th style="text-align: left; padding: 12px 24px; font-weight: 500; color: #5f6368; font-size: 12px; width: 40px;">#</th>
                        <th style="text-align: left; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Title</th>
                        <th style="text-align: left; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Program</th>
                        <th style="text-align: left; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Project</th>
                        <th style="text-align: center; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Criteria</th>
                        <th style="text-align: center; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Responses</th>
                        <th style="text-align: center; padding: 12px 16px; font-weight: 500; color: #5f6368; font-size: 12px;">Status</th>
                        <th style="text-align: right; padding: 12px 24px; font-weight: 500; color: #5f6368; font-size: 12px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forms as $form)
                        <tr style="border-bottom: 1px solid #f1f3f4; transition: background 0.15s;"
                            onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 14px 24px; color: #70757a; font-size: 12px;">{{ $loop->iteration + ($forms->currentPage() - 1) * $forms->perPage() }}</td>
                            <td style="padding: 14px 16px;">
                                <a href="{{ route('evaluation.forms.show', $form) }}"
                                   style="color: #1a73e8; text-decoration: none; font-weight: 500; transition: color 0.15s;"
                                   onmouseover="this.style.color='#1557b0'" onmouseout="this.style.color='#1a73e8'">
                                    {{ $form->title }}
                                </a>
                            </td>
                            <td style="padding: 14px 16px; color: #5f6368; font-size: 13px;">{{ Str::limit($form->program->title ?? '—', 40) }}</td>
                            <td style="padding: 14px 16px; color: #5f6368; font-size: 13px;">{{ Str::limit($form->project->title ?? '—', 30) }}</td>
                            <td style="padding: 14px 16px; text-align: center; color: #5f6368;">{{ $form->criteria_count }}</td>
                            <td style="padding: 14px 16px; text-align: center;">
                                @if($form->responses_count > 0)
                                    <a href="{{ route('evaluation.forms.results', $form) }}"
                                       style="color: #1a73e8; text-decoration: none; font-weight: 500;">
                                        {{ $form->responses_count }}
                                    </a>
                                @else
                                    <span style="color: #bdbdbd;">0</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; text-align: center;">
                                <form method="POST" action="{{ route('evaluation.forms.toggle-active', $form) }}" style="display: inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 500; border: none; cursor: pointer; transition: background 0.2s;
                                        {{ $form->is_active ? 'background: #e8f5e9; color: #2e7d32;' : 'background: #f5f5f5; color: #757575;' }}">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; display: inline-block;
                                            {{ $form->is_active ? 'background: #4caf50;' : 'background: #bdbdbd;' }}"></span>
                                        {{ $form->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td style="padding: 14px 24px;">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                                    <a href="{{ route('evaluation.forms.show', $form) }}"
                                       style="padding: 6px; color: #5f6368; border-radius: 50%; display: inline-flex; transition: background 0.2s;"
                                       onmouseover="this.style.background='#e3f2fd'; this.style.color='#1a73e8'" onmouseout="this.style.background='transparent'; this.style.color='#5f6368'"
                                       title="View">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('evaluation.forms.edit', $form) }}"
                                       style="padding: 6px; color: #5f6368; border-radius: 50%; display: inline-flex; transition: background 0.2s;"
                                       onmouseover="this.style.background='#fff3e0'; this.style.color='#ef6c00'" onmouseout="this.style.background='transparent'; this.style.color='#5f6368'"
                                       title="Edit">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('evaluation.forms.duplicate', $form) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit"
                                                style="padding: 6px; color: #5f6368; border: none; background: transparent; border-radius: 50%; display: inline-flex; cursor: pointer; transition: background 0.2s;"
                                                onmouseover="this.style.background='#e8f5e9'; this.style.color='#2e7d32'" onmouseout="this.style.background='transparent'; this.style.color='#5f6368'"
                                                title="Duplicate">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('evaluation.forms.destroy', $form) }}" style="display: inline;"
                                          onsubmit="return confirmSubmit(event, 'Delete Form', 'This will permanently delete this evaluation form and all its responses. Continue?', 'danger', 'Delete')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                style="padding: 6px; color: #5f6368; border: none; background: transparent; border-radius: 50%; display: inline-flex; cursor: pointer; transition: background 0.2s;"
                                                onmouseover="this.style.background='#fce4ec'; this.style.color='#c62828'" onmouseout="this.style.background='transparent'; this.style.color='#5f6368'"
                                                title="Delete">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            @if($forms->hasPages())
                <div style="padding: 16px 24px; border-top: 1px solid #dadce0;">
                    {{ $forms->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 48px 24px;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#bdbdbd" stroke-width="1.5" style="margin: 0 auto 12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p style="font-size: 14px; color: #5f6368; margin: 0 0 12px;">No evaluation forms yet.</p>
                <a href="{{ route('evaluation.forms.create') }}" style="font-size: 14px; color: #1a73e8; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Create your first form
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
