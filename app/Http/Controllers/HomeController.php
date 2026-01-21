<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $locations = DB::table('locations')
            ->select('id', 'external_id', 'display_name', 'location_code', 'is_default')
            ->where('active', 1)
            ->orderBy('display_name')
            ->get();

        if ($locations->isEmpty()) {
            return view('home.index', [
                'locations' => collect(),
                'selected' => null,
                'otdpInbound' => collect(),
                'otdpOutbound' => collect(),
                'stockStrength'   => $this->buildStockStrength($selected), // ✅ New
            ]);
        }

        $selectedId = (int) $request->get('location_id');

        if (!$selectedId) {
            $default = $locations->firstWhere('is_default', 1);
            $selectedId = $default?->id ?? $locations->first()->id;
        }

        $selected = $locations->firstWhere('id', $selectedId);

        return view('home.index', [
            'locations'     => $locations,
            'selected'      => $selected,
            'otdpInbound'   => $this->buildOtdp($selected, 'inbound'),
            'otdpOutbound'  => $this->buildOtdp($selected, 'outbound'),
            'stockStrength'   => $this->buildStockStrength($selected), // ✅ New
        ]);
    }

   /**
 * Build Stock Strength data for tomorrow's planning
 * Only for OUTBOUND type
 * Simple comparison: inventory.qty vs planning.qty
 * Filter out planning with qty = 0
 */
protected function buildStockStrength($location)
{
    $locationCode = $location->location_code;
    $tomorrow = Carbon::tomorrow()->toDateString();

    // Get tomorrow's planning - ONLY OUTBOUND
    $tomorrowPlanning = DB::table('plannings')
        ->select(
            'code',
            'cutting_center',
            'type',
            DB::raw('SUM(qty) as planned_qty')
        )
        ->where('location_code', $locationCode)
        ->whereRaw('LOWER(type) = ?', ['outbound'])
        ->whereDate('plan_date', $tomorrow)
        ->groupBy('code', 'cutting_center', 'type')
        ->havingRaw('SUM(qty) > 0') // ✅ Filter: hanya yang planned_qty > 0
        ->get();

    // Get current inventory stock
    $inventory = DB::table('inventories')
        ->select(
            'code',
            'name',
            'qty',
            'stock_status',
            'rack_type',
            // Extract cutting center dari custom_field atau product_payload
            DB::raw("COALESCE(
                JSON_UNQUOTE(JSON_EXTRACT(custom_field, '$.cutting_center')),
                JSON_UNQUOTE(JSON_EXTRACT(product_payload, '$.custom_field.cutting_center')),
                'UNKNOWN'
            ) as cutting_center")
        )
        ->where('location_code', $locationCode)
        ->get()
        ->keyBy('code');

    // Merge planning with inventory
    $stockData = $tomorrowPlanning->map(function($plan) use ($inventory) {
        $stock = $inventory->get($plan->code);

        // Simple: hanya pakai qty dari inventory
        $currentStock = $stock ? (int)$stock->qty : 0;
        $plannedQty = (int)$plan->planned_qty;

        // Calculate difference
        $difference = $currentStock - $plannedQty;

        // Determine status
        if ($difference < 0) {
            $status = 'CRITICAL';
            $statusColor = 'danger';
        } elseif ($difference == 0) {
            $status = 'EXACT';
            $statusColor = 'warning';
        } elseif ($difference > 0 && $difference <= ($plannedQty * 0.2)) {
            $status = 'LOW';
            $statusColor = 'info';
        } else {
            $status = 'SAFE';
            $statusColor = 'success';
        }

        return (object)[
            'code' => $plan->code,
            'name' => $stock->name ?? 'N/A',
            'cutting_center' => $plan->cutting_center,
            'type' => $plan->type,
            'planned_qty' => $plannedQty,
            'current_stock' => $currentStock,
            'difference' => $difference,
            'status' => $status,
            'status_color' => $statusColor,
            'rack_type' => $stock->rack_type ?? '-',
            'stock_status' => $stock->stock_status ?? 'unknown',
        ];
    });

    // Sort by status priority: CRITICAL > EXACT > LOW > SAFE
    $statusPriority = [
        'CRITICAL' => 1,
        'EXACT' => 2,
        'LOW' => 3,
        'SAFE' => 4,
    ];

    return $stockData->sortBy(function($item) use ($statusPriority) {
        return $statusPriority[$item->status] ?? 999;
    })->values();
}


    /**
     * Get detail data for specific day and cutting center
     */
    public function getDetail(Request $request)
    {
        $locationCode = $request->get('location_code');
        $externalId = $request->get('external_id');
        $type = $request->get('type'); // inbound or outbound
        $day = (int) $request->get('day');
        $cuttingCenter = $request->get('cutting_center');

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Build target date
        $targetDate = Carbon::createFromDate($currentYear, $currentMonth, $day)->toDateString();

        // Get planning data
        $planningData = DB::table('plannings')
            ->select(
                'id',
                'plan_date',
                'cutting_center',
                'qty',
                'type',
                'code',
                DB::raw("'planning' as source")
            )
            ->where('location_code', $locationCode)
            ->whereRaw('LOWER(type) = ?', [strtolower($type)])
            ->where('cutting_center', $cuttingCenter)
            ->whereDate('plan_date', $targetDate)
            ->get();

        // Get actual data (orders)
        // ✅ PERBAIKAN: Extract SKU dan Product Name dari raw_payload JSON
        $actualQuery = DB::table('order_details as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->select(
                'od.id',
                'o.id as order_id',
                'o.ref_number',
                'o.external_id as external_order_id',
                'od.code',
                'od.serial_number',
                // ✅ Extract SKU dari raw_payload
                DB::raw("COALESCE(
                    JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, '$.product.sku')),
                    JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, '$.sku')),
                    od.code
                ) as sku"),
                // ✅ Extract Product Name dari raw_payload
                DB::raw("COALESCE(
                    JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, '$.product.name')),
                    JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, '$.product_name')),
                    JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, '$.name')),
                    'N/A'
                ) as product_name"),
                DB::raw('COALESCE(od.qty_process, od.qty) as qty'),
                DB::raw('COALESCE(o.external_created_at, o.created_at) as order_date'),
                'od.status',
                'od.rack',
                'od.rack_source',
                // ✅ Extract Cutting Center
                DB::raw("
                    CASE
                        WHEN LOWER(o.type) = 'inbound' THEN
                            COALESCE(
                                JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, '$.product.custom_field.cutting_center')),
                                'UNKNOWN'
                            )
                        WHEN LOWER(o.type) = 'outbound' THEN
                            COALESCE(od.rack, 'UNKNOWN')
                        ELSE 'UNKNOWN'
                    END as cutting_center
                "),
                DB::raw("'actual' as source")
            )
            ->whereRaw('LOWER(o.type) = ?', [strtolower($type)])
            ->where('o.external_location_id', $externalId)
            ->whereRaw('LOWER(od.status) = ?', ['done'])
            ->whereDate(DB::raw('COALESCE(o.external_created_at, o.created_at)'), $targetDate)
            ->get();

        // Filter actual data by cutting center
        $actualData = $actualQuery->filter(function($item) use ($cuttingCenter) {
            return $item->cutting_center === $cuttingCenter;
        })->values();

        return response()->json([
            'planning' => $planningData,
            'actual' => $actualData,
            'summary' => [
                'total_plan_qty' => $planningData->sum('qty'),
                'total_actual_qty' => $actualData->sum('qty'),
                'date' => $targetDate,
                'cutting_center' => $cuttingCenter,
                'type' => $type
            ]
        ]);
    }

    protected function buildOtdp($location, string $type)
    {
        $locationCode = $location->location_code;
        $externalId   = $location->external_id;

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $planning = DB::table('plannings')
            ->select(
                'cutting_center',
                DB::raw('DAY(plan_date) as day'),
                DB::raw('SUM(qty) as plan_qty'),
                DB::raw('0 as act_qty')
            )
            ->where('location_code', $locationCode)
            ->whereRaw('LOWER(type) = ?', [$type])
            ->whereBetween('plan_date', [$startOfMonth, $endOfMonth])
            ->groupBy('cutting_center', DB::raw('DAY(plan_date)'));

        $actualBase = DB::table('order_details as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->select(
                DB::raw("
                    CASE
                        WHEN LOWER(o.type) = 'inbound' THEN
                            COALESCE(
                                JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, '$.product.custom_field.cutting_center')),
                                'UNKNOWN'
                            )
                        WHEN LOWER(o.type) = 'outbound' THEN
                            COALESCE(
                                JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, '$.rack')),
                                'UNKNOWN'
                            )
                        ELSE 'UNKNOWN'
                    END as cutting_center
                "),
                DB::raw('DAY(COALESCE(o.external_created_at, o.created_at)) as day'),
                DB::raw('0 as plan_qty'),
                DB::raw('COALESCE(od.qty_process, od.qty) as act_qty')
            )
            ->whereRaw('LOWER(o.type) = ?', [$type])
            ->where('o.external_location_id', $externalId)
            ->whereRaw('LOWER(od.status) = ?', ['done'])
            ->whereBetween(
                DB::raw('DATE(COALESCE(o.external_created_at, o.created_at))'),
                [$startOfMonth, $endOfMonth]
            );

        $actual = DB::query()
            ->fromSub($actualBase, 'x')
            ->select(
                'x.cutting_center',
                'x.day',
                DB::raw('SUM(x.plan_qty) as plan_qty'),
                DB::raw('SUM(x.act_qty) as act_qty')
            )
            ->groupBy('x.cutting_center', 'x.day');

        $union = $planning->unionAll($actual);

        $result = DB::query()
            ->fromSub($union, 'u')
            ->select(
                'u.cutting_center',
                'u.day',
                DB::raw('SUM(u.plan_qty) as plan_qty'),
                DB::raw('SUM(u.act_qty) as act_qty'),
                DB::raw("
                    CASE
                        WHEN SUM(u.plan_qty) > 0
                        THEN ROUND((SUM(u.act_qty) / SUM(u.plan_qty)) * 100, 2)
                        ELSE NULL
                    END as percentage
                ")
            )
            ->groupBy('u.cutting_center', 'u.day')
            ->orderBy('u.cutting_center')
            ->orderBy('u.day')
            ->get()
            ->groupBy('cutting_center');

        return $result;
    }
}
