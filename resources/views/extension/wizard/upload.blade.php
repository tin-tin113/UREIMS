@extends('layouts.app')

@section('title', 'Submit Proposal — Upload Documents')
@section('page-title', 'Submit ' . ucfirst($type) . ' Proposal')

@section('content')
<div class="max-w-4xl mx-auto" x-data="uploadManager()">

    {{-- Stepper --}}
    @include('extension.wizard._stepper', ['currentStep' => 2, 'type' => $type])

    {{-- Files Section --}}
    <div class="bg-white border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-academic-heading">Upload Submission</h3>
                <p class="text-sm text-gray-500 mt-0.5">Upload supporting documents for this proposal.</p>
            </div>
            <button type="button" @click="showUploadForm = !showUploadForm"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-academic-500 hover:bg-academic-600 text-white text-sm font-medium rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add File
            </button>
        </div>

        {{-- Upload Form (inline, toggleable) --}}
        <div x-show="showUploadForm" x-cloak x-transition.opacity.duration.200ms>
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/60">
                <form method="POST" action="{{ route('proposal.wizard.save-upload', $type) }}" enctype="multipart/form-data">
                    @csrf

                    {{-- Drag & Drop Zone --}}
                    <div class="relative border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer bg-white"
                         :class="dragOver ? 'border-academic-500 bg-blue-50/50' : 'border-gray-300 hover:border-academic-500/50'"
                         @dragover.prevent="dragOver = true"
                         @dragleave.prevent="dragOver = false"
                         @drop.prevent="handleDrop($event)">
                        <input type="file" name="file" required
                               @change="fileName = $event.target.files[0]?.name || ''"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.rtf,.pptx"
                               id="file-input">
                        <div class="pointer-events-none">
                            <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm text-gray-500" x-show="!fileName">
                                <span class="font-medium text-academic-500">Click to browse</span> or drag and drop
                            </p>
                            <p class="text-sm text-academic-500 font-medium" x-show="fileName" x-text="fileName"></p>
                            <p class="text-xs text-gray-400 mt-1">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, RTF, PPTX · Max 20 MB</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-4">
                        <button type="submit"
                                class="px-5 py-2 bg-academic-500 hover:bg-academic-600 text-white text-sm font-medium rounded-lg transition shadow-sm">
                            Upload
                        </button>
                        <button type="button" @click="showUploadForm = false; fileName = ''"
                                class="px-5 py-2 text-gray-500 hover:text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Error Messages --}}
        @if($errors->any())
            <div class="mx-6 mt-4 px-4 py-3 bg-red-50 border border-red-100">
                <ul class="list-disc list-inside text-sm text-red-600 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Uploaded Files List --}}
        <div class="px-6 py-4">
            @if(count($uploadedFiles) > 0)
                <ul class="divide-y divide-gray-100">
                    @foreach($uploadedFiles as $index => $file)
                        @php $ext = strtolower(pathinfo($file['original_name'], PATHINFO_EXTENSION)); @endphp
                        <li class="py-3.5 first:pt-0 last:pb-0">
                            {{-- Normal row --}}
                            <div x-show="editingFile !== {{ $index }}" class="flex items-center gap-3">
                                {{-- File icon --}}
                                <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center
                                    @if(in_array($ext, ['pdf']))
                                        bg-red-50 text-red-500
                                    @elseif(in_array($ext, ['doc', 'docx', 'rtf']))
                                        bg-blue-50 text-blue-500
                                    @elseif(in_array($ext, ['xls', 'xlsx']))
                                        bg-green-50 text-green-600
                                    @elseif(in_array($ext, ['jpg', 'jpeg', 'png']))
                                        bg-purple-50 text-purple-500
                                    @elseif(in_array($ext, ['pptx']))
                                        bg-orange-50 text-orange-500
                                    @else
                                        bg-gray-50 text-gray-400
                                    @endif
                                ">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                </div>

                                {{-- File name & size --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 truncate">{{ $file['original_name'] }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ number_format($file['size'] / 1024, 1) }} KB</p>
                                </div>

                                {{-- Label badge --}}
                                @if(!empty($file['label']) && $file['label'] !== 'Uncategorized')
                                    <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                        {{ $file['label'] }}
                                    </span>
                                @endif

                                {{-- Action buttons --}}
                                <div class="flex items-center gap-1.5">
                                    <button type="button"
                                            @click="editingFile = {{ $index }}; editLabel = '{{ addslashes($file['label'] ?? 'Uncategorized') }}'"
                                            class="px-3 py-1.5 text-xs font-medium text-academic-500 hover:bg-blue-50 rounded-lg transition">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('proposal.wizard.remove-file', $type) }}"
                                          onsubmit="return confirm('Remove this file?')">
                                        @csrf
                                        <input type="hidden" name="file_index" value="{{ $index }}">
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50 rounded-lg transition">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Inline edit row --}}
                            <div x-show="editingFile === {{ $index }}" x-cloak class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-9"></div>
                                <form method="POST" action="{{ route('proposal.wizard.update-file-label', $type) }}" class="flex flex-1 items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="file_index" value="{{ $index }}">
                                    <select name="new_label" x-model="editLabel"
                                            class="flex-1 px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-academic-500 focus:border-academic-500 outline-none bg-white">
                                        <option value="Uncategorized">— No category —</option>
                                        @foreach($fileLabels as $label)
                                            <option value="{{ $label }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                            class="px-4 py-1.5 text-xs font-medium text-white bg-academic-500 hover:bg-academic-600 rounded-lg transition">
                                        Save
                                    </button>
                                    <button type="button" @click="editingFile = null"
                                            class="px-4 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-100 rounded-lg transition">
                                        Cancel
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-center py-12">
                    <svg class="w-10 h-10 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <p class="text-sm text-gray-400">No files uploaded yet</p>
                    <p class="text-xs text-gray-300 mt-1">Click <span class="font-medium">Add File</span> to get started.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between py-2">
        <a href="{{ route('proposal.wizard.start', $type) }}"
           class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('proposal.wizard.save-draft', $type) }}">
                @csrf
                <input type="hidden" name="_current_step" value="2">
                <button type="submit"
                        class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition">
                    Save Draft
                </button>
            </form>
            <form method="POST" action="{{ route('proposal.wizard.save-upload-continue', $type) }}">
                @csrf
                <button type="submit"
                        class="px-6 py-2.5 bg-academic-500 hover:bg-academic-600 text-white text-sm font-medium rounded-lg shadow-sm transition inline-flex items-center gap-2">
                    Save and Continue
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function uploadManager() {
        return {
            showUploadForm: false,
            fileName: '',
            dragOver: false,
            editingFile: null,
            editLabel: '',
            handleDrop(event) {
                this.dragOver = false;
                const file = event.dataTransfer.files[0];
                if (file) {
                    this.fileName = file.name;
                    document.getElementById('file-input').files = event.dataTransfer.files;
                }
            }
        }
    }
</script>
@endsection



