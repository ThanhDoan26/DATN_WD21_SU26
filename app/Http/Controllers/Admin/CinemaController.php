<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cinema;
use Illuminate\Http\Request;

/**
 * CinemaController
 * ========================================
 * Controller quản lý cinemas
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
     * Store a newly created cinema
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cinemas',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ], [
            'name.required' => 'Tên rạp là bắt buộc',
            'name.unique' => 'Tên rạp đã tồn tại',
            'address.required' => 'Địa chỉ là bắt buộc',
            'city.required' => 'Thành phố là bắt buộc',
            'status.required' => 'Trạng thái là bắt buộc',
        ]);

        Cinema::create($validated);
        return redirect()->route('admin.cinemas.index')->with('success', 'Thêm rạp thành công!');
    }

    /**
     * Display the specified cinema with its movies
     */
    public function show(Cinema $cinema)
    {
        $cinema->load(['rooms', 'users']);
        return view('admin.cinemas.show', ['cinema' => $cinema]);
    }

    /**
     * Show the form for editing the specified cinema
     */
    public function edit(Cinema $cinema)
    {
        return view('admin.cinemas.edit', ['cinema' => $cinema]);
    }

    /**
     * Update the specified cinema
     */
    public function update(Request $request, Cinema $cinema)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cinemas,name,' . $cinema->id,
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ], [
            'name.required' => 'Tên rạp là bắt buộc',
            'name.unique' => 'Tên rạp đã tồn tại',
            'address.required' => 'Địa chỉ là bắt buộc',
            'city.required' => 'Thành phố là bắt buộc',
            'status.required' => 'Trạng thái là bắt buộc',
        ]);

        $cinema->update($validated);
        return redirect()->route('admin.cinemas.show', $cinema->id)->with('success', 'Cập nhật rạp thành công!');
    }

    /**
     * Delete the specified cinema
     */
    public function destroy(Cinema $cinema)
    {
        $cinema->delete();
        return redirect()->route('admin.cinemas.index')->with('success', 'Xóa rạp thành công!');
    }
}
