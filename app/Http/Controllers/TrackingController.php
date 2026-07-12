<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asn;
use App\Models\AsnItem;

class TrackingController extends Controller
{
    /**
     * Track cargo based on Master BL, Host BL, or Container Number
     */
    public function trackCargo(string $identifier)
    {
        // Try finding by Host BL first (Consignee level)
        $items = AsnItem::where('host_bl', $identifier)->with(['asn', 'consignee'])->get();
        if ($items->count() > 0) {
            return response()->json([
                'type' => 'HOST_BL',
                'data' => $items
            ]);
        }

        // Try finding by Master BL or Container Number (Forwarding level)
        $asns = Asn::where('no_master_bl', $identifier)
            ->orWhere('no_container', $identifier)
            ->with(['items.consignee', 'forwarding', 'warehouse'])
            ->get();
            
        if ($asns->count() > 0) {
            return response()->json([
                'type' => 'MASTER_BL_OR_CONTAINER',
                'data' => $asns
            ]);
        }

        return response()->json(['message' => 'Cargo not found'], 404);
    }
}
