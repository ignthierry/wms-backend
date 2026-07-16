<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function calculate(Request $request, $asn_item_id)
    {
        $asnItem = \App\Models\AsnItem::with('asn')->findOrFail($asn_item_id);
        $asn = $asnItem->asn;
        $invoice = \App\Models\Invoice::where('asn_item_id', $asn_item_id)->first();
        $tarif = \App\Models\Tarif::first();
        
        $volume = (float) $asnItem->actual_volume;
        $weight_ton = (float) $asnItem->actual_weight / 1000;
        $total_capacity = max($volume, $weight_ton);

        $stripping_date = $asn->tanggal_stripping ? \Carbon\Carbon::parse($asn->tanggal_stripping) : \Carbon\Carbon::now();
        
        if ($request->has('tgl_invoice') && !empty($request->tgl_invoice)) {
            $invoice_date = \Carbon\Carbon::parse($request->tgl_invoice);
        } else {
            $invoice_date = ($invoice && $invoice->tgl_invoice) ? \Carbon\Carbon::parse($invoice->tgl_invoice) : \Carbon\Carbon::now();
        }
        
        $days = $stripping_date->diffInDays($invoice_date);
        $days = $days < 0 ? 0 : $days + 1; // +1 to include the day of stripping

        // Default to hardcoded if no Tarif is found in DB
        $masa_1 = $tarif ? ($tarif->storage_masa_1 ?? 50000) : 50000;
        $masa_2 = $tarif ? ($tarif->storage_masa_2 ?? 100000) : 100000;
        $masa_3 = $tarif ? ($tarif->storage_masa_3 ?? 150000) : 150000;
        $masa_4 = $tarif ? ($tarif->storage_masa_4 ?? 200000) : 200000;
        
        $tarif_mekanis = $tarif ? ($tarif->mekanis ?? 50000) : 50000;
        $tarif_administrasi = $tarif ? ($tarif->administrasi ?? 100000) : 100000;
        $tarif_service = $tarif ? ($tarif->service ?? 50000) : 50000;
        $tarif_surveyor = $tarif ? ($tarif->surveyor_fee ?? 50000) : 50000;
        
        $storage_fee = 0;
        $details = [];

        // Progressive calculation for Storage
        for ($i = 1; $i <= $days; $i++) {
            $rate = 0;
            $masa_label = '';
            if ($i <= 5) {
                $rate = $masa_1;
                $masa_label = 'Masa 1 (Hari 1-5)';
            } elseif ($i <= 10) {
                $rate = $masa_2;
                $masa_label = 'Masa 2 (Hari 6-10)';
            } elseif ($i <= 15) {
                $rate = $masa_3;
                $masa_label = 'Masa 3 (Hari 11-15)';
            } else {
                $rate = $masa_4;
                $masa_label = 'Masa 4 (Hari 16+)';
            }
            
            $day_fee = $total_capacity * $rate;
            $storage_fee += $day_fee;
            
            $details[] = [
                'day' => $i,
                'date' => $stripping_date->copy()->addDays($i - 1)->format('Y-m-d'),
                'masa' => $masa_label,
                'rate' => $rate,
                'fee' => $day_fee
            ];
        }

        $mekanis_fee = $total_capacity * $tarif_mekanis;
        $document_fee = $tarif_administrasi + $tarif_service + $tarif_surveyor;
        
        $total_amount = $storage_fee + $mekanis_fee + $document_fee;

        return response()->json([
            'asn_id' => $asn->id,
            'asn_item_id' => $asnItem->id,
            'asn_number' => $asn->asn_number . ' - ' . $asnItem->pos_number,
            'tanggal_stripping' => $stripping_date->format('Y-m-d'),
            'tanggal_invoice' => $invoice_date->format('Y-m-d'),
            'total_capacity' => $total_capacity,
            'days' => $days,
            'storage_fee' => $storage_fee,
            'mekanis_fee' => $mekanis_fee,
            'tarif_administrasi' => $tarif_administrasi,
            'tarif_service' => $tarif_service,
            'tarif_surveyor' => $tarif_surveyor,
            'document_fee' => $document_fee,
            'total_amount' => $total_amount,
            'details' => $details
        ]);
    }

    public function store(Request $request, $asn_item_id)
    {
        // Re-calculate to ensure accuracy
        $calcResponse = $this->calculate($request, $asn_item_id);
        $data = $calcResponse->getData(true);

        $invoice = \App\Models\Invoice::updateOrCreate(
            ['asn_item_id' => $asn_item_id],
            [
                'asn_id' => $data['asn_id'],
                'invoice_number' => 'INV-' . strtoupper(uniqid()),
                'storage_fee' => $data['storage_fee'],
                'handling_fee' => $data['mekanis_fee'] + $data['document_fee'],
                'total_amount' => $data['total_amount'],
                'status' => 'UNPAID',
                'tgl_invoice' => $data['tanggal_invoice']
            ]
        );

        return response()->json($invoice, 201);
    }
}
