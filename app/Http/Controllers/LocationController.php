<?php

namespace App\Http\Controllers;

use App\Models\ApiEndpoint;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::orderBy('id', 'desc')->get();
        return view('locations.index', compact('locations'));
    }

    public function sync(Request $request)
    {
        $endpoint = ApiEndpoint::where('code', 'IWS_Get_Location')->first();

        if (!$endpoint) {
            return redirect()->back()->with('failed', 'API Endpoint code IWS_Get_Location tidak ditemukan di master.');
        }

        $url = rtrim($endpoint->base_url, '/') . '/' . ltrim($endpoint->path, '/');

        // ===== HEADERS =====
        $headers = is_array($endpoint->headers) ? $endpoint->headers : [];
        if ($endpoint->auth_type === 'api_key' && $endpoint->auth_key && $endpoint->auth_value) {
            $headers[$endpoint->auth_key] = $endpoint->auth_value; // x-api-key
        }

        // ===== PARAMS (INI YANG PENTING) =====
        $params = [];

        if (is_array($endpoint->params)) {
            // buang null / empty param
            $params = array_filter(
                $endpoint->params,
                fn ($v) => $v !== null && $v !== ''
            );
        }

        // contoh override runtime (kalau nanti mau)
        // $params['page'] = 1;

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->get($url, $params); // 👈 limit=-1 masuk di sini

            if (!$response->successful()) {
                return redirect()->back()->with('failed', 'Hit endpoint gagal. HTTP ' . $response->status());
            }

            $json = $response->json();

            $rows = data_get($json, 'location.list.data', []);
            if (!is_array($rows)) $rows = [];

            $count = 0;

            foreach ($rows as $r) {
                $externalId = $r['_id'] ?? null;
                if (!$externalId) continue;

                Location::updateOrCreate(
                    ['external_id' => $externalId],
                    [
                        'name' => $r['name'] ?? null,
                        'display_name' => $r['display_name'] ?? null,
                        'description' => $r['description'] ?? null,
                        'lat' => $r['lat'] ?? null,
                        'lng' => $r['lng'] ?? null,
                        'address' => $r['address'] ?? null,
                        'phone' => $r['phone'] ?? null,
                        'location_type' => $r['location_type'] ?? null,
                        'location_code' => $r['location_code'] ?? null,
                        'is_default' => (bool)($r['is_default'] ?? 0),
                       'zip_code' => (isset($r['zip_code']) && $r['zip_code'] !== '' && $r['zip_code'] !== null)
                        ? (int) $r['zip_code']
                        : null,
                        'timezone' => $r['timezone'] ?? null,
                        'organization_id' => $r['organization_id'] ?? null,
                        'status' => $r['status'] ?? null,
                       'amount_balance' => (isset($r['amount_balance']) && $r['amount_balance'] !== '' && $r['amount_balance'] !== null)
                        ? (int) $r['amount_balance']
                        : null,
                    'total_user' => (isset($r['total_user']) && $r['total_user'] !== '' && $r['total_user'] !== null)
                        ? (int) $r['total_user']
                        : null,
                        'is_enable_wallet' => (bool)($r['is_enable_wallet'] ?? 0),
                        'raw_payload' => $r,
                    ]
                );

                $count++;
            }

            return redirect()->back()->with(
                'success',
                "Sync Location sukses (limit = -1). Updated/Inserted: {$count} data."
            );

        } catch (\Throwable $e) {
            return redirect()->back()->with('failed', 'Exception: ' . $e->getMessage());
        }
    }

}
