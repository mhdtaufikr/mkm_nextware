<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Planning;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\PlanningTemplateExport;
use App\Imports\PlanningImport;
use Maatwebsite\Excel\Facades\Excel;

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
        $locations = DB::table('locations')
            ->select('location_code')
            ->whereNotNull('location_code')
            ->where('location_code', '<>', '')
            ->where('active', 1)
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

        // Ambil cutting_center berdasarkan type (SEMUA DARI ORDERS)
        if ($type === 'inbound') {
            $subquery = DB::table('order_details as od')
                ->join('orders as o', 'o.id', '=', 'od.order_id')
                ->join('locations as loc', 'loc.external_id', '=', 'o.external_location_id')
                ->where('loc.location_code', $locationCode)
                ->whereRaw('LOWER(o.type) = ?', ['inbound'])
                ->select(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, "$.product.custom_field.cutting_center")) as cutting_center'))
                ->distinct();

            $cuttingCenters = DB::table(DB::raw("({$subquery->toSql()}) as centers"))
                ->mergeBindings($subquery)
                ->whereNotNull('cutting_center')
                ->where('cutting_center', '<>', '')
                ->pluck('cutting_center')
                ->values();
        } else {
            $subquery = DB::table('order_details as od')
                ->join('orders as o', 'o.id', '=', 'od.order_id')
                ->join('locations as loc', 'loc.external_id', '=', 'o.external_location_id')
                ->where('loc.location_code', $locationCode)
                ->whereRaw('LOWER(o.type) = ?', ['outbound'])
                ->select(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, "$.rack")) as rack'))
                ->distinct();

            $cuttingCenters = DB::table(DB::raw("({$subquery->toSql()}) as racks"))
                ->mergeBindings($subquery)
                ->whereNotNull('rack')
                ->where('rack', '<>', '')
                ->pluck('rack')
                ->values();
        }

        $codes = DB::table('order_details as od')
            ->join('orders as o', 'o.id', '=', 'od.order_id')
            ->join('locations as loc', 'loc.external_id', '=', 'o.external_location_id')
            ->where('loc.location_code', $locationCode)
            ->whereRaw('LOWER(o.type) = ?', [$type])
            ->select('od.code')
            ->distinct()
            ->orderBy('od.code')
            ->pluck('code');

        $groups = collect();

        foreach ($cuttingCenters as $cc) {
            if ($type === 'inbound') {
                $ccCodes = DB::table('order_details as od')
                    ->join('orders as o', 'o.id', '=', 'od.order_id')
                    ->join('locations as loc', 'loc.external_id', '=', 'o.external_location_id')
                    ->where('loc.location_code', $locationCode)
                    ->whereRaw('LOWER(o.type) = ?', ['inbound'])
                    ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, "$.product.custom_field.cutting_center")) = ?', [$cc])
                    ->distinct()
                    ->pluck('od.code');
            } else {
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

        $plans = Planning::query()
            ->where('type', $type)
            ->where('location_code', $locationCode)
            ->whereBetween('plan_date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->get();

        $qtyMap = [];
        foreach ($plans as $p) {
            $cc   = $p->cutting_center;
            $code = $p->code;
            $date = $p->plan_date->toDateString();

            $qtyMap[$cc][$code][$date] = (int) $p->qty;
        }

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

    public function downloadTemplate(Request $request)
{
    $request->validate([
        'location_code' => ['required', 'string'],
        'type' => ['required', 'in:inbound,outbound'],
        'month' => ['required', 'date_format:Y-m'],
    ]);

    $locationCode = $request->query('location_code');
    $type = $request->query('type');
    $month = $request->query('month');

    $filename = "Planning_Template_{$type}_{$locationCode}_{$month}.xlsx";

    return Excel::download(
        new PlanningTemplateExport($locationCode, $type, $month),
        $filename
    );
}


public function import(Request $request)
{
    $request->validate([
        'month' => ['required', 'date_format:Y-m'], // ✅ Tambah validasi month
        'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
    ]);

    try {
        // ✅ Pass month parameter ke constructor
        $import = new PlanningImport($request->input('month'));
        Excel::import($import, $request->file('file'));

        $stats = $import->getStats();

        return response()->json([
            'ok' => true,
            'inserted' => $stats['inserted'],
            'updated' => $stats['updated'],
            'skipped' => $stats['skipped'],
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'ok' => false,
            'message' => 'Import failed: ' . $e->getMessage(),
        ], 422);
    }
}

}
