<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Packing;

class PackingController extends Controller
{
    public function index()
    {
        return Packing::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'dr_id' => 'required|exists:delivery_requests,id',
            'packed_by' => 'required|exists:users,id',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'barcode_scanned_count' => 'nullable|integer|min:0',
            'packing_photo' => 'nullable|string|max:255',
        ]);

        // packing_photo NOT NULL di DB — default string kosong jika tidak dikirim
        $data['packing_photo'] = $data['packing_photo'] ?? '';
        $item = Packing::create($data);
        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        $item = Packing::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = Packing::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = Packing::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
