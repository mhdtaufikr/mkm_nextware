<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // dropdown: only active locations
        $locations = DB::table('locations')
            ->select('id', 'external_id', 'display_name', 'location_code', 'is_default')
            ->where('active', 1)
            ->orderBy('display_name')
            ->get();

        if ($locations->isEmpty()) {
            return view('home.index', [
                'locations' => $locations,
                'selected' => null,
                'stats' => null,
                'byStatus' => collect(),
                'topItems' => collect(),
                'byCuttingCenter' => collect(),
            ])->with('failed', 'Tidak ada Location yang active.');
        }

        // selected location (internal id)
        $selectedId = $request->get('location_id');

        if (!$selectedId) {
            $defaultLoc = $locations->firstWhere('is_default', 1);
            $selectedId = $defaultLoc?->id ?? $locations->first()->id;
        }

        $selected = $locations->firstWhere('id', (int) $selectedId);
        if (!$selected) {
            $selected = $locations->first();
            $selectedId = $selected->id;
        }

        // filter inventories by external_location_id
        $externalLocationId = $selected->external_id;

        // SUMMARY (inventories table)
        $stats = DB::table('inventories')
            ->where('external_location_id', $externalLocationId)
            ->selectRaw('COUNT(DISTINCT code) AS total_sku')
            ->selectRaw('COALESCE(SUM(qty),0) AS qty')
            ->selectRaw('COALESCE(SUM(qty_goods),0) AS qty_goods')
            ->selectRaw('COALESCE(SUM(qty_available),0) AS qty_available')
            ->selectRaw('COALESCE(SUM(qty_incoming),0) AS qty_incoming')
            ->selectRaw('COALESCE(SUM(qty_outgoing),0) AS qty_outgoing')
            ->first();

        // stock status breakdown (inventories)
        $byStatus = DB::table('inventories')
            ->where('external_location_id', $externalLocationId)
            ->selectRaw("COALESCE(stock_status,'(empty)') AS stock_status, COUNT(*) AS total_rows")
            ->groupBy('stock_status')
            ->orderByDesc('total_rows')
            ->get();

        // top items (inventories)
        $topItems = DB::table('inventories')
            ->where('external_location_id', $externalLocationId)
            ->select('code', 'name', 'qty_available', 'qty', 'qty_goods', 'stock_status')
            ->orderByDesc('qty_available')
            ->limit(10)
            ->get();

        /**
         * ✅ SUM berdasarkan cutting_center untuk selected location
         * Ambil dari inventory_items (karena cutting_center ada di custom_field JSON).
         * Filter pakai location_code (sesuai view).
         */
        $byCuttingCenter = DB::table('inventory_items')
            ->where('location_code', $selected->location_code)
            ->whereRaw("JSON_EXTRACT(custom_field, '$.cutting_center') IS NOT NULL")
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_field, '$.cutting_center')) <> ''")
            ->selectRaw("
                JSON_UNQUOTE(JSON_EXTRACT(custom_field, '$.cutting_center')) AS cutting_center,
                COUNT(*) AS total_rows,
                COALESCE(SUM(qty),0) AS total_qty
            ")
            ->groupBy('cutting_center')
            ->orderByDesc('total_qty')
            ->get();

        return view('home.index', compact(
            'locations',
            'selected',
            'stats',
            'byStatus',
            'topItems',
            'byCuttingCenter'
        ));
    }
}
