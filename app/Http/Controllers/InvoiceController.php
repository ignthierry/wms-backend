<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function calculate($asn_id)
    {
        $asn = \App\Models\Asn::with('items')->findOrFail($asn_id);
        
        $total_capacity = 0;
        foreach ($asn->items as $item) {
            $volume = (float) $item->actual_volume;
            $weight_ton = (float) $item->actual_weight / 1000;
            $total_capacity += max($volume, $weight_ton);
        }

        $stripping_date = $asn->tanggal_stripping ? \Carbon\Carbon::parse($asn->tanggal_stripping) : \Carbon\Carbon::now();
        $current_date = \Carbon\Carbon::now();
        $days = $stripping_date->diffInDays($current_date) + 1; // +1 to include the day of stripping

        // Basic default tariffs (these could be configurable)
        $base_tariff = 50000; // e.g. Rp 50,000 per CBM/Ton per day
        $handling_fee = 100000; // e.g. Rp 100,000 fixed fee
        
        $storage_fee = $total_capacity * $days * $base_tariff;
        $total_amount = $storage_fee + $handling_fee;

        return response()->json([
            'asn_id' => $asn->id,
            'asn_number' => $asn->asn_number,
            'total_capacity' => $total_capacity,
            'days' => $days,
            'base_tariff' => $base_tariff,
            'handling_fee' => $handling_fee,
            'storage_fee' => $storage_fee,
            'total_amount' => $total_amount,
        ]);
    }

    public function store(Request $request, $asn_id)
    {
        // Re-calculate to ensure accuracy
        $calcResponse = $this->calculate($asn_id);
        $data = $calcResponse->getData(true);

        $invoice = \App\Models\Invoice::updateOrCreate(
            ['asn_id' => $asn_id],
            [
                'invoice_number' => 'INV-' . strtoupper(uniqid()),
                'storage_fee' => $data['storage_fee'],
                'handling_fee' => $data['handling_fee'],
                'total_amount' => $data['total_amount'],
                'status' => 'UNPAID'
            ]
        );

        return response()->json($invoice, 201);
    }
}
