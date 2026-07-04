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
            // Add validation rules here
        ]);
        // Bypass strict validation for quick scaffold
        $item = Packing::create($request->all());
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
