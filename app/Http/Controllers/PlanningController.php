<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Planning;
use Carbon\Carbon;

class PlanningController extends Controller
{
    public function index()
    {
        return view('planning.index');
    }

    /**
     * Load master location_code
     */
    public function meta()
    {
        $locations = DB::table('view_inventory_item_cutting_center_by_location')
            ->select('location_code')
            ->whereNotNull('location_code')
            ->where('location_code', '<>', '')
            ->distinct()
            ->orderBy('location_code')
            ->pluck('location_code');

        return response()->json([
            'locations' => $locations,
        ]);
    }

    /**
     * Render planning table (grouped by cutting_center -> code)
     */
    public function table(Request $request)
    {
        $request->validate([
            'location_code' => ['required', 'string'],
            'month' => ['required', 'date_format:Y-m'],
            'type' => ['required', 'in:inbound,outbound'],
        ]);

        $locationCode = $request->query('location_code');
        $month = $request->query('month');
        $type = $request->query('type');

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        // ✅ 1. Ambil cutting_center berdasarkan type
        if ($type === 'inbound') {
            // INBOUND: Ambil dari inventory_items (SCI, USC, AAP, dll)
            $cuttingCenters = DB::table('view_inventory_item_cutting_center_by_location')
                ->where('location_code', $locationCode)
                ->whereNotNull('cutting_center')
                ->where('cutting_center', '<>', '')
                ->distinct()
                ->pluck('cutting_center')
                ->values();
        } else {
            // OUTBOUND: Ambil dari rack order_details (PRESS A, PRESS B, dll)
            $cuttingCenters = DB::table('order_details as od')
                ->join('orders as o', 'o.id', '=', 'od.order_id')
                ->join('locations as loc', 'loc.external_id', '=', 'o.external_location_id')
                ->where('loc.location_code', $locationCode)
                ->whereRaw('LOWER(o.type) = ?', ['outbound'])
                ->select(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, "$.rack")) as rack'))
                ->whereNotNull(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, "$.rack"))'))
                ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, "$.rack")) <> ?', [''])
                ->distinct()
                ->pluck('rack')
                ->values();
        }

        // ✅ 2. Ambil code berdasarkan type
        if ($type === 'inbound') {
            // INBOUND: Ambil semua code dari inventory_items
            $codes = DB::table('inventory_items')
                ->where('location_code', $locationCode)
                ->whereNotNull('code')
                ->where('code', '<>', '')
                ->select('code')
                ->distinct()
                ->orderBy('code')
                ->pluck('code');
        } else {
            // OUTBOUND: Ambil code yang pernah keluar
            $codes = DB::table('order_details as od')
                ->join('orders as o', 'o.id', '=', 'od.order_id')
                ->join('locations as loc', 'loc.external_id', '=', 'o.external_location_id')
                ->where('loc.location_code', $locationCode)
                ->whereRaw('LOWER(o.type) = ?', ['outbound'])
                ->select('od.code')
                ->distinct()
                ->orderBy('od.code')
                ->pluck('code');
        }

        // ✅ 3. Group manual berdasarkan type
        $groups = collect();

        foreach ($cuttingCenters as $cc) {
            if ($type === 'inbound') {
                // INBOUND: Filter codes per cutting_center dari inventory
                $ccCodes = DB::table('inventory_items')
                    ->where('location_code', $locationCode)
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(custom_field, '$.cutting_center')) = ?", [$cc])
                    ->distinct()
                    ->pluck('code');
            } else {
                // OUTBOUND: Filter codes per rack
                $ccCodes = DB::table('order_details as od')
                    ->join('orders as o', 'o.id', '=', 'od.order_id')
                    ->join('locations as loc', 'loc.external_id', '=', 'o.external_location_id')
                    ->where('loc.location_code', $locationCode)
                    ->whereRaw('LOWER(o.type) = ?', ['outbound'])
                    ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, "$.rack")) = ?', [$cc])
                    ->distinct()
                    ->pluck('od.code');
            }

            $groups[$cc] = $ccCodes->map(fn ($c) => (object)['code' => $c]);
        }

        /**
         * Ambil planning existing
         */
        $plans = Planning::query()
            ->where('type', $type)
            ->where('location_code', $locationCode)
            ->whereBetween('plan_date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->get();

        /**
         * Map: cutting_center -> code -> date -> qty
         */
        $qtyMap = [];
        foreach ($plans as $p) {
            $cc   = $p->cutting_center;
            $code = $p->code;
            $date = $p->plan_date->toDateString();

            $qtyMap[$cc][$code][$date] = (int) $p->qty;
        }

        /**
         * Generate tanggal sebulan
         */
        $dates = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dates[] = [
                'date' => $cursor->toDateString(),
                'label' => $cursor->format('d M'),
                'weekday' => $cursor->format('D'),
            ];
            $cursor->addDay();
        }

        $html = view('planning._table', [
            'location_code' => $locationCode,
            'month' => $month,
            'type' => $type,
            'dates' => $dates,
            'groups' => $groups,
            'qtyMap' => $qtyMap,
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Autosave per cell (by code)
     */
    public function upsert(Request $request)
    {
        $request->validate([
            'location_code' => ['required', 'string'],
            'cutting_center' => ['required', 'string'],
            'code' => ['required', 'string'],
            'plan_date' => ['required', 'date'],
            'type' => ['required', 'in:inbound,outbound'],
            'qty' => ['required', 'integer', 'min:0'],
        ]);

        // ✅ Langsung save tanpa validasi master
        // Planning bisa dibuat untuk future items yang belum ada di inventory

        $row = Planning::updateOrCreate(
            [
                'location_code' => $request->location_code,
                'cutting_center' => $request->cutting_center,
                'code' => $request->code,
                'type' => $request->type,
                'plan_date' => $request->plan_date,
            ],
            [
                'qty' => $request->qty,
            ]
        );

        return response()->json([
            'ok' => true,
            'id' => $row->id,
            'updated_at' => $row->updated_at?->toDateTimeString(),
        ]);
    }
}
