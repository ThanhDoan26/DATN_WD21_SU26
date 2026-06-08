<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cinema;
use App\Http\Requests\Admin\StoreCinemaRequest;
use App\Http\Requests\Admin\UpdateCinemaRequest;

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
     * Store a newly created cinema in storage
     */
    public function store(StoreCinemaRequest $request)
    {
        Cinema::create($request->validated());

        return redirect()->route('admin.cinemas.index')
            ->with('success', 'Rạp chiếu phim đã được tạo thành công.');
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
    public function update(\App\Http\Requests\Admin\UpdateCinemaRequest $request, Cinema $cinema)
    {
        $cinema->update($request->validated());

        return redirect()->route('admin.cinemas.index')
            ->with('success', 'Rạp chiếu phim đã được cập nhật thành công.');
    }

    /**
     * Remove the specified cinema from storage
     */
    public function destroy(Cinema $cinema)
    {
        try {
            // Check if cinema has rooms
            if ($cinema->rooms()->exists()) {
                return redirect()->route('admin.cinemas.index')
                    ->with('error', 'Không thể xóa rạp này vì đang có phòng chiếu phụ thuộc.');
            }
            
            $cinema->delete();
            return redirect()->route('admin.cinemas.index')
                ->with('success', 'Rạp chiếu phim đã được xóa thành công.');
        } catch (\Exception $e) {
            return redirect()->route('admin.cinemas.index')
                ->with('error', 'Có lỗi xảy ra khi xóa rạp chiếu phim.');
        }
    }
}
