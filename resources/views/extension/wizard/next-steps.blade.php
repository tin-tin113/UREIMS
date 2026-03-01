@extends('layouts.app')

@section('title', 'Submission Complete — Next Steps')
@section('page-title', 'Submit ' . ucfirst($type) . ' Proposal')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Stepper --}}
    @include('extension.wizard._stepper', ['currentStep' => $type === 'program' ? 6 : 5, 'type' => $type])

    {{-- Success Hero --}}
    <div class="bg-white border border-gray-200 mb-6">
        <div class="px-6 py-8 text-center">
            <div class="w-16 h-16 rounded-lg bg-green-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-academic-heading mb-2">Submission Successful!</h3>
            <p class="text-sm text-gray-500 max-w-lg mx-auto">
                Your {{ $type }} proposal <strong class="text-gray-700">"{{ $result['model_title'] }}"</strong>
                has been submitted successfully and is now in <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-700 border border-yellow-200">Proposal</span> status.
            </p>
        </div>
    </div>

    {{-- What Happens Next --}}
    <div class="bg-white border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h4 class="text-base font-bold text-academic-heading">What Happens Next?</h4>
        </div>
        <div class="p-6">
            <div class="space-y-4">
            <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-xs font-bold text-blue-600">1</span>
                </div>
                <div>
                    <h5 class="text-sm font-semibold text-gray-800">Review Process</h5>
                    <p class="text-sm text-gray-500 mt-0.5">Your proposal will be reviewed by the Extension Services office. You may be contacted for additional information or clarifications.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-xs font-bold text-blue-600">2</span>
                </div>
                <div>
                    <h5 class="text-sm font-semibold text-gray-800">Status Updates</h5>
                    <p class="text-sm text-gray-500 mt-0.5">You can track the status of your proposal from your dashboard. The status will be updated as it progresses through the approval process.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-xs font-bold text-blue-600">3</span>
                </div>
                <div>
                    <h5 class="text-sm font-semibold text-gray-800">Edit & Upload More Documents</h5>
                    <p class="text-sm text-gray-500 mt-0.5">You can still edit your {{ $type }} details and upload additional documents from the {{ $type }} detail page at any time.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-xs font-bold text-green-600">4</span>
                </div>
                <div>
                    <h5 class="text-sm font-semibold text-gray-800">Approval & Implementation</h5>
                    <p class="text-sm text-gray-500 mt-0.5">Once approved, your {{ $type }} will move to "Ongoing" status and you can begin tracking activities, beneficiaries, and budget items.</p>
                </div>
            </div>
            </div>
        </div>
    </div>

    {{-- Important Reminders --}}
    <div class="bg-amber-50 border border-amber-200 p-6 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h5 class="text-sm font-semibold text-amber-800">Important Reminders</h5>
                <ul class="mt-2 text-sm text-amber-700 space-y-1 list-disc list-inside">
                    <li>Ensure all required documents are uploaded before the review deadline.</li>
                    <li>Keep your contact information up to date for correspondence.</li>
                    <li>If you need to make changes, edit the {{ $type }} directly from the detail page.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex items-center justify-between py-2">
        <a href="{{ route('extension.' . $type . 's.index') }}"
           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to {{ ucfirst($type) }}s
        </a>

        <div class="flex items-center gap-3">
            <a href="{{ route('proposal.wizard.start', $type) }}"
               class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                Submit Another
            </a>
            <a href="{{ route('extension.' . $type . 's.show', $result['model_id']) }}"
               class="px-6 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-medium rounded-lg shadow-sm transition inline-flex items-center gap-2">
                View {{ ucfirst($type) }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</div>
@endsection




