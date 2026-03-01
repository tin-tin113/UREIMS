@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="max-w-2xl">
    <nav class="text-sm text-gray-500 mb-6 flex items-center gap-1">
        <a href="{{ route('admin.users.index') }}" class="hover:text-blue-600">Users</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-700 font-medium">Edit — {{ $user->full_name }}</span>
    </nav>

    @if($errors->any())
        <div class="mb-6 px-4 py-3 rounded-lg bg-red-50 border border-red-200">
            <ul class="list-disc list-inside text-sm text-red-600">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user) }}" onsubmit="return confirmSubmit(event, 'Update User', 'Are you sure you want to save these changes?', 'info', 'Save Changes')">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Account Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                    <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="extension_staff" {{ old('role', $user->role) === 'extension_staff' ? 'selected' : '' }}>Extension Staff</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campus</label>
                    <select name="campus_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">— No campus assigned —</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ old('campus_id', $user->campus_id) == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    @if($user->id === auth()->id())
                        <div class="inline-flex items-center gap-2 mt-1.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                            </span>
                            <span class="text-xs text-gray-400">You cannot deactivate your own account</span>
                        </div>
                    @else
                        <label class="inline-flex items-center gap-2 mt-1.5 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm text-gray-600">Active account</span>
                        </label>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Change Password</h2>
            <p class="text-[12px] text-gray-400 mb-4">Leave blank to keep the current password.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">Update User</button>
            <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
