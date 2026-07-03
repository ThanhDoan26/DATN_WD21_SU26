<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cinema;
use App\Http\Requests\Admin\StoreCinemaRequest;
use App\Http\Requests\Admin\UpdateCinemaRequest;
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
    public function index(Request $request)
    {
        $query = Cinema::query();

        // Lọc theo tìm kiếm (Tên, Địa chỉ)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái bị xóa mềm (trashed) hoặc status
        if ($request->has('trashed') && $request->trashed == 'true') {
            $query->onlyTrashed();
        } else {
            if ($request->has('status') && in_array($request->status, ['ACTIVE', 'INACTIVE'])) {
                $query->where('status', $request->status);
            }
        }

        $cinemas = $query->orderBy('created_at', 'desc')->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($cinemas);
        }

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
    public function update(UpdateCinemaRequest $request, Cinema $cinema)
    {
        $cinema->update($request->validated());

        return redirect()->route('admin.cinemas.index')
            ->with('success', 'Rạp chiếu phim đã được cập nhật thành công.');
    }

    /**
     * Remove the specified cinema from storage
     */
    public function destroy(Cinema $cinema, Request $request)
    {
        try {
            // Không chặn xóa khi có phòng chiếu nữa, vì đây là Soft Delete.
            // Nếu muốn chặt chẽ, ta có thể giữ lại check này, nhưng soft delete thì dữ liệu ko mất hẳn.
            
            $cinema->delete();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Rạp chiếu phim đã được chuyển vào thùng rác.']);
            }

            return redirect()->route('admin.cinemas.index')
                ->with('success', 'Rạp chiếu phim đã được chuyển vào thùng rác.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa rạp chiếu phim.'], 500);
            }
            return redirect()->route('admin.cinemas.index')
                ->with('error', 'Có lỗi xảy ra khi xóa rạp chiếu phim.');
        }
    }

    /**
     * Khôi phục cụm rạp đã xóa mềm
     */
    public function restore($id, Request $request)
    {
        try {
            $cinema = Cinema::withTrashed()->findOrFail($id);
            $cinema->restore();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Đã khôi phục rạp chiếu phim thành công.']);
            }

            return redirect()->route('admin.cinemas.index')->with('success', 'Đã khôi phục rạp chiếu phim thành công.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi khôi phục.'], 500);
            }
            return redirect()->route('admin.cinemas.index')->with('error', 'Có lỗi xảy ra khi khôi phục.');
        }
    }
}