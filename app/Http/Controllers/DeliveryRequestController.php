<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryRequest;

class DeliveryRequestController extends Controller
{
    public function index()
    {
        return DeliveryRequest::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asn_item_id' => 'required|exists:asn_items,id',
            'no_sppb' => 'required|string',
            'tgl_sppb' => 'required|date',
            'jenis_sppb' => 'required|string',
            'no_referensi' => 'required|string',
            'tgl_invoice' => 'required|date',
        ]);

        $asnItem = \App\Models\AsnItem::with('asn')->findOrFail($data['asn_item_id']);
        $asn = $asnItem->asn;
        
        $drNumber = 'DR-' . date('Ymd') . '-' . rand(1000, 9999);
        
        $dr = DeliveryRequest::create([
            'asn_id' => $asn->id,
            'asn_item_id' => $asnItem->id,
            'warehouse_id' => $asn->warehouse_id,
            'forwarding_id' => $asn->forwarding_id,
            'dr_number' => $drNumber,
            'request_date' => now(),
            'recipient_name' => $request->input('recipient_name', $asn->consignee ? $asn->consignee->name : 'DEFAULT RECIPIENT'),
            'delivery_address' => $request->input('delivery_address', 'WAREHOUSE ADDRESS'),
            'status' => 'PENDING',
            'no_sppb' => $data['no_sppb'],
            'tgl_sppb' => $data['tgl_sppb'],
            'jenis_sppb' => $data['jenis_sppb'],
            'no_referensi' => $data['no_referensi'],
        ]);

        \App\Models\DrItem::create([
            'dr_id' => $dr->id,
            'item_code' => $asnItem->item_code,
            'qty_requested' => $asnItem->qty_expected,
            'lot_number' => $asnItem->pos_number ?? ''
        ]);

        $invoiceController = app(\App\Http\Controllers\InvoiceController::class);
        $invoiceResponse = $invoiceController->store($request, $asnItem->id);
        $invoice = $invoiceResponse->getData(true);

        // Optional: Update ASN status
        // $asn->update(['status' => 'DELIVERY_REQUESTED']);

        return response()->json([
            'message' => 'Delivery Request and Invoice created successfully',
            'delivery_request' => $dr,
            'invoice' => $invoice
        ], 201);
    }

    public function show(string $id)
    {
        $item = DeliveryRequest::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, string $id)
    {
        $item = DeliveryRequest::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    public function destroy(string $id)
    {
        $item = DeliveryRequest::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
