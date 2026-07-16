<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asn;
use App\Models\DeliveryRequest;
use App\Models\Stock;
use App\Models\Warehouse;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Total ASNs (Inbound)
        $totalAsn = Asn::count();
        
        // 2. Total Delivery Requests (Outbound)
        $totalDeliveryRequest = DeliveryRequest::count();
        
        // 3. Total Stock Items (Quantity)
        $totalStockQty = Stock::sum('qty');
        
        // 4. Total Warehouses
        $totalWarehouse = Warehouse::count();

        // 5. ASN trend for the last 6 months
        $asnTrend = Asn::selectRaw('DATE_FORMAT(created_at, "%b") as month, COUNT(*) as count')
                        ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                        ->groupBy('month')
                        ->orderByRaw('MIN(created_at)')
                        ->get();

        // 6. DR trend for the last 6 months
        $drTrend = DeliveryRequest::selectRaw('DATE_FORMAT(created_at, "%b") as month, COUNT(*) as count')
                        ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                        ->groupBy('month')
                        ->orderByRaw('MIN(created_at)')
                        ->get();

        // Ensure we have 6 months even if no data
        $months = collect(range(5, 0))->map(function($i) {
            return now()->subMonths($i)->format('b');
        });

        $asnChartData = $months->mapWithKeys(function($month) use ($asnTrend) {
            $found = $asnTrend->firstWhere('month', $month);
            return [$month => $found ? $found->count : 0];
        });

        $drChartData = $months->mapWithKeys(function($month) use ($drTrend) {
            $found = $drTrend->firstWhere('month', $month);
            return [$month => $found ? $found->count : 0];
        });
        
        $chartData = [
            'categories' => $months->values(),
            'series' => [
                [
                    'name' => 'Inbound (ASN)',
                    'data' => $asnChartData->values()
                ],
                [
                    'name' => 'Outbound (DR)',
                    'data' => $drChartData->values()
                ]
            ]
        ];

        // 7. Recent Activities
        $recentAsns = Asn::with('warehouse')->latest()->take(5)->get();
        $recentDeliveryRequests = DeliveryRequest::with('warehouse')->latest()->take(5)->get();

        return response()->json([
            'metrics' => [
                'total_asn' => $totalAsn,
                'total_dr' => $totalDeliveryRequest,
                'total_stock_qty' => $totalStockQty,
                'total_warehouse' => $totalWarehouse,
            ],
            'chart_data' => $chartData,
            'recent_asns' => $recentAsns,
            'recent_drs' => $recentDeliveryRequests,
        ]);
    }
}
