<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You — URESIMS Evaluation</title>

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
    </style>
</head>
<body class="min-h-screen font-sans antialiased" style="background: #e8f0fe;">

    <div class="w-full max-w-3xl mx-auto px-4 py-6 sm:py-10">

        {{-- Header Card --}}
        <div class="gf-card gf-header-card">
            <h1 style="font-size: 24px; font-weight: 400; color: #202124; margin: 0 0 4px;"
                class="sm:!text-[32px]">{{ $form->title }}</h1>
            <p style="font-size: 14px; color: #70757a; margin: 0;">{{ $form->program->title ?? 'URESIMS Extension Services' }}</p>
        </div>

        {{-- Thank You Card --}}
        <div class="gf-card">
            <div style="display: flex; align-items: flex-start; gap: 16px; margin-bottom: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #e8f5e9; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h2 style="font-size: 20px; font-weight: 400; color: #202124; margin: 0 0 8px;"
                        class="sm:!text-[24px]">Your response has been recorded.</h2>
                    <p style="font-size: 14px; color: #5f6368; line-height: 1.65;">
                        Thank you for taking the time to complete this evaluation. Your feedback is valuable and will help us improve our extension services.
                    </p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display: flex; align-items: center; gap: 12px; padding: 8px 0;">
            <a href="{{ route('evaluation.public.show', $form->access_token) }}"
               style="font-size: 14px; color: #1a73e8; text-decoration: none; font-weight: 500;">
                Submit another response
            </a>
        </div>

        {{-- Footer --}}
        <div style="text-align: center; padding: 24px 0 12px; font-size: 12px; color: #70757a;">
            <p style="margin: 0;">This form was created inside URESIMS.</p>
            <p style="margin: 4px 0 0;">University Research & Extension Services Information Management System</p>
        </div>
    </div>

</body>
</html>
