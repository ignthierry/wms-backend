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
        $data = $request->all();

        if ($request->hasFile('photo_proof_file')) {
            $path = $request->file('photo_proof_file')->store('public/photo_proofs');
            $data['photo_proof'] = str_replace('public/', 'storage/', $path);
        }

        $item->update($data);
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = AsnItem::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }

    public function findByQr(string $qr_id)
    {
        $item = AsnItem::where('qr_id', $qr_id)->with(['asn', 'consignee'])->firstOrFail();
        return response()->json($item);
    }
}
