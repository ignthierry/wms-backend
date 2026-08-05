<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Asn;
use App\Models\AsnItem;
use App\Models\DeliveryRequest;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\Forwarding;
use App\Models\Consignee;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Dashboard Laporan — ringkasan grafik + KPI untuk semua laporan.
     * GET /api/reports/dashboard
     */
    public function dashboard(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $warehouseId = $request->query('warehouse_id');

        $asnQ = Asn::query();
        $drQ = DeliveryRequest::query();
        $invQ = Invoice::query();

        if ($warehouseId) {
            $asnQ->where('warehouse_id', $warehouseId);
            $drQ->where('warehouse_id', $warehouseId);
        }

        if ($from && $to) {
            $asnQ->whereBetween('created_at', [$from, $to]);
            $drQ->whereBetween('created_at', [$from, $to]);
            $invQ->whereBetween('tgl_invoice', [$from, $to]);
        }

        // Inbound vs Outbound per bulan (6 bln terakhir)
        $months = collect(range(5, 0))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        });

        $asnByMonth = Asn::query()
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->get()
            ->groupBy(fn($a) => Carbon::parse($a->created_at)->format('Y-m'));

        $drByMonth = DeliveryRequest::query()
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->get()
            ->groupBy(fn($d) => Carbon::parse($d->created_at)->format('Y-m'));

        $inboundData = $months->map(fn($m) => $asnByMonth->get($m)?->count() ?? 0);
        $outboundData = $months->map(fn($m) => $drByMonth->get($m)?->count() ?? 0);

        // Revenue per bulan (dari invoice PAID)
        $revenueByMonth = Invoice::query()
            ->where('status', 'PAID')
            ->when($from && $to, fn($q) => $q->whereBetween('tgl_invoice', [$from, $to]))
            ->get()
            ->groupBy(fn($i) => Carbon::parse($i->tgl_invoice)->format('Y-m'));

        $revenueData = $months->map(fn($m) => $revenueByMonth->get($m)?->sum('total_amount') ?? 0);

        // Status barang inbound (asn_items)
        $statusItems = AsnItem::query()
            ->when($warehouseId, function ($q) use ($warehouseId) {
                $q->whereHas('asn', fn($a) => $a->where('warehouse_id', $warehouseId));
            })
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        // Status outbound
        $statusDr = DeliveryRequest::query()
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        // Status invoice
        $statusInv = Invoice::query()
            ->selectRaw('status, COUNT(*) as c, SUM(total_amount) as total')
            ->groupBy('status')
            ->get();

        return response()->json([
            'kpi' => [
                'total_inbound' => $asnQ->count(),
                'total_outbound' => $drQ->count(),
                'total_revenue' => round($invQ->where('status', 'PAID')->sum('total_amount'), 2),
                'outstanding' => round(Invoice::query()
                    ->when($from && $to, fn($q) => $q->whereBetween('tgl_invoice', [$from, $to]))
                    ->where('status', 'UNPAID')->sum('total_amount'), 2),
                'total_stock_qty' => (int) Stock::sum('qty'),
                'total_warehouse' => Warehouse::count(),
            ],
            'chart_inbound_outbound' => [
                'categories' => $months->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->format('M')),
                'inbound' => $inboundData,
                'outbound' => $outboundData,
            ],
            'chart_revenue' => [
                'categories' => $months->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->format('M')),
                'revenue' => $revenueData,
            ],
            'status_inbound' => $statusItems,
            'status_outbound' => $statusDr,
            'status_invoice' => $statusInv,
            'warehouses' => Warehouse::select('id', 'warehouse_name', 'code')->get(),
        ]);
    }

    /**
     * Laporan Invoice — detail + rekap per consignee.
     * GET /api/reports/invoices?status=PAID|UNPAID
     */
    public function invoices(Request $request)
    {
        $status = $request->query('status');
        $q = Invoice::with(['asn', 'asnItem' => fn($x) => $x->with('consignee')]);

        if ($status && in_array($status, ['PAID', 'UNPAID'])) {
            $q->where('status', $status);
        }

        $invoices = $q->get()->map(function ($inv) {
            $consignee = $inv->asnItem?->consignee?->name
                        ?? $inv->asnItem?->consignee?->consignee_name
                        ?? '-';
            return [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'tgl_invoice' => $inv->tgl_invoice,
                'asn_number' => $inv->asn?->asn_number,
                'no_container' => $inv->asn?->no_container,
                'item_code' => $inv->asnItem?->item_code,
                'item_name' => $inv->asnItem?->item_name,
                'consignee' => $consignee,
                'storage_fee' => (float) $inv->storage_fee,
                'handling_fee' => (float) $inv->handling_fee,
                'total_amount' => (float) $inv->total_amount,
                'status' => $inv->status,
            ];
        });

        $totalRevenue = $invoices->where('status', 'PAID')->sum('total_amount');
        $totalOutstanding = $invoices->where('status', 'UNPAID')->sum('total_amount');
        $totalAll = $invoices->sum('total_amount');

        return response()->json([
            'data' => $invoices,
            'summary' => [
                'total_invoice' => $invoices->count(),
                'paid' => $invoices->where('status', 'PAID')->count(),
                'unpaid' => $invoices->where('status', 'UNPAID')->count(),
                'total_revenue' => round($totalRevenue, 2),
                'total_outstanding' => round($totalOutstanding, 2),
                'total_all' => round($totalAll, 2),
            ],
        ]);
    }

    /**
     * Rekap Pendapatan (per consignee / per warehouse / per bulan).
     * GET /api/reports/revenue
     */
    public function revenue(Request $request)
    {
        $group = $request->query('group', 'consignee'); // consignee | warehouse | month
        $status = $request->query('status', 'PAID');

        $q = Invoice::with(['asn', 'asnItem' => fn($x) => $x->with('consignee')]);
        if ($status && in_array($status, ['PAID', 'UNPAID', 'ALL'])) {
            if ($status !== 'ALL') $q->where('status', $status);
        }

        $invoices = $q->get();
        $rows = [];

        if ($group === 'consignee') {
            foreach ($invoices as $inv) {
                $consignee = $inv->asnItem?->consignee?->name
                            ?? $inv->asnItem?->consignee?->consignee_name
                            ?? 'Lainnya';
                $rows[$consignee] = ($rows[$consignee] ?? 0) + (float) $inv->total_amount;
            }
        } elseif ($group === 'warehouse') {
            foreach ($invoices as $inv) {
                $wh = $inv->asn?->warehouse?->warehouse_name ?? 'Unknown';
                $rows[$wh] = ($rows[$wh] ?? 0) + (float) $inv->total_amount;
            }
        } else { // month
            foreach ($invoices as $inv) {
                $m = $inv->tgl_invoice ? Carbon::parse($inv->tgl_invoice)->format('Y-m') : 'Unknown';
                $rows[$m] = ($rows[$m] ?? 0) + (float) $inv->total_amount;
            }
        }

        arsort($rows);

        return response()->json([
            'group' => $group,
            'status' => $status,
            'data' => collect($rows)->map(fn($total, $label) => ['label' => $label, 'total' => round($total, 2)]),
            'grand_total' => round(array_sum($rows), 2),
        ]);
    }

    /**
     * Laporan Operasional — inbound & outbound per periode + status barang.
     * GET /api/reports/operational
     */
    public function operational(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $warehouseId = $request->query('warehouse_id');
        $type = $request->query('type', 'all'); // all | inbound | outbound

        // INBOUND: ASN + items
        $asns = Asn::with(['warehouse', 'forwarding'])
            ->withCount('items')
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->orderByDesc('created_at')
            ->get();

        // OUTBOUND: Delivery Requests + dispatch
        $drs = DeliveryRequest::with(['warehouse', 'forwarding', 'asn'])
            ->when($from && $to, fn($q) => $q->whereBetween('request_date', [$from, $to]))
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->orderByDesc('request_date')
            ->get();

        // Status barang (inbound items)
        $statusItems = AsnItem::query()
            ->selectRaw('status, COUNT(*) as c')
            ->when($warehouseId, fn($q) => $q->whereHas('asn', fn($a) => $a->where('warehouse_id', $warehouseId)))
            ->groupBy('status')
            ->get();

        $inboundList = $asns->map(fn($a) => [
            'asn_number' => $a->asn_number,
            'no_container' => $a->no_container,
            'no_master_bl' => $a->no_master_bl,
            'voyage' => $a->voyage,
            'warehouse' => $a->warehouse?->warehouse_name,
            'forwarding' => $a->forwarding?->forwarding_name,
            'items_count' => $a->items_count,
            'date' => $a->created_at?->format('Y-m-d'),
        ]);

        $outboundList = $drs->map(fn($d) => [
            'dr_number' => $d->dr_number,
            'recipient' => $d->recipient_name,
            'warehouse' => $d->warehouse?->warehouse_name,
            'forwarding' => $d->forwarding?->forwarding_name,
            'asn_number' => $d->asn?->asn_number,
            'status' => $d->status,
            'date' => $d->request_date ? Carbon::parse($d->request_date)->format('Y-m-d') : null,
        ]);

        return response()->json([
            'inbound' => $type !== 'outbound' ? $inboundList : [],
            'outbound' => $type !== 'inbound' ? $outboundList : [],
            'status_items' => $statusItems,
            'summary' => [
                'total_inbound' => $inboundList->count(),
                'total_outbound' => $outboundList->count(),
                'total_items_received' => (int) $statusItems->where('status', 'RECEIVED')->sum('c'),
            ],
        ]);
    }
}