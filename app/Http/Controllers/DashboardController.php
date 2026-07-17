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

        // 5. Total Warehouse Capacity (from locations)
        $totalWarehouseCapacity = \App\Models\Location::sum('capacity');
        
        // 6. Occupied Capacity (from stock actual_volume)
        // Note: Assuming stock item correlates to actual_volume or qty. We'll use qty if volume is not tracked in stock, but normally it's volume. Let's check if stock has volume, or use totalStockQty.
        // Wait, WMS usually calculates SOR by summing volume of items in stock. Let's check stock table schema later or just use a sum of stock volume. If stock doesn't have volume, we can join with asn_items or just use qty for now. Actually, let's sum 'qty' or a hypothetical 'volume'. I will look at Stock model later, but for now I'll sum 'qty' as a proxy if volume isn't there, or maybe `sum('volume')` if it exists.
        // Since I need it to be accurate, let's just use totalStockQty as occupied if volume doesn't exist, but it's better to calculate based on `actual_volume` from `asn_items`.
        // Let's query `stocks` and join `asn_items` to sum `actual_volume`.
        $occupiedCapacity = Stock::join('asn_items', 'stocks.asn_item_id', '=', 'asn_items.id')
                                ->sum('asn_items.actual_volume');

        $sorPercentage = $totalWarehouseCapacity > 0 ? round(($occupiedCapacity / $totalWarehouseCapacity) * 100, 2) : 0;

        // 7. ASN trend for the last 6 months
        $asnTrend = Asn::selectRaw('DATE_FORMAT(created_at, "%b") as month, COUNT(*) as count')
                        ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                        ->groupBy('month')
                        ->orderByRaw('MIN(created_at)')
                        ->get();

        // 8. DR trend for the last 6 months
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

        // 9. Recent Activities
        $recentAsns = Asn::with('warehouse')->latest()->take(5)->get();
        $recentDeliveryRequests = DeliveryRequest::with('warehouse')->latest()->take(5)->get();

        return response()->json([
            'metrics' => [
                'total_asn' => $totalAsn,
                'total_dr' => $totalDeliveryRequest,
                'total_stock_qty' => $totalStockQty,
                'total_warehouse' => $totalWarehouse,
                'total_capacity' => $totalWarehouseCapacity,
                'occupied_capacity' => $occupiedCapacity,
                'sor_percentage' => $sorPercentage
            ],
            'chart_data' => $chartData,
            'recent_asns' => $recentAsns,
            'recent_drs' => $recentDeliveryRequests,
        ]);
    }
}
