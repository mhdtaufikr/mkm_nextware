<?php

namespace App\Http\Controllers;

use App\Models\ApiEndpoint;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderItemController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::where('active', 1)
            ->orderBy('location_code')
            ->get();

        $query = Order::query()->latest();

        if ($request->filled('location_external_id')) {
            $query->where('external_location_id', $request->location_external_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('external_created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $orders = $query->limit(1000)->get();

        return view('order_items.index', compact(
            'locations',
            'orders'
        ));
    }

        public function detail($id)
    {
        $order = Order::with('details')->findOrFail($id);

        return response()->json([
            'data' => $order->details
        ]);
    }

    public function sync(Request $request)
    {
        $request->validate([
            'location_external_id' => ['required'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'type' => ['required', 'in:inbound,outbound'],
        ]);

        $endpoint = ApiEndpoint::where('code', 'IWS_Get_Orders')->first();
        if (!$endpoint) {
            return back()->with('failed', 'API endpoint IWS_Get_Orders not found.');
        }

        $decode = fn ($v) => is_array($v) ? $v : (json_decode($v, true) ?? []);

        $headers = $decode($endpoint->headers);
        if ($endpoint->auth_type === 'api_key') {
            $headers[$endpoint->auth_key] = $endpoint->auth_value;
        }

        $params = array_merge($decode($endpoint->params), [
            'location_id' => $request->location_external_id,
            'type' => $request->type,
            'start_date' => $request->start_date . ' 00:00:00',
            'end_date' => $request->end_date . ' 23:59:59',
            'limit' => 100,
        ]);

        $url = rtrim($endpoint->base_url, '/') . '/' . ltrim($endpoint->path, '/');

        try {
            $response = Http::withHeaders($headers)
                ->timeout(60)
                ->retry(2, 500)
                ->get($url, $params);

            if (!$response->successful()) {
                return back()->with('failed', 'API failed: ' . $response->body());
            }

            $rows = data_get($response->json(), 'data', []);
            if (empty($rows)) {
                return back()->with('success', 'No data returned.');
            }

            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    $order = Order::updateOrCreate(
                        ['external_id' => data_get($row, '_id')],
                        [
                            'ref_number' => data_get($row, 'refNumber'),
                            'type' => data_get($row, 'type'),
                            'status' => data_get($row, 'status'),
                            'customer_name' => data_get($row, 'customer_name'),
                            'external_location_id' => data_get($row, 'location_id'),
                            'organization_id' => data_get($row, 'organization_id'),
                            'total' => data_get($row, 'total') ?? 0,
                            'total_item' => data_get($row, 'total_item') ?? 0,
                            'external_created_at' => data_get($row, 'created_at'),
                            'external_updated_at' => data_get($row, 'updated_at'),
                            'raw_item' => data_get($row, 'raw_item'),
                            'custom_field' => data_get($row, 'custom_field'),
                            'raw_payload' => $row,
                        ]
                    );

                    foreach (data_get($row, 'item', []) as $item) {
                        OrderDetail::updateOrCreate(
                            [
                                'order_id' => $order->id,
                                'code' => data_get($item, 'code'),
                                'serial_number' => data_get($item, 'serial_number'),
                            ],
                            [
                                'qty' => data_get($item, 'qty') ?? 0,
                                'qty_process' => data_get($item, 'qty_process') ?? 0,
                                'rack' => data_get($item, 'rack'),
                                'rack_source' => data_get($item, 'rack_source'),
                                'external_location_id' => data_get($item, 'location_id'),
                                'external_location_id_source' => data_get($item, 'location_id_source'),
                                'ref_number_outbound' => data_get($item, 'refNumberOutbound'),
                                'status' => data_get($item, 'status'),
                                'raw_payload' => $item,
                            ]
                        );
                    }
                }
            });

            return back()->with('success', 'Order sync completed.');

        } catch (\Throwable $e) {
            Log::error($e);
            return back()->with('failed', $e->getMessage());
        }
    }
}
