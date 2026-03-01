@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
{{-- ===== TOOLBAR ===== --}}
<div class="bg-white rounded-xl border border-gray-200 px-4 py-3 mb-5">
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[180px]">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        </div>
        <select name="role" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none min-w-[140px]">
            <option value="">All Roles</option>
            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="extension_staff" {{ request('role') == 'extension_staff' ? 'selected' : '' }}>Extension Staff</option>
        </select>
        <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none min-w-[120px]">
            <option value="">All Status</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <select name="campus_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-[13px] bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none min-w-[160px]">
            <option value="">All Campuses</option>
            @foreach($campuses as $campus)
                <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-[13px] font-medium rounded-lg border border-gray-200 transition">Search</button>
        @if(request()->hasAny(['search','role','status','campus_id']))
            <a href="{{ route('admin.users.index') }}" class="text-[13px] text-gray-400 hover:text-gray-600 transition">Clear</a>
        @endif
        <a href="{{ route('admin.users.create') }}" class="ml-auto inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[13px] font-semibold rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New User
        </a>
    </form>
</div>

{{-- ===== STATS BAR ===== --}}
<div class="flex items-center gap-4 mb-4 text-[12px] text-gray-500">
    <span class="font-medium text-gray-700">{{ $totalUsers }} total</span>
    <span class="text-gray-300">|</span>
    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-400"></span> {{ $activeUsers }} active</span>
    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-300"></span> {{ $totalUsers - $activeUsers }} inactive</span>
</div>

{{-- ===== LIST ===== --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="hidden md:grid grid-cols-12 gap-4 px-5 py-2.5 bg-gray-50/80 border-b border-gray-100 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
        <div class="col-span-3">User</div>
        <div class="col-span-3">Email</div>
        <div class="col-span-2">Campus</div>
        <div class="col-span-1">Role</div>
        <div class="col-span-1">Status</div>
        <div class="col-span-2 text-right">Actions</div>
    </div>
    @forelse($users as $user)
        <div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-4 items-center px-5 py-3.5 border-b border-gray-50 hover:bg-gray-50/50 transition-colors {{ !$user->is_active ? 'opacity-60' : '' }}">
            <div class="col-span-3 min-w-0 flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-[12px] font-bold text-white flex-shrink-0 {{ $user->is_active ? 'bg-gradient-to-br from-blue-400 to-blue-600' : 'bg-gray-300' }}">
                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] font-semibold text-gray-800 truncate">{{ $user->last_name }}, {{ $user->first_name }}{{ $user->middle_name ? ' ' . substr($user->middle_name, 0, 1) . '.' : '' }}</p>
                    <p class="text-[11px] text-gray-400 md:hidden">{{ $user->email }}</p>
                </div>
            </div>
            <div class="col-span-3 text-[12px] text-gray-500 truncate hidden md:block">{{ $user->email }}</div>
            <div class="col-span-2 text-[12px] text-gray-500 truncate hidden md:block">{{ $user->campus->name ?? '—' }}</div>
            <div class="col-span-1 hidden md:block">
                @if($user->role === 'admin')
                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-full bg-purple-100 text-purple-700 border border-purple-200">Admin</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-full bg-blue-50 text-blue-600 border border-blue-100">Staff</span>
                @endif
            </div>
            <div class="col-span-1 hidden md:block">
                @if($user->is_active)
                    <span class="inline-flex items-center gap-1 text-[11px] text-green-600 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Active</span>
                @else
                    <span class="inline-flex items-center gap-1 text-[11px] text-gray-400 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span> Inactive</span>
                @endif
            </div>
            <div class="col-span-2 flex items-center justify-end gap-1.5">
                <a href="{{ route('admin.users.edit', $user) }}" class="px-3 py-1.5 text-[11px] font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition">Edit</a>
                @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="inline">
                        @csrf @method('PATCH')
                        <button class="px-3 py-1.5 text-[11px] font-medium rounded-md transition {{ $user->is_active ? 'text-orange-600 bg-orange-50 hover:bg-orange-100' : 'text-green-600 bg-green-50 hover:bg-green-100' }}">
                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirmSubmit(event, 'Delete User', 'Are you sure you want to permanently delete this user?', 'danger', 'Delete')" class="inline">
                        @csrf @method('DELETE')
                        <button class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                @else
                    <span class="px-2 py-1 text-[10px] text-gray-400 italic">You</span>
                @endif
            </div>
        </div>
    @empty
        <div class="px-5 py-16 text-center">
            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9.12 0A4 4 0 0012 8a4 4 0 00-4.12 6.13M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-gray-400 text-[13px]">No users found.</p>
        </div>
    @endforelse
</div>

@if($users->hasPages())<div class="mt-4">{{ $users->links() }}</div>@endif
@endsection
