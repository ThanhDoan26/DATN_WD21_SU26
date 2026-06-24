<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComboController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $combos = Combo::orderBy('id', 'desc')->paginate(10);
        return view('admin.combos.index', compact('combos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.combos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('combos', 'public');
            $data['image'] = $path;
        }

        Combo::create($data);

        return redirect()->route('admin.combos.index')->with('success', 'Thêm mới combo bắp nước thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Combo $combo)
    {
        return view('admin.combos.show', compact('combo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Combo $combo)
    {
        return view('admin.combos.edit', compact('combo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Combo $combo)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            // Xoá ảnh cũ nếu có
            if ($combo->image && Storage::disk('public')->exists($combo->image)) {
                Storage::disk('public')->delete($combo->image);
            }
            $path = $request->file('image')->store('combos', 'public');
            $data['image'] = $path;
        }

        $combo->update($data);

        return redirect()->route('admin.combos.index')->with('success', 'Cập nhật combo bắp nước thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Combo $combo)
    {
        if ($combo->image && Storage::disk('public')->exists($combo->image)) {
            Storage::disk('public')->delete($combo->image);
        }
        
        $combo->delete();

        return redirect()->route('admin.combos.index')->with('success', 'Xoá combo bắp nước thành công!');
    }
}
