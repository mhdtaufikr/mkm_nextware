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
        ]);
    }

    protected function buildOtdp($location, string $type)
    {
        $locationCode = $location->location_code;
        $externalId   = $location->external_id;

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        /**
         * PLANNING (filtered by current month)
         */
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

        /**
         * ACTUAL - Pakai external_created_at untuk tanggal transaksi asli
         */
        $actualBase = DB::table('order_details as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('inventory_items as ii', 'ii.code', '=', 'od.code')
            ->select(
                DB::raw("
                    COALESCE(
                        JSON_UNQUOTE(JSON_EXTRACT(ii.custom_field,'$.cutting_center')),
                        'UNKNOWN'
                    ) as cutting_center
                "),
                // ✅ GUNAKAN external_created_at, fallback ke created_at
                DB::raw('DAY(COALESCE(o.external_created_at, o.created_at)) as day'),
                DB::raw('0 as plan_qty'),
                DB::raw('COALESCE(od.qty_process, od.qty) as act_qty')
            )
            ->whereRaw('LOWER(o.type) = ?', [$type])
            ->where('o.external_location_id', $externalId)
            ->whereRaw('LOWER(od.status) = ?', ['done'])
            // ✅ Filter berdasarkan external_created_at
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

        /**
         * UNION planning + actual
         */
        $union = $planning->unionAll($actual);

        /**
         * FINAL RESULT
         */
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
