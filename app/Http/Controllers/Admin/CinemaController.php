<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cinema;

/**
 * CinemaController
 * ========================================
 * Controller quản lý cinemas - Read-only (Index only)
 */
class CinemaController extends AdminController
{
    /**
     * Display a listing of cinemas
     */
    public function index()
    {
        $cinemas = Cinema::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.cinemas.index', ['cinemas' => $cinemas]);
    }

    /**
     * Show the form for creating a new cinema
     */
    public function create()
    {
        return view('admin.cinemas.create');
    }

    /**
     * Store a newly created cinema in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        Cinema::create($validated);

        return redirect()->route('admin.cinemas.index')
                         ->with('success', 'Thêm rạp chiếu phim thành công!');
    }

    /**
     * Display the specified cinema
     */
    public function show(Cinema $cinema)
    {
        $cinema->load('rooms');
        return view('admin.cinemas.show', compact('cinema'));
    }

    /**
     * Show the form for editing the specified cinema
     */
    public function edit(Cinema $cinema)
    {
        return view('admin.cinemas.edit', compact('cinema'));
    }

    /**
     * Update the specified cinema in storage
     */
    public function update(Request $request, Cinema $cinema)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $cinema->update($validated);

        return redirect()->route('admin.cinemas.index')
                         ->with('success', 'Cập nhật rạp chiếu phim thành công!');
    }

    /**
     * Remove the specified cinema from storage
     */
    public function destroy(Cinema $cinema)
    {
        if ($cinema->rooms()->count() > 0) {
            return redirect()->route('admin.cinemas.index')
                             ->with('error', 'Không thể xóa rạp này vì vẫn còn phòng chiếu liên kết!');
        }

        $cinema->delete();

        return redirect()->route('admin.cinemas.index')
                         ->with('success', 'Xóa rạp chiếu phim thành công!');
    }
}
