<?php

namespace App\Http\Controllers;

use App\Models\ApiEndpoint;
use App\Models\Inventory;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InventoryController extends Controller
{
    public function index()
    {
        // dropdown location (internal id + external_id)
        $locations = Location::orderBy('display_name')->get();

        // list inventory (bisa kamu filter by location nanti)
        $inventories = Inventory::orderBy('id', 'desc')->limit(2000)->get();

        return view('inventories.index', compact('locations', 'inventories'));
    }

    public function sync(Request $request)
    {
        $request->validate([
            'location_external_id' => ['required','string'],
        ]);

        $endpoint = ApiEndpoint::where('code', 'IWS_Get_Inventory')->first();

        if (!$endpoint) {
            return redirect()->back()->with('failed', 'API Endpoint code IWS_Get_Inventory tidak ditemukan di master.');
        }

        $url = rtrim($endpoint->base_url, '/') . '/' . ltrim($endpoint->path, '/');

        // headers + auth api key
        $headers = is_array($endpoint->headers) ? $endpoint->headers : [];
        if ($endpoint->auth_type === 'api_key' && $endpoint->auth_key && $endpoint->auth_value) {
            $headers[$endpoint->auth_key] = $endpoint->auth_value;
        }

        // params default dari master (kalau ada)
        $params = is_array($endpoint->params) ? $endpoint->params : [];

        // override runtime sesuai requirement kamu
        $params['location_id'] = $request->location_external_id;
        $params['limit'] = -1;
        $params['page'] = 1;
        $params['show_item'] = false;

        // optional: kosongin biar rapih
        $params = array_filter($params, fn($v) => $v !== null);

        try {
            $response = Http::withHeaders($headers)
                ->timeout(60)
                ->get($url, $params);

            if (!$response->successful()) {
                return redirect()->back()->with('failed', 'Hit endpoint gagal. HTTP '.$response->status().' | '.$response->body());
            }

            $json = $response->json();
            $rows = data_get($json, 'collection.data', []);
            if (!is_array($rows)) $rows = [];

            $count = 0;

            foreach ($rows as $r) {

                $externalId = $r['_id'] ?? null;
                $locId      = $r['location_id'] ?? null;
                $code       = $r['code'] ?? null;

                // ✅ Validasi 3 field wajib ada
                if (!$externalId || !$locId || !$code) {
                    continue;
                }

                // payload data yang mau disimpan
                $payload = [
                    'external_id' => $externalId,              // simpan _id terbaru
                    'external_location_id' => $locId,
                    'organization_id' => $r['organization_id'] ?? null,
                    'location_code' => $r['location_code'] ?? null,
                    'code' => $code,
                    'name' => $r['name'] ?? null,

                    // sanitize numeric
                    'qty' => ($r['qty'] ?? null) === '' ? null : (int)($r['qty'] ?? 0),
                    'qty_goods' => ($r['qty_goods'] ?? null) === '' ? null : (int)($r['qty_goods'] ?? 0),
                    'qty_available' => ($r['qty_available'] ?? null) === '' ? null : (int)($r['qty_available'] ?? 0),
                    'qty_incoming' => ($r['qty_incoming'] ?? null) === '' ? null : (int)($r['qty_incoming'] ?? 0),
                    'qty_outgoing' => ($r['qty_outgoing'] ?? null) === '' ? null : (int)($r['qty_outgoing'] ?? 0),

                    'stock_status' => $r['stock_status'] ?? null,
                    'created_by' => $r['created_by'] ?? null,

                    'api_created_at' => $r['created_at'] ?? null,
                    'api_updated_at' => $r['updated_at'] ?? null,
                    'last_calculated' => $r['last_calculated'] ?? null,

                    'rack_type' => $r['rack_type'] ?? null,

                    'location_payload' => $r['location'] ?? null,
                    'product_payload' => $r['product'] ?? null,
                    'custom_field' => $r['custom_field'] ?? null,
                    'raw_payload' => $r,
                ];

                // 1) cari berdasarkan kunci bisnis: location + code
                $inv = Inventory::where('external_location_id', $locId)
                    ->where('code', $code)
                    ->first();

                if ($inv) {
                    // ✅ update record existing (walau _id berubah)
                    $inv->update($payload);
                    $count++;
                    continue;
                }

                // 2) fallback: kalau belum ketemu, cek apakah _id sudah ada di record lain
                $invByExternal = Inventory::where('external_id', $externalId)->first();
                if ($invByExternal) {
                    $invByExternal->update($payload);
                    $count++;
                    continue;
                }

                // 3) create baru
                Inventory::create($payload);
                $count++;
            }


            return redirect()->back()->with('success', "Sync Inventory sukses. Updated/Inserted: {$count} data.");

        } catch (\Throwable $e) {
            return redirect()->back()->with('failed', 'Exception: '.$e->getMessage());
        }
    }
}
