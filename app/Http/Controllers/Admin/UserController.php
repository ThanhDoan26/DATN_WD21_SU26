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
     * Display a listing of users with search & filter
     */
    public function index(Request $request)
    {
        $query = User::with(['role', 'cinema']);

        // Tìm kiếm theo tên, email, số điện thoại
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Lọc theo vai trò
        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $roles         = Role::all();
        $totalUsers    = User::count();
        $activeUsers   = User::where('status', 'ACTIVE')->count();
        $inactiveUsers = User::where('status', 'INACTIVE')->count();

        return view('admin.users.index', compact('users', 'roles', 'totalUsers', 'activeUsers', 'inactiveUsers'));
    }

    /**
     * Xem chi tiết người dùng
     */
    public function show(User $user)
    {
        $user->load(['role', 'cinema', 'bookings.showtime.movie', 'reviews']);

        $totalBookings  = $user->bookings()->count();
        $paidBookings   = $user->bookings()->where('status', 'PAID')->count();
        $totalSpent     = $user->bookings()->where('status', 'PAID')->sum('total_price');
        $totalReviews   = $user->reviews()->count();
        $recentBookings = $user->bookings()
                               ->with('showtime.movie')
                               ->orderBy('created_at', 'desc')
                               ->limit(5)
                               ->get();

        return view('admin.users.show', compact(
            'user', 'totalBookings', 'paidBookings', 'totalSpent', 'totalReviews', 'recentBookings'
        ));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles   = Role::all();
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
        $roles   = Role::all();
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
        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'Xóa người dùng thành công!');
    }

    /**
     * Toggle user status (khóa / mở khóa)
     */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể khóa tài khoản của chính mình!'
            ], 400);
        }

        $user->status = $user->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $user->save();

        return response()->json([
            'success' => true,
            'status'  => $user->status,
            'message' => $user->status === 'ACTIVE' ? 'Đã mở khóa tài khoản' : 'Đã khóa tài khoản'
        ]);
    }
}
