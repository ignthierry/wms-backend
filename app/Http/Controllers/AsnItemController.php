<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsnItem;

class AsnItemController extends Controller
{
    public function index()
    {
        return AsnItem::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // Add validation rules here
        ]);
        // Bypass strict validation for quick scaffold
        $item = AsnItem::create($request->all());
        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        $item = AsnItem::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = AsnItem::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = AsnItem::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
