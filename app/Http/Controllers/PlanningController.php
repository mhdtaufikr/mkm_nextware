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
        $type = $request->query('type'); // inbound | outbound


        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        /**
         * Ambil cutting_center + code
         * Source:
         * - view_inventory_item_cutting_center_by_location
         * - order_details (real transaction)
         */
       // 1. Ambil cutting center valid untuk location
        $cuttingCenters = DB::table('view_inventory_item_cutting_center_by_location')
        ->where('location_code', $locationCode)
        ->whereNotNull('cutting_center')
        ->where('cutting_center', '<>', '')
        ->distinct()
        ->pluck('cutting_center')
        ->values();

        // 2. Ambil code dari order_details untuk location ini
        $codes = DB::table('order_details as od')
        ->join('orders as o', 'o.id', '=', 'od.order_id')
        ->where('o.external_location_id', function ($q) use ($locationCode) {
            $q->select('external_id')
            ->from('locations')
            ->where('location_code', $locationCode)
            ->limit(1);
        })
        ->select('od.code')
        ->distinct()
        ->orderBy('od.code')
        ->pluck('code');

        // 3. Group manual: cutting_center => codes
        $groups = collect();

        foreach ($cuttingCenters as $cc) {
        $groups[$cc] = $codes->map(fn ($c) => (object)['code' => $c]);
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
            'type' => $type, // <—— ini penting
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


        // validasi master
        $exists = DB::table('view_inventory_item_cutting_center_by_location')
            ->where('location_code', $request->location_code)
            ->where('cutting_center', $request->cutting_center)
            ->where('code', $request->code)
            ->exists();

        if (!$exists) {
            return response()->json([
                'ok' => false,
                'message' => 'Master location / cutting_center / code tidak valid.',
            ], 422);
        }

        $row = Planning::updateOrCreate(
            [
                'location_code' => $request->location_code,
                'cutting_center' => $request->cutting_center,
                'code' => $request->code,
                'type' => $request->type, // inbound / outbound
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
