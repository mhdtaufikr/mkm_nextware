<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\ApiEndpoint;
use App\Models\Location;
use App\Models\Inventory;

class SyncInventoryAllLocations extends Command
{
    protected $signature = 'inventory:sync {--location_external_id=}';
    protected $description = 'Sync Inventory dari Mile API untuk semua Location yang active=1 (atau satu lokasi tertentu)';

    public function handle(): int
    {
        $endpoint = ApiEndpoint::where('code', 'IWS_Get_Inventory')->first();
        if (!$endpoint) {
            $this->error('API Endpoint code IWS_Get_Inventory tidak ditemukan di master.');
            return self::FAILURE;
        }

        $url = rtrim($endpoint->base_url, '/') . '/' . ltrim($endpoint->path, '/');

        $headers = is_array($endpoint->headers) ? $endpoint->headers : [];
        if ($endpoint->auth_type === 'api_key' && $endpoint->auth_key && $endpoint->auth_value) {
            $headers[$endpoint->auth_key] = $endpoint->auth_value;
        }

        $baseParams = is_array($endpoint->params) ? $endpoint->params : [];
        $baseParams['limit'] = -1;
        $baseParams['page'] = 1;
        $baseParams['show_item'] = false;

        // Kalau mau sync hanya 1 lokasi
        $onlyLocExt = $this->option('location_external_id');

        $locationsQuery = Location::query()
            ->where('active', 1)
            ->orderBy('display_name');

        if ($onlyLocExt) {
            $locationsQuery->where('external_id', $onlyLocExt);
        }

        $locations = $locationsQuery->get(['id','external_id','display_name']);

        if ($locations->isEmpty()) {
            $this->warn('Tidak ada lokasi active yang ditemukan.');
            return self::SUCCESS;
        }

        $grandCount = 0;

        foreach ($locations as $loc) {
            if (!$loc->external_id) {
                $this->warn("Skip: {$loc->display_name} (external_id null)");
                continue;
            }

            $params = $baseParams;
            $params['location_id'] = $loc->external_id;
            $params = array_filter($params, fn($v) => $v !== null);

            $this->info("Sync inventory: {$loc->display_name} | external_id={$loc->external_id}");

            try {
                $response = Http::withHeaders($headers)
                    ->timeout(60)
                    ->get($url, $params);

                if (!$response->successful()) {
                    $this->error("Gagal: HTTP {$response->status()} | {$response->body()}");
                    continue;
                }

                $json = $response->json();
                $rows = data_get($json, 'collection.data', []);
                if (!is_array($rows)) $rows = [];

                $count = 0;

                foreach ($rows as $r) {
                    $externalId = $r['_id'] ?? null;
                    $locId      = $r['location_id'] ?? null;
                    $code       = $r['code'] ?? null;

                    if (!$externalId || !$locId || !$code) {
                        continue;
                    }

                    $payload = [
                        'external_id' => $externalId,
                        'external_location_id' => $locId,
                        'organization_id' => $r['organization_id'] ?? null,
                        'location_code' => $r['location_code'] ?? null,
                        'code' => $code,
                        'name' => $r['name'] ?? null,

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

                    $inv = Inventory::where('external_location_id', $locId)
                        ->where('code', $code)
                        ->first();

                    if ($inv) {
                        $inv->update($payload);
                        $count++;
                        continue;
                    }

                    $invByExternal = Inventory::where('external_id', $externalId)->first();
                    if ($invByExternal) {
                        $invByExternal->update($payload);
                        $count++;
                        continue;
                    }

                    Inventory::create($payload);
                    $count++;
                }

                $grandCount += $count;
                $this->info("OK: {$loc->display_name} => Updated/Inserted: {$count}");

            } catch (\Throwable $e) {
                $this->error("Exception: {$e->getMessage()}");
                continue;
            }
        }

        $this->info("DONE. Total Updated/Inserted: {$grandCount}");
        return self::SUCCESS;
    }
}
