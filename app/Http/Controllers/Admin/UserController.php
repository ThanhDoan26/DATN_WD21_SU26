<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Role;
use App\Models\Cinema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;

/**
 * UserController
 * ========================================
 * Controller quản lý users trong hệ thống Admin
 */
class UserController extends AdminController
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::with(['role', 'cinema'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        $cinemas = Cinema::where('status', 'ACTIVE')->get();
        return view('admin.users.create', compact('roles', 'cinemas'));
    }

    /**
     * Store a newly created user in storage
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Thêm người dùng thành công!');
    }

    /**
     * Show the form for editing a user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $cinemas = Cinema::where('status', 'ACTIVE')->get();
        return view('admin.users.edit', compact('user', 'roles', 'cinemas'));
    }

    /**
     * Update a user in storage
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
                         ->with('success', 'Cập nhật người dùng thành công!');
    }

    /**
     * Delete a user from storage
     */
    public function destroy(User $user)
    {
        // Tuỳ rules nghiệp vụ, admin có thể xóa logic hoặc xóa cứng
        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'Xóa người dùng thành công!');
    }
}
