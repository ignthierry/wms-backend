<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TruckingInvoice;
use App\Models\TruckingCompany;
use App\Models\Asn;
use App\Models\Invoice;

class TruckingInvoiceController extends Controller
{
    public function index()
    {
        $invoices = TruckingInvoice::with(['company', 'asn'])->orderBy('id', 'desc')->get();
        return response()->json($invoices);
    }

    /**
     * Hitung invoice trucking untuk satu ASN.
     * type=trucking → hanya jasa trucking; type=combined → gudang + trucking.
     */
    public function calculate(Request $request, $asn_id)
    {
        $asn = Asn::with('truckingCompany', 'items')->findOrFail($asn_id);
        $company = $asn->truckingCompany;
        if (!$company) {
            return response()->json(['message' => 'ASN ini tidak memakai trucking milik kita (is_ours).'], 422);
        }

        $type = $request->input('type', 'trucking'); // trucking | combined

        // Ambil tarif pertama yang aktif dari trucking company tsb
        $tarif = $company->tarifs()->where('is_active', true)->first();

        // Estimasi fee trucking: rate * jumlah container (atau per trip)
        $containerCount = max(1, $asn->no_container ? substr_count($asn->no_container, ',') + 1 : 1);
        $rate = $tarif ? (float) $tarif->rate : 0;
        $minimumCharge = $tarif ? (float) $tarif->minimum_charge : 0;

        $truckingFee = $rate * $containerCount;
        if ($truckingFee < $minimumCharge) {
            $truckingFee = $minimumCharge;
        }

        $warehouseFee = 0;
        // Rekap fee gudang dari invoice existing (storage + handling) untuk ASN ini
        $warehouseInvoices = Invoice::where('asn_id', $asn_id)->get();
        foreach ($warehouseInvoices as $inv) {
            $warehouseFee += (float) $inv->storage_fee + (float) $inv->handling_fee;
        }

        $total = $type === 'combined' ? ($truckingFee + $warehouseFee) : $truckingFee;
        $ppn = $total * 0.11;
        $grandTotal = $total + $ppn;

        return response()->json([
            'asn_id' => $asn->id,
            'asn_number' => $asn->asn_number,
            'trucking_company_id' => $company->id,
            'trucking_company_name' => $company->name,
            'no_container' => $asn->no_container,
            'container_count' => $containerCount,
            'tarif' => $tarif,
            'rate' => $rate,
            'minimum_charge' => $minimumCharge,
            'trucking_fee' => $truckingFee,
            'warehouse_fee' => $warehouseFee,
            'type' => $type,
            'subtotal' => $total,
            'ppn' => $ppn,
            'total_amount' => $grandTotal,
        ]);
    }

    /**
     * Generate (simpan) invoice trucking untuk satu ASN.
     * Bisa trucking-only atau combined (gudang + trucking).
     */
    public function store(Request $request, $asn_id)
    {
        $calc = $this->calculate($request, $asn_id)->getData(true);
        if (isset($calc['message'])) {
            return response()->json($calc, 422);
        }

        $invoice = TruckingInvoice::updateOrCreate(
            ['asn_id' => $asn_id],
            [
                'trucking_company_id' => $calc['trucking_company_id'],
                'invoice_number' => $request->input('invoice_number', 'TINV-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6))),
                'invoice_type' => $calc['type'],
                'trucking_fee' => $calc['trucking_fee'],
                'warehouse_fee' => $calc['warehouse_fee'],
                'total_amount' => $calc['total_amount'],
                'status' => $request->input('status', 'UNPAID'),
                'tgl_invoice' => $request->input('tgl_invoice', now()->toDateString()),
                'details' => $calc,
            ]
        );

        return response()->json($invoice->load('company', 'asn'), 201);
    }

    public function show($id)
    {
        return response()->json(TruckingInvoice::with('company', 'asn')->findOrFail($id));
    }

    /**
     * Generate invoice trucking TANPA ASN (jasa trucking saja, terpisah).
     */
    public function storeStandalone(Request $request)
    {
        $data = $request->validate([
            'trucking_company_id' => 'required|exists:trucking_companies,id',
            'trucking_fee' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:20',
            'tgl_invoice' => 'nullable|date',
        ]);

        $truckingFee = (float) $data['trucking_fee'];
        $subtotal = $truckingFee;
        $ppn = $subtotal * 0.11;
        $total = $subtotal + $ppn;

        $invoice = TruckingInvoice::create([
            'trucking_company_id' => $data['trucking_company_id'],
            'asn_id' => null,
            'invoice_number' => 'TINV-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'invoice_type' => 'trucking',
            'trucking_fee' => $truckingFee,
            'warehouse_fee' => 0,
            'total_amount' => $total,
            'status' => $data['status'] ?? 'UNPAID',
            'tgl_invoice' => $data['tgl_invoice'] ?? now()->toDateString(),
            'details' => [
                'description' => $data['description'] ?? null,
                'subtotal' => $subtotal,
                'ppn' => $ppn,
            ],
        ]);

        return response()->json($invoice->load('company', 'asn'), 201);
    }

    public function update(Request $request, $id)
    {
        $invoice = TruckingInvoice::findOrFail($id);
        $invoice->update($request->only(['status', 'tgl_invoice']));

        return response()->json($invoice->load('company', 'asn'));
    }

    public function destroy($id)
    {
        TruckingInvoice::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}