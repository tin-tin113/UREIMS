<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $query = User::with('campus');

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (request('role')) {
            $query->where('role', request('role'));
        }

        if (request('status') !== null && request('status') !== '') {
            $query->where('is_active', request('status'));
        }

        if (request('campus_id')) {
            $query->where('campus_id', request('campus_id'));
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $campuses = Campus::orderBy('name')->get();
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        return view('admin.users.index', compact('users', 'campuses', 'totalUsers', 'activeUsers'));
    }

    public function create()
    {
        $campuses = Campus::orderBy('name')->get();
        return view('admin.users.create', compact('campuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'  => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name'   => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', Rule::in(['admin', 'extension_staff'])],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'is_active' => ['boolean'],
        ]);

        $data['password']  = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);

        User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $campuses = Campus::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'campuses'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name'  => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name'   => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', Rule::in(['admin', 'extension_staff'])],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        // Prevent admin from deactivating themselves via the edit form
        if ($user->id === auth()->id() && ! $data['is_active']) {
            return back()->with('error', 'You cannot deactivate your own account.')->withInput();
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function toggleActive(User $user)
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User {$status} successfully.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting users who own programs, projects, or activities.
        // The DB uses restrictOnDelete on created_by, but we provide a
        // user-friendly error message here instead of a raw SQL exception.
        $ownedCount = $user->createdPrograms()->count()
                    + $user->createdProjects()->count()
                    + $user->createdActivities()->count();

        if ($ownedCount > 0) {
            return back()->with('error', 'This user owns ' . $ownedCount . ' record(s) (programs, projects, or activities). Please reassign or delete their records before removing the account, or deactivate the user instead.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
