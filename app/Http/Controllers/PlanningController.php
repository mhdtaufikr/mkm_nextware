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
     * Load master data dari VIEW:
     * - list location_code
     * - list cutting_center by location_code
     */
    public function meta(Request $request)
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
     * Render table 1 bulan (tanggal) -> qty (editable)
     * Server-side: return HTML partial
     */
    public function table(Request $request)
    {
        $request->validate([
            'location_code' => ['required', 'string'],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $locationCode = $request->query('location_code');
        $month = $request->query('month');
        $type = 'planning';

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        // list semua cutting center untuk location ini
        $cuttingCenters = DB::table('view_inventory_item_cutting_center_by_location')
            ->select('cutting_center')
            ->where('location_code', $locationCode)
            ->whereNotNull('cutting_center')
            ->where('cutting_center', '<>', '')
            ->distinct()
            ->orderBy('cutting_center')
            ->pluck('cutting_center')
            ->values();

        // ambil semua data planning untuk location + bulan ini (semua cutting center)
        $plans = Planning::query()
            ->where('type', $type)
            ->where('location_code', $locationCode)
            ->whereBetween('plan_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        // map qty per cutting_center + date
        $qtyMap = [];
        foreach ($plans as $p) {
            $cc = $p->cutting_center;
            $d  = $p->plan_date->toDateString();
            $qtyMap[$cc][$d] = (int) $p->qty;
        }

        // Generate list tanggal sebulan
        $dates = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dates[] = [
                'date' => $cursor->toDateString(),
                'label' => $cursor->format('d M Y'),
                'weekday' => $cursor->format('D'),
            ];
            $cursor->addDay();
        }

        $html = view('planning._table', [
            'dates' => $dates,
            'location_code' => $locationCode,
            'month' => $month,
            'type' => $type,
            'cutting_centers' => $cuttingCenters,
            'qtyMap' => $qtyMap,
        ])->render();

        return response()->json(['html' => $html]);
    }


    /**
     * Autosave per cell (no submit).
     */
    public function upsert(Request $request)
    {
        $request->validate([
            'location_code' => ['required', 'string'],
            'cutting_center' => ['required', 'string'],
            'plan_date' => ['required', 'date'],
            'qty' => ['required', 'integer', 'min:0'],
        ]);

        $data = $request->only(['location_code', 'cutting_center', 'plan_date', 'qty']);
        $data['type'] = 'planning';

        $exists = DB::table('view_inventory_item_cutting_center_by_location')
            ->where('location_code', $data['location_code'])
            ->where('cutting_center', $data['cutting_center'])
            ->exists();

        if (!$exists) {
            return response()->json([
                'ok' => false,
                'message' => 'Master location/cutting_center tidak valid (tidak ada di view).'
            ], 422);
        }

        $row = Planning::updateOrCreate(
            [
                'location_code' => $data['location_code'],
                'cutting_center' => $data['cutting_center'],
                'type' => $data['type'],
                'plan_date' => $data['plan_date'],
            ],
            [
                'qty' => $data['qty'],
            ]
        );

        return response()->json([
            'ok' => true,
            'id' => $row->id,
            'updated_at' => $row->updated_at?->toDateTimeString(),
        ]);
    }

}
