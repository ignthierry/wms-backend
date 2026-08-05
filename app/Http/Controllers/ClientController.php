<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asn;
use App\Models\AsnItem;
use App\Models\Invoice;
use App\Models\Consignee;
use Illuminate\Support\Facades\Storage;

/**
 * Client Dashboard API — untuk user role 'forwarding'.
 * Semua data di-filter otomatis per consignee milik user yang login,
 * sehingga client hanya melihat barang/invoice miliknya sendiri.
 */
class ClientController extends Controller
{
    /**
     * Resolve consignee scope milik user yang login.
     * Mendukung dua mode:
     *  - user.consignee_id terisi  -> scope = consignee itu (client per-consignee)
     *  - user.consignee_id kosong  -> scope = semua consignee di bawah forwarding milik user
     */
    protected function scopeConsigneeIds(Request $request): array
    {
        $user = $request->user();

        // Mode 1: user terikat langsung ke satu consignee
        if ($user->consignee_id) {
            return [$user->consignee_id];
        }

        // Mode 2: user adalah forwarding/EMKL -> consignee dari semua ASN miliknya.
        // Jalur: Forwarding(user_id) --hasMany--> Asn(forwarding_id) --hasMany--> AsnItem(consignee_id)
        $forwarding = \App\Models\Forwarding::where('user_id', $user->id)->first();
        if ($forwarding) {
            $asnIds = \App\Models\Asn::where('forwarding_id', $forwarding->id)->pluck('id');
            return AsnItem::whereIn('asn_id', $asnIds)
                ->whereNotNull('consignee_id')
                ->pluck('consignee_id')
                ->unique()
                ->all();
        }

        return [];
    }

    /**
     * Ringkasan dashboard client: KPI + tren inbound/outbound + status terbaru.
     */
    public function dashboard(Request $request)
    {
        $consigneeIds = $this->scopeConsigneeIds($request);

        if (empty($consigneeIds)) {
            return response()->json([
                'message' => 'Akun anda belum terhubung ke consignee/forwarding manapun. Hubungi administrator.',
                'consignees' => [],
                'metrics' => ['total_items' => 0, 'inbound' => 0, 'outbound' => 0, 'in_warehouse' => 0, 'unpaid_invoices' => 0],
                'recent_items' => [],
            ]);
        }

        $items = AsnItem::whereIn('consignee_id', $consigneeIds);
        $invoices = Invoice::whereIn('asn_item_id', $items->pluck('id'));

        $metrics = [
            'total_items'   => (clone $items)->count(),
            'pending'       => (clone $items)->where('status', 'PENDING')->count(),
            'in_warehouse'  => (clone $items)->where('status', 'RECEIVED')->count(),
            'ready'         => (clone $items)->where('status', 'READY_TO_DISPATCH')->count(),
            'inbound'       => (clone $items)->where('status', 'RECEIVED')->count(),
            'outbound'      => (clone $items)->where('status', 'READY_TO_DISPATCH')->count(),
            'unpaid_invoices' => (clone $invoices)->where('status', 'UNPAID')->count(),
        ];

        $recentItems = (clone $items)
            ->with(['consignee', 'asn', 'invoice'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return response()->json([
            'consignees' => Consignee::whereIn('id', $consigneeIds)->get(['id', 'name', 'email', 'phone', 'address', 'status']),
            'metrics' => $metrics,
            'recent_items' => $recentItems,
        ]);
    }

    /**
     * Daftar semua barang milik client + status + lokasi.
     */
    public function items(Request $request)
    {
        $consigneeIds = $this->scopeConsigneeIds($request);

        if (empty($consigneeIds)) {
            return response()->json(['data' => []]);
        }

        $query = AsnItem::whereIn('consignee_id', $consigneeIds)
            ->with(['consignee', 'asn', 'invoice', 'photos', 'deliveryRequest']);

        // Filter opsional
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        if ($request->has('manifest') && $request->manifest) {
            $query->whereHas('asn', function ($q) use ($request) {
                $q->where('asn_number', 'like', "%{$request->manifest}%")
                  ->orWhere('no_master_bl', 'like', "%{$request->manifest}%")
                  ->orWhere('no_container', 'like', "%{$request->manifest}%");
            });
        }
        if ($request->has('search') && $request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('item_name', 'like', "%{$s}%")
                  ->orWhere('item_code', 'like', "%{$s}%")
                  ->orWhere('host_bl', 'like', "%{$s}%")
                  ->orWhere('qr_id', 'like', "%{$s}%");
            });
        }

        $data = $query->latest('updated_at')->paginate($request->get('per_page', 15));

        return response()->json($data);
    }

    /**
     * Detail satu barang: riwayat pergerakan, foto dokumentasi, invoice.
     */
    public function itemDetail(Request $request, string $id)
    {
        $consigneeIds = $this->scopeConsigneeIds($request);

        $item = AsnItem::whereIn('consignee_id', $consigneeIds)
            ->with(['consignee', 'asn', 'invoice', 'photos', 'deliveryRequest', 'histories'])
            ->find($id);

        if (!$item) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }

        return response()->json($item);
    }

    /**
     * Daftar invoice milik client, ringkas + filter status.
     */
    public function invoices(Request $request)
    {
        $consigneeIds = $this->scopeConsigneeIds($request);

        if (empty($consigneeIds)) {
            return response()->json(['data' => [], 'summary' => ['total' => 0, 'paid' => 0, 'unpaid' => 0]]);
        }

        $itemIds = AsnItem::whereIn('consignee_id', $consigneeIds)->pluck('id');

        $query = Invoice::whereIn('asn_item_id', $itemIds)
            ->with(['asnItem.consignee', 'asnItem.asn']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->get();

        return response()->json([
            'data' => $invoices,
            'summary' => [
                'total'  => $invoices->count(),
                'paid'   => $invoices->where('status', 'PAID')->count(),
                'unpaid' => $invoices->where('status', 'UNPAID')->count(),
                'total_amount' => $invoices->sum('total_amount'),
                'unpaid_amount' => $invoices->where('status', 'UNPAID')->sum('total_amount'),
            ],
        ]);
    }

    /**
     * Lacak barang via QR code / nomor referensi milik client sendiri.
     */
    public function track(Request $request, string $identifier)
    {
        $consigneeIds = $this->scopeConsigneeIds($request);

        $item = AsnItem::whereIn('consignee_id', $consigneeIds)
            ->where(function ($q) use ($identifier) {
                $q->where('qr_id', $identifier)
                  ->orWhere('item_code', $identifier)
                  ->orWhere('host_bl', $identifier);
            })
            ->with(['consignee', 'asn', 'invoice', 'photos', 'histories'])
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Barang tidak ditemukan. Periksa kembali nomor QR/referensi.'], 404);
        }

        return response()->json($item);
    }

    /**
     * Daftar manifest (ASN) milik client — untuk dropdown filter di halaman barang.
     */
    public function manifests(Request $request)
    {
        $consigneeIds = $this->scopeConsigneeIds($request);

        if (empty($consigneeIds)) {
            return response()->json(['data' => []]);
        }

        $asnIds = AsnItem::whereIn('consignee_id', $consigneeIds)->pluck('asn_id')->unique();

        $manifests = Asn::whereIn('id', $asnIds)
            ->get(['id', 'asn_number', 'no_master_bl', 'no_container', 'voyage', 'no_segel'])
            ->map(function ($asn) {
                return [
                    'id'         => $asn->id,
                    'asn_number' => $asn->asn_number,
                    'no_master_bl' => $asn->no_master_bl,
                    'no_container' => $asn->no_container,
                    'voyage'     => $asn->voyage,
                    'no_segel'   => $asn->no_segel,
                    'label'      => $asn->asn_number . ($asn->no_container ? " · {$asn->no_container}" : ''),
                ];
            });

        return response()->json(['data' => $manifests]);
    }
}
