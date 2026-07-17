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
        
        $tarif_tps_asal = $tarif ? ($tarif->tps_asal ?? 100000) : 100000;
        $tarif_mekanis = $tarif ? ($tarif->mekanis ?? 50000) : 50000;
        $tarif_receiving = $tarif ? ($tarif->receiving ?? 65000) : 65000;
        $tarif_delivery = $tarif ? ($tarif->delivery ?? 65000) : 65000;
        $tarif_pemeriksaan = $tarif ? ($tarif->pemeriksaan ?? 200000) : 200000;
        $tarif_service = $tarif ? ($tarif->service ?? 100000) : 100000;
        $tarif_gerakan = $tarif ? ($tarif->gerakan ?? 45000) : 45000;
        $tarif_keamanan = $tarif ? ($tarif->keamanan ?? 100000) : 100000;
        $tarif_administrasi = $tarif ? ($tarif->administrasi ?? 100000) : 100000;
        $tarif_fuel = $tarif ? ($tarif->fuel ?? 50000) : 50000;
        
        $storage_fee = 0;
        $details = [];

        // Progressive calculation for Storage
        $masa_summary = [];
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
            
            if (!isset($masa_summary[$masa_label])) {
                $masa_summary[$masa_label] = [
                    'keterangan' => 'PENUMPUKAN (STORAGE) - ' . $masa_label,
                    'qty' => $total_capacity,
                    'unit' => 'M3/Ton',
                    'tarif' => $rate,
                    'hari' => 0,
                    'total' => 0
                ];
            }
            $masa_summary[$masa_label]['hari']++;
            $masa_summary[$masa_label]['total'] += $day_fee;
        }
        $storage_details = array_values($masa_summary);

        $other_fees = [
            [
                'keterangan' => 'BIAYA TPS ASAL (UTPK)',
                'qty' => 1,
                'unit' => 'Ls',
                'tarif' => $tarif_tps_asal,
                'hari' => 1,
                'total' => 1 * $tarif_tps_asal
            ],
            [
                'keterangan' => 'MEKANIS',
                'qty' => $total_capacity,
                'unit' => 'M3/Ton',
                'tarif' => $tarif_mekanis,
                'hari' => 1,
                'total' => $total_capacity * $tarif_mekanis
            ],
            [
                'keterangan' => 'RECEIVING',
                'qty' => $total_capacity,
                'unit' => 'M3/Ton',
                'tarif' => $tarif_receiving,
                'hari' => 1,
                'total' => $total_capacity * $tarif_receiving
            ],
            [
                'keterangan' => 'DELIVERY',
                'qty' => $total_capacity,
                'unit' => 'M3/Ton',
                'tarif' => $tarif_delivery,
                'hari' => 1,
                'total' => $total_capacity * $tarif_delivery
            ],
            [
                'keterangan' => 'BIAYA PEMERIKSAAN',
                'qty' => 1,
                'unit' => 'Ls',
                'tarif' => $tarif_pemeriksaan,
                'hari' => 1,
                'total' => 1 * $tarif_pemeriksaan
            ],
            [
                'keterangan' => 'SERVICE CHARGE',
                'qty' => 1,
                'unit' => 'Ls',
                'tarif' => $tarif_service,
                'hari' => 1,
                'total' => 1 * $tarif_service
            ],
            [
                'keterangan' => 'GERAKAN DAN PENGATURAN',
                'qty' => $total_capacity,
                'unit' => 'M3/Ton',
                'tarif' => $tarif_gerakan,
                'hari' => 1,
                'total' => $total_capacity * $tarif_gerakan
            ],
            [
                'keterangan' => 'KEAMANAN DAN KEBERSIHAN',
                'qty' => 1,
                'unit' => 'Ls',
                'tarif' => $tarif_keamanan,
                'hari' => 1,
                'total' => 1 * $tarif_keamanan
            ],
            [
                'keterangan' => 'ADMINISTRASI',
                'qty' => 1,
                'unit' => 'Ls',
                'tarif' => $tarif_administrasi,
                'hari' => 1,
                'total' => 1 * $tarif_administrasi
            ],
            [
                'keterangan' => 'FUEL SURCHARGE',
                'qty' => 1,
                'unit' => 'Cont',
                'tarif' => $tarif_fuel,
                'hari' => 1,
                'total' => 1 * $tarif_fuel
            ]
        ];

        $subtotal = $storage_fee;
        foreach ($other_fees as $fee) {
            $subtotal += $fee['total'];
        }

        $ppn = $subtotal * 0.11; // 11% PPN

        $moving_fee = 422500; // Dummy moving fee

        $total_amount = $subtotal + $ppn + $moving_fee;

        return response()->json([
            'asn_id' => $asn->id,
            'asn_item_id' => $asnItem->id,
            'asn_number' => $asn->asn_number . ' - ' . $asnItem->pos_number,
            'tanggal_stripping' => $stripping_date->format('Y-m-d'),
            'tanggal_invoice' => $invoice_date->format('Y-m-d'),
            'tanggal_masuk' => $asnItem->created_at ? $asnItem->created_at->format('Y-m-d H:i') : $stripping_date->format('Y-m-d H:i'),
            'item_description' => $asnItem->description ?? 'GENERAL CARGO',
            'qty' => $asnItem->qty ?? 10,
            'weight' => $asnItem->actual_weight ?? 2000,
            'volume' => $asnItem->actual_volume ?? 2.0,
            'total_capacity' => $total_capacity,
            'days' => $days,
            'storage_details' => $storage_details,
            'other_fees' => $other_fees,
            'subtotal' => $subtotal,
            'ppn' => $ppn,
            'moving_fee' => $moving_fee,
            'total_amount' => $total_amount
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
