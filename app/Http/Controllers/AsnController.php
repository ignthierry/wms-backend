<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asn;

class AsnController extends Controller
{
    public function index()
    {
        return Asn::with(['forwarding', 'warehouse', 'items.photos', 'invoice'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->except('items');
        $item = Asn::create($data);
        
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $asnItem) {
                $item->items()->create($asnItem);
            }
        }
        
        return response()->json($item->load(['forwarding', 'warehouse', 'items.photos']), 201);
    }

    public function show(string $id)
    {
        $item = Asn::with(['forwarding', 'warehouse', 'items.photos'])->findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = Asn::findOrFail($id);
        $data = $request->except('items');
        $item->update($data);

        if ($request->has('items') && is_array($request->items)) {
            // Simple sync: delete old and insert new. In a real app, you might want to update existing.
            $item->items()->delete();
            foreach ($request->items as $asnItem) {
                $item->items()->create($asnItem);
            }
        }

        return response()->json($item->load(['forwarding', 'warehouse', 'items.photos']));
    }

    public function destroy(string $id)
    {
        $item = Asn::findOrFail($id);
        $item->delete(); // Cascade on delete should handle items
        return response()->json(null, 204);
    }
}
