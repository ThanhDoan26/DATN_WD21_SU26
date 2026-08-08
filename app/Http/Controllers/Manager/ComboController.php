<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use Illuminate\Http\Request;

class ComboController extends Controller
{
    /**
     * Display a listing of the combos (Read-only for Manager).
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $combos = Combo::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('manager.combos.index', compact('combos', 'search', 'status'));
    }

    /**
     * Display the specified combo (Read-only for Manager).
     */
    public function show(Combo $combo)
    {
        return view('manager.combos.show', compact('combo'));
    }
}
