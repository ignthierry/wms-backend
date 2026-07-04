<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockOpnameItem;

class StockOpnameItemController extends Controller
{
    public function index()
    {
        return StockOpnameItem::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // Add validation rules here
        ]);
        // Bypass strict validation for quick scaffold
        $item = StockOpnameItem::create($request->all());
        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        $item = StockOpnameItem::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = StockOpnameItem::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = StockOpnameItem::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
