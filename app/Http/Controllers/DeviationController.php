<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deviation;

class DeviationController extends Controller
{
    public function index()
    {
        return Deviation::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'receiving_id' => 'required|exists:receivings,id',
            'item_code' => 'required|string|max:100',
            'qty_diff' => 'nullable|integer',
            'damage_condition' => 'nullable|string',
            'photo_url' => 'nullable|string|max:255',
        ]);

        // photo_url NOT NULL di DB — default string kosong jika tidak dikirim
        $data['photo_url'] = $data['photo_url'] ?? '';
        $item = Deviation::create($data);
        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        $item = Deviation::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = Deviation::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = Deviation::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
