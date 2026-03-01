@extends('layouts.app')

@section('title', 'Submit Proposal — Start')
@section('page-title', 'Submit ' . ucfirst($type) . ' Proposal')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Stepper --}}
    @include('extension.wizard._stepper', ['currentStep' => 1, 'type' => $type])

    <form method="POST" action="{{ route('proposal.wizard.save-start', $type) }}">
        @csrf
        <input type="hidden" name="_current_step" value="1">

        {{-- Section Policy --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Section Policy</h3>
            </div>
            <div class="px-6 py-4">
                <p class="text-sm text-gray-500 leading-relaxed">Section default policy</p>
            </div>
        </div>

        {{-- Submission Requirements --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Submission Requirements</h3>
            </div>
            <div class="px-6 py-4">
                <p class="text-sm text-gray-500 mb-5 leading-relaxed">
                    You must read and acknowledge that you've completed the requirements below before proceeding.
                </p>
                <div class="space-y-3.5">
                    @foreach($requirements as $index => $req)
                        <label class="flex items-start gap-3 cursor-pointer group">
                            <input type="checkbox"
                                   name="requirements[]"
                                   value="{{ $index }}"
                                   {{ in_array($index, $checkedRequirements) ? 'checked' : '' }}
                                   class="mt-0.5 w-4 h-4 rounded border-gray-300 text-academic-500 focus:ring-academic-500 transition">
                            <span class="text-xs text-gray-600 leading-relaxed group-hover:text-gray-800 transition">
                                {{ $req }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Data Privacy Consent --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading flex items-center gap-2">
                    <svg class="w-4 h-4 text-academic-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Data Privacy Notice
                </h3>
            </div>
            <div class="px-6 py-4">
                <div class="bg-gray-50 border border-gray-100 p-4 mb-4 text-sm text-gray-600 leading-relaxed">
                    {{ $dataPrivacyNotice }}
                </div>
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox"
                           name="privacy_agreed"
                           value="1"
                           {{ $privacyAgreed ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 rounded border-gray-300 text-academic-500 focus:ring-academic-500 transition">
                    <span class="text-xs text-gray-700 font-medium group-hover:text-gray-900 transition leading-relaxed">
                        I have read and agree to the Data Privacy Notice above. I consent to the collection and processing of my personal information as described.
                    </span>
                </label>
            </div>
        </div>

        {{-- Comments for the Reviewer --}}
        <div class="bg-white border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-base font-bold text-academic-heading">Comments for the Reviewer</h3>
            </div>
            <div class="px-6 py-4">
                <p class="text-sm text-gray-500 mb-3 leading-relaxed">Optional — provide any additional notes or context for the reviewer.</p>
                <div class="border border-gray-200 rounded">
                    <div class="flex items-center gap-1 px-3 py-1.5 bg-gray-50 border-b border-gray-200">
                        <button type="button" class="p-1 text-gray-400 hover:text-gray-600 rounded transition" title="Bold">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/></svg>
                        </button>
                        <button type="button" class="p-1 text-gray-400 hover:text-gray-600 rounded transition" title="Italic">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/></svg>
                        </button>
                        <button type="button" class="p-1 text-gray-400 hover:text-gray-600 rounded transition" title="Underline">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg>
                        </button>
                        <div class="w-px h-4 bg-gray-300 mx-1"></div>
                        <button type="button" class="p-1 text-gray-400 hover:text-gray-600 rounded transition" title="Link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </button>
                    </div>
                    <textarea name="comments" rows="4" placeholder="Enter your comments here..."
                              class="w-full px-4 py-3 text-sm text-gray-700 focus:outline-none resize-y border-0">{{ $comments }}</textarea>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between py-2">
            <a href="{{ route('extension.' . $type . 's.index') }}"
               class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <div class="flex items-center gap-3">
                <button type="submit" name="_save_draft" value="1" formaction="{{ route('proposal.wizard.save-draft', $type) }}"
                        class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    Save Draft
                </button>
                <button type="submit"
                        class="px-6 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-medium rounded-lg transition inline-flex items-center gap-2">
                    Save and continue
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection



