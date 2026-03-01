@extends('layouts.app')

@section('title', 'Encode Hardcopy Evaluation')
@section('page-title', 'Encode Hardcopy Evaluation')

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
    .gf-question-title { font-size: 14px; color: #202124; font-weight: 400; line-height: 1.5; margin-bottom: 16px; }
    .gf-radio-outer { width: 20px; height: 20px; border-radius: 50%; border: 2px solid #5f6368; flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: border-color 0.2s; }
    .gf-radio-inner { width: 10px; height: 10px; border-radius: 50%; background: transparent; transition: background 0.2s; }
    input[type="radio"]:checked + .gf-radio-label .gf-radio-outer,
    input[type="radio"]:checked + span .gf-radio-outer { border-color: #1a73e8; }
    input[type="radio"]:checked + .gf-radio-label .gf-radio-inner,
    input[type="radio"]:checked + span .gf-radio-inner { background: #1a73e8; }
    .gf-scale-container { display: flex; align-items: center; justify-content: space-between; gap: 0; padding: 16px 0 8px; }
    .gf-scale-option { display: flex; flex-direction: column; align-items: center; gap: 8px; cursor: pointer; flex: 1; }
    .gf-radio-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f3f4; cursor: pointer; }
    .gf-radio-row:last-child { border-bottom: none; }
    .gf-radio-row:hover { background: #f8f9fa; margin: 0 -24px; padding: 10px 24px; }
</style>

<div x-data="encodeForm()" class="w-full max-w-3xl mx-auto">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm mb-4" style="color: #5f6368;">
        <a href="{{ route('evaluation.forms.index') }}" class="hover:underline" style="color: #1a73e8;">Evaluation Forms</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color: #202124; font-weight: 500;">Encode Hardcopy</span>
    </div>

    {{-- Header Card --}}
    <div class="gf-card gf-header-card">
        <h1 style="font-size: 24px; font-weight: 400; color: #202124; margin: 0 0 4px;" class="sm:!text-[32px]">Encode Hardcopy Evaluation</h1>
        <p style="font-size: 14px; color: #70757a; margin: 0 0 16px;">Enter paper evaluation responses into the system. Scores are computed automatically.</p>
        <div style="padding-top: 16px; border-top: 1px solid #dadce0;">
            <p style="font-size: 13px; color: #d93025;">* Indicates required question</p>
        </div>
    </div>

    {{-- Step 1: Select Form --}}
    <div class="gf-card">
        <p class="gf-question-title">
            Select Evaluation Form<span class="gf-required">*</span>
        </p>
        <form method="GET" action="{{ route('evaluation.encode.create') }}">
            <select name="form_id" class="gf-select" onchange="this.form.submit()">
                <option value="">Choose an evaluation form…</option>
                @foreach($forms as $f)
                    <option value="{{ $f->id }}" {{ request('form_id') == $f->id ? 'selected' : '' }}>
                        {{ $f->title }} — {{ $f->program->title ?? '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($selectedForm)
        <form method="POST" action="{{ route('evaluation.encode.store') }}">
            @csrf
            <input type="hidden" name="evaluation_form_id" value="{{ $selectedForm->id }}">

            {{-- Step 2: Select Activity --}}
            <div class="gf-card">
                <p class="gf-question-title">
                    Which activity is being evaluated?<span class="gf-required">*</span>
                </p>
                <select name="extension_activity_id" required class="gf-select">
                    <option value="">Choose an activity…</option>
                    @foreach($activities as $activity)
                        <option value="{{ $activity->id }}" {{ old('extension_activity_id') == $activity->id ? 'selected' : '' }}>
                            {{ $activity->title }}
                        </option>
                    @endforeach
                </select>
                @error('extension_activity_id')<p style="font-size: 12px; color: #d93025; margin-top: 4px;">{{ $message }}</p>@enderror
            </div>

            {{-- Respondent Info Card --}}
            <div class="gf-card">
                <p style="font-size: 16px; color: #202124; font-weight: 400; margin-bottom: 20px;">Respondent Information</p>

                <div style="margin-bottom: 20px;">
                    <label class="gf-label">Full Name</label>
                    <input type="text" name="respondent_name" value="{{ old('respondent_name') }}"
                           placeholder="Your answer" class="gf-input">
                </div>

                <div style="margin-bottom: 20px;">
                    <label class="gf-label">Email</label>
                    <input type="email" name="respondent_email" value="{{ old('respondent_email') }}"
                           placeholder="Your answer" class="gf-input">
                </div>

                <div style="margin-bottom: 20px;">
                    <label class="gf-label">Contact Number</label>
                    <input type="text" name="respondent_contact" value="{{ old('respondent_contact') }}"
                           placeholder="Your answer" class="gf-input">
                </div>

                <div style="margin-bottom: 20px;">
                    <label class="gf-label">Organization</label>
                    <input type="text" name="respondent_organization" value="{{ old('respondent_organization') }}"
                           placeholder="Your answer" class="gf-input">
                </div>

                <div>
                    <label class="gf-label" style="margin-bottom: 12px;">Sex</label>
                    <label class="gf-radio-row">
                        <input type="radio" name="respondent_gender" value="male" class="sr-only" {{ old('respondent_gender') === 'male' ? 'checked' : '' }}>
                        <span class="gf-radio-label" style="display: flex; align-items: center; gap: 12px;">
                            <span class="gf-radio-outer"><span class="gf-radio-inner"></span></span>
                            <span style="font-size: 14px; color: #202124;">Male</span>
                        </span>
                    </label>
                    <label class="gf-radio-row">
                        <input type="radio" name="respondent_gender" value="female" class="sr-only" {{ old('respondent_gender') === 'female' ? 'checked' : '' }}>
                        <span class="gf-radio-label" style="display: flex; align-items: center; gap: 12px;">
                            <span class="gf-radio-outer"><span class="gf-radio-inner"></span></span>
                            <span style="font-size: 14px; color: #202124;">Female</span>
                        </span>
                    </label>
                </div>
            </div>

            {{-- Rating Scale Legend --}}
            @if($selectedForm->criteria->where('type', 'rating')->count())
                <div class="gf-card">
                    <p style="font-size: 16px; color: #202124; font-weight: 400; margin-bottom: 8px;">Rating Scale</p>
                    <div style="font-size: 14px; color: #5f6368; line-height: 1.8;">
                        <span style="font-weight: 500; color: #202124;">1</span> — Poor &nbsp;&nbsp;
                        <span style="font-weight: 500; color: #202124;">2</span> — Fair &nbsp;&nbsp;
                        <span style="font-weight: 500; color: #202124;">3</span> — Good &nbsp;&nbsp;
                        <span style="font-weight: 500; color: #202124;">4</span> — Very Good &nbsp;&nbsp;
                        <span style="font-weight: 500; color: #202124;">5</span> — Excellent
                    </div>
                </div>
            @endif

            {{-- Each question in its own card --}}
            @foreach($selectedForm->criteria as $criterion)
                <div class="gf-card">
                    <p class="gf-question-title">
                        {{ $criterion->label }}
                        @if($criterion->is_required)<span class="gf-required">*</span>@endif
                    </p>

                    @if($criterion->type === 'rating')
                        <div class="gf-scale-container">
                            @for($n = 1; $n <= 5; $n++)
                                <label class="gf-scale-option">
                                    <span style="font-size: 14px; color: #202124;">{{ $n }}</span>
                                    <input type="radio" name="answers[{{ $criterion->id }}]" value="{{ $n }}"
                                           {{ old("answers.{$criterion->id}") == $n ? 'checked' : '' }}
                                           class="sr-only" {{ $criterion->is_required ? 'required' : '' }}>
                                    <span class="gf-radio-outer"><span class="gf-radio-inner"></span></span>
                                </label>
                            @endfor
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 4px 0 0; font-size: 12px; color: #70757a;">
                            <span>Poor</span>
                            <span>Excellent</span>
                        </div>
                    @else
                        <textarea name="answers[{{ $criterion->id }}]"
                                  placeholder="Your answer"
                                  class="gf-textarea"
                                  {{ $criterion->is_required ? 'required' : '' }}>{{ old("answers.{$criterion->id}") }}</textarea>
                    @endif
                </div>
            @endforeach

            {{-- Submit --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0;">
                @if($selectedForm)
                    <a href="{{ route('evaluation.forms.show', $selectedForm) }}" style="font-size: 14px; color: #1a73e8; text-decoration: none; font-weight: 500;">← Back to form</a>
                @else
                    <span></span>
                @endif
                <button type="submit" class="gf-btn-primary">Save Encoded Response</button>
            </div>
        </form>
    @endif
</div>

<script>
function encodeForm() {
    return {}
}
</script>
@endsection
