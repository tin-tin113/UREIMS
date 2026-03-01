<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Extension Module') — URESIMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f7f8fa] text-gray-800 antialiased font-sans" x-data="{ sidebarOpen: window.innerWidth >= 1024, mobileMenu: false }" @resize.window="if(window.innerWidth >= 1024) { mobileMenu = false }">

    <div class="flex min-h-screen">

        {{-- Mobile sidebar backdrop --}}
        <div x-show="mobileMenu" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="mobileMenu = false" class="fixed inset-0 z-20 bg-black/50 lg:hidden" x-cloak></div>

        {{-- ======== SIDEBAR ======== --}}
        <aside class="fixed inset-y-0 left-0 z-30 flex flex-col bg-[#0e2439] text-white transition-all duration-300 shadow-xl"
               :class="[
                   sidebarOpen ? 'lg:w-60' : 'lg:w-[68px]',
                   mobileMenu ? 'translate-x-0 w-60' : '-translate-x-full lg:translate-x-0'
               ]">

            {{-- Logo --}}
            <div class="flex items-center gap-3 px-4 h-14 border-b border-white/5 flex-shrink-0">
                <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center font-bold text-sm text-blue-300">
                    U
                </div>
                <div x-show="sidebarOpen" x-cloak class="leading-tight overflow-hidden">
                    <p class="font-bold text-[13px] text-white tracking-tight">URESIMS</p>
                    <p class="text-[10px] text-blue-300/80">Extension Services</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 py-3 px-2.5 space-y-0.5 overflow-y-auto sidebar-scroll">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13px] font-medium transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-gray-200' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                    <span x-show="sidebarOpen" x-cloak>Dashboard</span>
                </a>

                <p x-show="sidebarOpen" x-cloak class="px-3 pt-5 pb-1.5 text-[10px] uppercase tracking-[0.12em] text-gray-500 font-semibold">Extension</p>
                <div x-show="!sidebarOpen" x-cloak class="my-3 mx-3 border-t border-white/10"></div>

                {{-- Programs (expandable) --}}
                @php
                    $pStatusColors = ['proposal'=>'bg-yellow-400','ongoing'=>'bg-cyan-400','completed'=>'bg-green-400'];
                    $pStatusLabels = ['proposal'=>'Proposal','ongoing'=>'Ongoing','completed'=>'Completed'];
                @endphp
                <div x-data="{ progOpen: {{ request()->routeIs('extension.programs.*') ? 'true' : 'false' }} }">
                    <div class="flex items-center">
                        <a href="{{ route('extension.programs.index') }}"
                           class="flex-1 flex items-center gap-3 px-3 py-2 rounded-md text-[13px] font-medium transition-all duration-150 {{ request()->routeIs('extension.programs.*') ? 'bg-white/10 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-gray-200' }}">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span x-show="sidebarOpen" x-cloak>Programs</span>
                        </a>
                        <button x-show="sidebarOpen" x-cloak @click="progOpen = !progOpen" class="p-1.5 text-gray-500 hover:text-gray-300 transition rounded">
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="progOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <div x-show="sidebarOpen && progOpen" x-collapse x-cloak class="ml-[30px] mt-0.5 space-y-0.5 border-l border-white/10 pl-3">
                        @foreach($pStatusLabels as $sKey => $sLabel)
                            @php $cnt = $sidebarProgramCounts[$sKey] ?? 0; @endphp
                            <a href="{{ route('extension.programs.index', ['status' => $sKey]) }}"
                               class="flex items-center gap-2 px-2 py-1 rounded text-[12px] transition-all duration-150 {{ request('status') === $sKey && request()->routeIs('extension.programs.*') ? 'text-white bg-white/10' : 'text-gray-500 hover:text-gray-300 hover:bg-white/5' }}">
                                <span class="w-2 h-2 rounded-full {{ $pStatusColors[$sKey] }} flex-shrink-0"></span>
                                <span class="flex-1">{{ $sLabel }}</span>
                                @if($cnt > 0)<span class="text-[10px] font-semibold text-gray-500">{{ $cnt }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Projects (expandable) --}}
                <div x-data="{ projOpen: {{ request()->routeIs('extension.projects.*') ? 'true' : 'false' }} }">
                    <div class="flex items-center">
                        <a href="{{ route('extension.projects.index') }}"
                           class="flex-1 flex items-center gap-3 px-3 py-2 rounded-md text-[13px] font-medium transition-all duration-150 {{ request()->routeIs('extension.projects.*') ? 'bg-white/10 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-gray-200' }}">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span x-show="sidebarOpen" x-cloak>Projects</span>
                        </a>
                        <button x-show="sidebarOpen" x-cloak @click="projOpen = !projOpen" class="p-1.5 text-gray-500 hover:text-gray-300 transition rounded">
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="projOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <div x-show="sidebarOpen && projOpen" x-collapse x-cloak class="ml-[30px] mt-0.5 space-y-0.5 border-l border-white/10 pl-3">
                        @foreach($pStatusLabels as $sKey => $sLabel)
                            @php $cnt = $sidebarProjectCounts[$sKey] ?? 0; @endphp
                            <a href="{{ route('extension.projects.index', ['status' => $sKey]) }}"
                               class="flex items-center gap-2 px-2 py-1 rounded text-[12px] transition-all duration-150 {{ request('status') === $sKey && request()->routeIs('extension.projects.*') ? 'text-white bg-white/10' : 'text-gray-500 hover:text-gray-300 hover:bg-white/5' }}">
                                <span class="w-2 h-2 rounded-full {{ $pStatusColors[$sKey] }} flex-shrink-0"></span>
                                <span class="flex-1">{{ $sLabel }}</span>
                                @if($cnt > 0)<span class="text-[10px] font-semibold text-gray-500">{{ $cnt }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('extension.activities.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13px] font-medium transition-all duration-150 {{ request()->routeIs('extension.activities.*') ? 'bg-white/10 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-gray-200' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span x-show="sidebarOpen" x-cloak>Activities</span>
                </a>

                {{-- Evaluations (expandable) --}}
                <div x-data="{ evalOpen: {{ request()->routeIs('evaluation.*') ? 'true' : 'false' }} }">
                    <div class="flex items-center">
                        <a href="{{ route('evaluation.forms.index') }}"
                           class="flex-1 flex items-center gap-3 px-3 py-2 rounded-md text-[13px] font-medium transition-all duration-150 {{ request()->routeIs('evaluation.*') ? 'bg-white/10 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-gray-200' }}">
                            <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            <span x-show="sidebarOpen" x-cloak>Evaluations</span>
                        </a>
                        <button x-show="sidebarOpen" x-cloak @click="evalOpen = !evalOpen" class="p-1.5 text-gray-500 hover:text-gray-300 transition rounded">
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="evalOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>
                    <div x-show="sidebarOpen && evalOpen" x-collapse x-cloak class="ml-[30px] mt-0.5 space-y-0.5 border-l border-white/10 pl-3">
                        <a href="{{ route('evaluation.forms.index') }}"
                           class="flex items-center gap-2 px-2 py-1 rounded text-[12px] transition-all duration-150 {{ request()->routeIs('evaluation.forms.*') ? 'text-white bg-white/10' : 'text-gray-500 hover:text-gray-300 hover:bg-white/5' }}">
                            <span class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></span>
                            <span class="flex-1">Forms</span>
                        </a>
                        <a href="{{ route('evaluation.encode.create') }}"
                           class="flex items-center gap-2 px-2 py-1 rounded text-[12px] transition-all duration-150 {{ request()->routeIs('evaluation.encode.*') ? 'text-white bg-white/10' : 'text-gray-500 hover:text-gray-300 hover:bg-white/5' }}">
                            <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                            <span class="flex-1">Encode Hardcopy</span>
                        </a>
                    </div>
                </div>

                @if(auth()->user()->isAdmin())
                <p x-show="sidebarOpen" x-cloak class="px-3 pt-5 pb-1.5 text-[10px] uppercase tracking-[0.12em] text-gray-500 font-semibold">Administration</p>
                <div x-show="!sidebarOpen" x-cloak class="my-3 mx-3 border-t border-white/10"></div>

                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-[13px] font-medium transition-all duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-gray-200' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span x-show="sidebarOpen" x-cloak>Users</span>
                </a>
                @endif
            </nav>

            {{-- User --}}
            <div class="border-t border-white/5 px-3 py-3 flex-shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-[11px] font-semibold text-blue-300 flex-shrink-0">
                        {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}{{ substr(auth()->user()->last_name ?? '', 0, 1) }}
                    </div>
                    <div x-show="sidebarOpen" x-cloak class="flex-1 min-w-0">
                        <p class="text-[12px] font-medium truncate text-gray-200">{{ auth()->user()->full_name ?? 'User' }}</p>
                        <p class="text-[10px] text-gray-500 capitalize">{{ auth()->user()->role ?? 'staff' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" x-show="sidebarOpen" x-cloak>
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-300 transition" title="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ======== MAIN CONTENT ======== --}}
        <div class="flex-1 transition-all duration-300 ml-0 lg:ml-60" :class="sidebarOpen ? 'lg:ml-60' : 'lg:!ml-[68px]'">

            {{-- Top Bar --}}
            <header class="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-gray-200/60 h-14 sm:h-16 px-4 sm:px-8 flex items-center">
                {{-- Left: toggle --}}
                <div class="flex items-center gap-3 w-auto sm:w-48">
                    {{-- Mobile hamburger --}}
                    <button @click="mobileMenu = !mobileMenu" class="text-gray-400 hover:text-gray-600 transition lg:hidden">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    {{-- Desktop sidebar toggle --}}
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-gray-600 transition hidden lg:block">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
                {{-- Center: page title --}}
                <div class="flex-1 text-center">
                    <h1 class="text-base sm:text-lg font-semibold text-gray-800 tracking-tight truncate">@yield('page-title', 'Dashboard')</h1>
                </div>
                {{-- Right: institution + avatar --}}
                <div class="flex items-center justify-end gap-4 w-auto sm:w-48">
                    <span class="text-[11px] text-gray-400 hidden lg:block">CHMSU</span>
                    <div class="flex items-center gap-2.5 pl-4 border-l border-gray-200/80">
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-[11px] font-semibold text-gray-600">
                            {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}{{ substr(auth()->user()->last_name ?? '', 0, 1) }}
                        </div>
                        <span x-show="sidebarOpen" class="text-[12px] text-gray-600 font-medium hidden md:block">{{ auth()->user()->full_name ?? 'User' }}</span>
                    </div>
                </div>
            </header>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="max-w-[1360px] mx-auto mt-5 px-5 py-3.5 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="max-w-[1360px] mx-auto mt-5 px-5 py-3.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Page Content --}}
            <main class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ======== CONFIRMATION MODAL ======== --}}
    <div x-data="confirmModal()" x-cloak
         @confirm-action.window="open($event.detail)"
         x-show="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <div x-show="show" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="cancel()" class="absolute inset-0 bg-black/40"></div>
        <div x-show="show" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 p-6">
            <div class="flex items-center gap-3 mb-3">
                <template x-if="type === 'danger'">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </template>
                <template x-if="type !== 'danger'">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </template>
                <h3 class="text-base font-semibold text-gray-800" x-text="title"></h3>
            </div>
            <p class="text-sm text-gray-500 mb-5 ml-[52px]" x-text="message"></p>
            <div class="flex items-center justify-end gap-2">
                <button @click="cancel()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Cancel</button>
                <button @click="confirm()" class="px-4 py-2 text-white text-sm font-semibold rounded-lg transition"
                        :class="type === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
                        x-text="confirmText"></button>
            </div>
        </div>
    </div>

    <script>
        function confirmModal() {
            return {
                show: false, title: '', message: '', type: 'info', confirmText: 'Confirm', _formEl: null,
                open(detail) {
                    this.title       = detail.title || 'Confirm Action';
                    this.message     = detail.message || 'Are you sure you want to proceed?';
                    this.type        = detail.type || 'info';
                    this.confirmText = detail.confirmText || 'Confirm';
                    this._formEl     = detail.formEl || null;
                    this.show        = true;
                },
                confirm() { this.show = false; if (this._formEl) { this._formEl._confirmed = true; this._formEl.submit(); } },
                cancel() { this.show = false; }
            }
        }
        function confirmSubmit(event, title, message, type, confirmText) {
            if (event.target._confirmed) { event.target._confirmed = false; return true; }
            event.preventDefault();
            window.dispatchEvent(new CustomEvent('confirm-action', {
                detail: { title, message, type: type || 'info', confirmText: confirmText || 'Confirm', formEl: event.target }
            }));
            return false;
        }
    </script>

</body>
</html>
