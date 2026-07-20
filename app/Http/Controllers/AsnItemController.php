<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsnItem;

class AsnItemController extends Controller
{
    public function index()
    {
        return AsnItem::with(['asn', 'consignee', 'invoice', 'photos'])->get();
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
        $item = AsnItem::with(['asn.forwarding', 'asn.warehouse', 'consignee', 'photos'])->findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = AsnItem::findOrFail($id);
        $data = $request->except(['photo_proof_files', 'jenis_foto']);

        if ($request->hasFile('photo_proof_files')) {
            $files = $request->file('photo_proof_files');
            $jenis_foto = $request->input('jenis_foto', 'in');

            foreach ($files as $file) {
                $path = $file->store('photo_proofs', 'sftp');
                $item->photos()->create([
                    'photo_proof' => $path,
                    'jenis_foto' => $jenis_foto,
                ]);
            }
        }

        $item->update($data);
        return response()->json($item->load('photos'));
    }

    public function destroy(string $id)
    {
        $item = AsnItem::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }

    public function findByQr(string $qr_id)
    {
        $query = AsnItem::with(['asn', 'consignee', 'photos', 'invoice', 'deliveryRequest']);
        
        if (str_starts_with($qr_id, 'ITEM-')) {
            $id = str_replace('ITEM-', '', $qr_id);
            $query->where('id', $id)->orWhere('qr_id', $qr_id);
        } else {
            $query->where('qr_id', $qr_id);
        }

        $item = $query->firstOrFail();
        return response()->json($item);
    }

    public function histories(string $id)
    {
        $item = AsnItem::findOrFail($id);
        $histories = $item->histories()->get();
        return response()->json($histories);
    }
}
