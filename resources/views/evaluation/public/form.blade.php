<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form->title }} — URESIMS Evaluation</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .gf-card {
            background: #fff;
            border: 1px solid #dadce0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 12px;
        }
        @media (min-width: 640px) {
            .gf-card { padding: 28px 32px; }
        }
        .gf-header-card {
            border-top: 10px solid #1a73e8;
        }
        .gf-radio-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f4;
            cursor: pointer;
        }
        .gf-radio-row:last-child { border-bottom: none; }
        .gf-radio-row:hover { background: #f8f9fa; margin: 0 -24px; padding: 10px 24px; }
        .gf-radio-outer {
            width: 20px; height: 20px; border-radius: 50%;
            border: 2px solid #5f6368; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            transition: border-color 0.2s;
        }
        .gf-radio-inner {
            width: 10px; height: 10px; border-radius: 50%;
            background: transparent; transition: background 0.2s;
        }
        input[type="radio"]:checked + .gf-radio-label .gf-radio-outer {
            border-color: #1a73e8;
        }
        input[type="radio"]:checked + .gf-radio-label .gf-radio-inner {
            background: #1a73e8;
        }
        .gf-scale-container {
            display: flex; align-items: center; justify-content: space-between;
            gap: 0; padding: 16px 0 8px;
        }
        .gf-scale-option {
            display: flex; flex-direction: column; align-items: center; gap: 8px;
            cursor: pointer; flex: 1;
        }
        .gf-scale-option .gf-radio-outer { margin: 0 auto; }
        .gf-scale-label { font-size: 14px; color: #202124; }
        .gf-input-underline {
            border: none; border-bottom: 1px solid #dadce0; border-radius: 0;
            padding: 8px 0; font-size: 14px; width: 100%; outline: none;
            transition: border-color 0.2s;
        }
        .gf-input-underline:focus {
            border-bottom: 2px solid #1a73e8; margin-bottom: -1px;
        }
        .gf-textarea {
            border: 1px solid #dadce0; border-radius: 4px;
            padding: 12px; font-size: 14px; width: 100%; min-height: 80px;
            resize: vertical; outline: none; transition: border-color 0.2s;
            font-family: inherit;
        }
        .gf-textarea:focus { border-color: #1a73e8; box-shadow: 0 0 0 1px #1a73e8; }
        .gf-required { color: #d93025; margin-left: 4px; }
        .gf-select {
            width: 100%; padding: 12px; border: 1px solid #dadce0; border-radius: 4px;
            font-size: 14px; background: #fff; outline: none; cursor: pointer;
            transition: border-color 0.2s; appearance: auto;
        }
        .gf-select:focus { border-color: #1a73e8; box-shadow: 0 0 0 1px #1a73e8; }
        .gf-submit-btn {
            background: #1a73e8; color: #fff; border: none;
            padding: 10px 24px; border-radius: 4px; font-size: 14px;
            font-weight: 500; cursor: pointer; transition: background 0.2s;
            letter-spacing: 0.25px;
        }
        .gf-submit-btn:hover { background: #1557b0; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
        .gf-section-title { font-size: 16px; color: #202124; font-weight: 400; line-height: 1.5; }
        .gf-question-title { font-size: 14px; color: #202124; font-weight: 400; line-height: 1.5; margin-bottom: 16px; }
    </style>
</head>
<body class="min-h-screen font-sans antialiased" style="background: #e8f0fe;" x-data="evaluationForm()">

    <div class="w-full max-w-3xl mx-auto px-4 py-6 sm:py-10">

        {{-- Header Card --}}
        <div class="gf-card gf-header-card">
            <h1 style="font-size: 24px; font-weight: 400; color: #202124; margin: 0 0 4px;"
                class="sm:!text-[32px]">{{ $form->title }}</h1>
            <p style="font-size: 14px; color: #70757a; margin: 0 0 16px;">{{ $form->program->title ?? 'URESIMS Extension Services' }}</p>

            @if($form->description)
                <p style="font-size: 14px; color: #202124; line-height: 1.65; white-space: pre-line;">{{ $form->description }}</p>
            @endif

            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #dadce0;">
                <p style="font-size: 13px; color: #d93025;">* Indicates required question</p>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="gf-card" style="border-left: 4px solid #d93025; background: #fce8e6;">
                <p style="font-size: 14px; font-weight: 500; color: #c5221f; margin-bottom: 8px;">Please fix the following errors:</p>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li style="font-size: 13px; color: #c5221f; margin-bottom: 2px;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('evaluation.public.submit', $form->access_token) }}">
            @csrf

            {{-- Activity Selection Card --}}
            <div class="gf-card">
                <p class="gf-question-title">
                    Which activity are you evaluating?<span class="gf-required">*</span>
                </p>

                @if($activities->count())
                    <select name="extension_activity_id" required x-model="selectedActivity" class="gf-select">
                        <option value="">Choose…</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}">{{ $activity->title }}</option>
                        @endforeach
                    </select>
                @else
                    <p style="font-size: 14px; color: #70757a; font-style: italic;">No activities available for evaluation at this time.</p>
                @endif
            </div>

            {{-- Respondent Info Card --}}
            <div class="gf-card" x-show="selectedActivity" x-transition.opacity x-cloak>
                <p class="gf-section-title" style="margin-bottom: 20px;">Your Information</p>

                <div style="margin-bottom: 20px;">
                    <label style="font-size: 14px; color: #202124; display: block; margin-bottom: 8px;">Full Name</label>
                    <input type="text" name="respondent_name" value="{{ old('respondent_name') }}"
                           placeholder="Your answer" class="gf-input-underline">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-size: 14px; color: #202124; display: block; margin-bottom: 8px;">Email</label>
                    <input type="email" name="respondent_email" value="{{ old('respondent_email') }}"
                           placeholder="Your answer" class="gf-input-underline">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-size: 14px; color: #202124; display: block; margin-bottom: 8px;">Contact Number</label>
                    <input type="text" name="respondent_contact" value="{{ old('respondent_contact') }}"
                           placeholder="Your answer" class="gf-input-underline">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-size: 14px; color: #202124; display: block; margin-bottom: 8px;">Organization</label>
                    <input type="text" name="respondent_organization" value="{{ old('respondent_organization') }}"
                           placeholder="Your answer" class="gf-input-underline">
                </div>

                <div>
                    <label style="font-size: 14px; color: #202124; display: block; margin-bottom: 12px;">Sex</label>
                    <label class="gf-radio-row">
                        <input type="radio" name="respondent_gender" value="male" class="sr-only" {{ old('respondent_gender') === 'male' ? 'checked' : '' }}>
                        <span class="gf-radio-label flex items-center gap-3">
                            <span class="gf-radio-outer"><span class="gf-radio-inner"></span></span>
                            <span class="gf-scale-label">Male</span>
                        </span>
                    </label>
                    <label class="gf-radio-row">
                        <input type="radio" name="respondent_gender" value="female" class="sr-only" {{ old('respondent_gender') === 'female' ? 'checked' : '' }}>
                        <span class="gf-radio-label flex items-center gap-3">
                            <span class="gf-radio-outer"><span class="gf-radio-inner"></span></span>
                            <span class="gf-scale-label">Female</span>
                        </span>
                    </label>
                </div>
            </div>

            {{-- Rating Scale Legend --}}
            @if($form->criteria->where('type', 'rating')->count())
                <div class="gf-card" x-show="selectedActivity" x-transition.opacity x-cloak>
                    <p class="gf-section-title" style="margin-bottom: 8px;">Rating Scale</p>
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
            @foreach($form->criteria as $criterion)
                <div class="gf-card" x-show="selectedActivity" x-transition.opacity x-cloak>
                    <p class="gf-question-title">
                        {{ $criterion->label }}
                        @if($criterion->is_required)<span class="gf-required">*</span>@endif
                    </p>

                    @if($criterion->type === 'rating')
                        {{-- Google Forms linear scale style --}}
                        <div class="gf-scale-container">
                            @for($n = 1; $n <= 5; $n++)
                                <label class="gf-scale-option">
                                    <span class="gf-scale-label">{{ $n }}</span>
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
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0;" x-show="selectedActivity" x-transition.opacity x-cloak>
                <button type="submit" class="gf-submit-btn">Submit</button>
                <a href="{{ route('evaluation.public.show', $form->access_token) }}"
                   style="font-size: 14px; color: #1a73e8; text-decoration: none; font-weight: 500;">Clear form</a>
            </div>
        </form>

        {{-- Footer (Google Forms style) --}}
        <div style="text-align: center; padding: 24px 0 12px; font-size: 12px; color: #70757a;">
            <p style="margin: 0;">This form was created inside URESIMS.</p>
            <p style="margin: 4px 0 0;">University Research & Extension Services Information Management System</p>
        </div>
    </div>

    <script>
    function evaluationForm() {
        return {
            selectedActivity: '{{ old('extension_activity_id', '') }}'
        }
    }
    </script>

</body>
</html>
