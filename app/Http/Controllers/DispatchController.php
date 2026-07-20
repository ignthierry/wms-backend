<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dispatch;

class DispatchController extends Controller
{
    public function index()
    {
        return Dispatch::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // Add validation rules here
        ]);
        // Bypass strict validation for quick scaffold
        $item = Dispatch::create($request->all());
        return response()->json($item, 201);
    }

    public function show(string $id)
    {
        $item = Dispatch::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = Dispatch::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = Dispatch::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }

    public function outboundQcSubmit(Request $request, $asn_item_id)
    {
        $asnItem = \App\Models\AsnItem::with('deliveryRequest')->findOrFail($asn_item_id);

        if ($request->hasFile('photo_proof_files')) {
            $files = $request->file('photo_proof_files');
            foreach ($files as $file) {
                $path = $file->store('photo_proofs', 'sftp');
                $asnItem->photos()->create([
                    'photo_proof' => $path,
                    'jenis_foto' => 'out',
                ]);
            }
        }

        // Update ASN Item Status and details
        $asnItem->update([
            'status' => 'READY_TO_DISPATCH',
            'block_location' => $request->input('block_location', $asnItem->block_location),
            'item_condition' => $request->input('item_condition', $asnItem->item_condition),
        ]);

        return response()->json([
            'message' => 'QC Outbound saved successfully. Item is ready for dispatch.',
            'asn_item' => $asnItem->load('photos')
        ]);
    }

    public function readyToDispatch()
    {
        // Get all items that are READY_TO_DISPATCH and have a delivery request
        $items = \App\Models\AsnItem::with(['deliveryRequest', 'invoice', 'asn'])
            ->where('status', 'READY_TO_DISPATCH')
            ->get();
            
        return response()->json($items);
    }

    public function generateSuratJalan(Request $request)
    {
        $data = $request->validate([
            'asn_item_id' => 'required|exists:asn_items,id',
            'expedition_name' => 'required|string',
            'driver_name' => 'required|string',
            'driver_phone' => 'required|string',
        ]);

        $asnItem = \App\Models\AsnItem::with('deliveryRequest')->findOrFail($data['asn_item_id']);

        if (!$asnItem->deliveryRequest) {
            return response()->json(['message' => 'Delivery Request not found for this item.'], 400);
        }

        // Create Dispatch / Surat Jalan
        $dispatch = Dispatch::create([
            'dr_id' => $asnItem->deliveryRequest->id,
            'dispatcher_by' => $request->user() ? $request->user()->id : 1,
            'surat_jalan_number' => 'SJ-' . date('Ymd') . '-' . rand(1000, 9999),
            'manifest_number' => 'MNF-' . date('Ymd') . '-' . rand(1000, 9999), // default manifest number
            'expedition_name' => $data['expedition_name'],
            'driver_name' => $data['driver_name'],
            'driver_phone' => $data['driver_phone'],
            'status' => 'DISPATCHED'
        ]);

        // Update Item Status
        $asnItem->update(['status' => 'DISPATCHED']);

        return response()->json([
            'message' => 'Surat Jalan generated successfully.',
            'dispatch' => $dispatch
        ]);
    }
}
