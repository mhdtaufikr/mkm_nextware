<?php

namespace App\Http\Controllers;

use App\Models\ApiEndpoint;
use App\Models\InventoryItem;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Inventory;

class InventoryItemController extends Controller
{
    public function index()
    {
        $locations = Location::where('active', 1)->orderBy('location_code')->get();

        // tampilkan latest 2000 biar ga berat (opsional)
        $items = InventoryItem::orderBy('id', 'desc')->limit(2000)->get();

        return view('inventory_items.index', compact('locations', 'items'));
    }



    public function sync(Request $request)
    {
        $request->validate([
            'location_external_id' => ['required', 'string'],
        ]);

        $endpoint = ApiEndpoint::where('code', 'IWS_Get_InventoryItem')->first();
        if (!$endpoint) {
            return back()->with('failed', 'API Endpoint code IWS_Get_InventoryItem tidak ditemukan di master.');
        }

        $decode = function ($value): array {
            if (empty($value)) return [];
            if (is_array($value)) return $value;
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        };

        $url    = rtrim($endpoint->base_url, '/') . '/' . ltrim($endpoint->path, '/');
        $method = strtoupper($endpoint->method ?? 'GET');

        $headers = $decode($endpoint->headers);
        if ($endpoint->auth_type === 'api_key' && $endpoint->auth_key && $endpoint->auth_value) {
            $headers[$endpoint->auth_key] = $endpoint->auth_value;
        }

        $locationId = $request->location_external_id;

        $params = array_merge($decode($endpoint->params), [
            'location_id'    => $locationId,
            'limit'          => 10,
            'page'           => 1,
            'serial_number'  => '',
            'rack'           => '',
            'rack_type'      => '',
            'start_date'     => '',
            'end_date'       => '',
        ]);

        // buang null saja (jangan buang empty string)
        $params = array_filter($params, fn($v) => $v !== null);

        Log::info('SYNC InventoryItem', [
            'url' => $url,
            'method' => $method,
            'params' => $params,
        ]);

        // optional speed-up
        InventoryItem::unsetEventDispatcher();

        try {
            $response = Http::withHeaders($headers)
                ->timeout(60)
                ->retry(2, 500)
                ->send($method, $url, ['query' => $params]);

            if (!$response->successful()) {
                return back()->with('failed', 'Hit endpoint gagal. HTTP '.$response->status().' | '.$response->body());
            }

            $items = data_get($response->json(), 'data', []);
            if (!is_array($items) || count($items) === 0) {
                return back()->with('success', 'Sync selesai. Upsert: 0 data.');
            }

            $saved = 0;

            DB::transaction(function () use ($items, &$saved) {
                foreach ($items as $r) {
                    $externalId = data_get($r, '_id');
                    $extInvId   = data_get($r, 'inventory_id');
                    $extLocId   = data_get($r, 'location_id');
                    $code       = data_get($r, 'code');
                    $sn         = data_get($r, 'serial_number');

                    // minimal fields
                    if (!$extLocId || !$code) continue;

                    $payload = [
                        'external_id' => $externalId,
                        'external_inventory_id' => $extInvId,
                        'external_location_id' => $extLocId,
                        'code' => $code,
                        'serial_number' => $sn,

                        'rack' => data_get($r, 'rack'),
                        'rack_type' => data_get($r, 'rack_type'),
                        'status' => data_get($r, 'status'),
                        'qty' => (int) (data_get($r, 'qty') ?? 0),

                        'receive_date' => data_get($r, 'receive_date'),
                        'location_code' => data_get($r, 'location_code'),
                        'product_name' => data_get($r, 'product_name') ?? data_get($r, 'product.name'),

                        'product_payload' => data_get($r, 'product'),
                        'location_payload' => data_get($r, 'location'),
                        'custom_field' => data_get($r, 'custom_field'),
                        'raw_payload' => $r,
                    ];

                    // key anti duplikat
                    $key = [
                        'external_location_id' => $extLocId,
                        'code' => $code,
                    ];

                    if (!empty($sn)) {
                        $key['serial_number'] = $sn;
                    } else {
                        $key['external_id'] = $externalId;
                    }

                    InventoryItem::updateOrCreate($key, $payload);
                    $saved++;
                }
            });

            return back()->with('success', "Sync selesai (page 1 only). Upsert: {$saved} data.");

        } catch (\Throwable $e) {
            return back()->with('failed', 'Exception: '.$e->getMessage());
        }
    }


    // optional
    public function show($id) { return response()->json(['data' => InventoryItem::findOrFail($id)]); }
    public function store(Request $request) { abort(404); }
    public function update(Request $request, $id) { abort(404); }
    public function destroy($id) { abort(404); }
}
