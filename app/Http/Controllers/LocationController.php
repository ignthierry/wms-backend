<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    public function index()
    {
        return Location::with('warehouse')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'zone' => 'required|string|max:50',
            'aisle' => 'required|string|max:20',
            'rack_row' => 'required|string|max:20',
            'tier' => 'required|string|max:20',
            'capacity' => 'nullable|numeric|min:0',
            'is_empty' => 'nullable|boolean',
        ]);

        // Generate barcode_loc otomatis jika tidak dikirim: WAREHOUSE-ZONE-AISLE-ROW-TIER
        if (empty($request->input('barcode_loc'))) {
            $wh = \App\Models\Warehouse::find($data['warehouse_id']);
            $whCode = $wh ? $wh->code : 'WH';
            $data['barcode_loc'] = strtoupper(sprintf('%s-%s-%s-%s-%s',
                $whCode, $data['zone'], $data['aisle'], $data['rack_row'], $data['tier']
            ));
        } else {
            $data['barcode_loc'] = $request->input('barcode_loc');
        }
        $data['is_empty'] = $request->input('is_empty', true);
        $data['capacity'] = $request->input('capacity', 0);

        $item = Location::create($data);
        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        $item = Location::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = Location::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = Location::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
