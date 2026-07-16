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
            'asn_id' => 'required|exists:asns,id',
            'no_sppb' => 'required|string',
            'tgl_sppb' => 'required|date',
            'jenis_sppb' => 'required|string',
            'no_referensi' => 'required|string',
            'tgl_invoice' => 'required|date',
        ]);

        $asn = \App\Models\Asn::with('items')->findOrFail($data['asn_id']);
        
        $drNumber = 'DR-' . date('Ymd') . '-' . rand(1000, 9999);
        
        $dr = DeliveryRequest::create([
            'asn_id' => $asn->id,
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

        foreach($asn->items as $item) {
            \App\Models\DrItem::create([
                'dr_id' => $dr->id,
                'item_code' => $item->item_code,
                'qty_requested' => $item->qty_expected,
                'lot_number' => $item->pos_number ?? ''
            ]);
        }

        $invoiceNumber = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);

        $invoice = \App\Models\Invoice::create([
            'asn_id' => $asn->id,
            'invoice_number' => $invoiceNumber,
            'storage_fee' => 100000, 
            'handling_fee' => 50000, 
            'total_amount' => 150000, 
            'status' => 'UNPAID',
            'tgl_invoice' => $data['tgl_invoice']
        ]);

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
