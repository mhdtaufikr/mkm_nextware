<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\ApiEndpoint;
use App\Models\Location;
use App\Models\InventoryItem;

class SyncInventoryItemAllLocations extends Command
{
    protected $signature = 'inventory-item:sync {--date=} {--location_external_id=}';
    protected $description = 'Sync InventoryItem untuk semua Location active=1, dengan start_date/end_date = hari ini (atau override date)';

    public function handle(): int
    {
        $endpoint = ApiEndpoint::where('code', 'IWS_Get_InventoryItem')->first();
        if (!$endpoint) {
            $this->error('API Endpoint code IWS_Get_InventoryItem tidak ditemukan di master.');
            return self::FAILURE;
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

        // tanggal: default hari ini (mengikuti timezone app)
        $dateOpt = $this->option('date');
        $date = $dateOpt
            ? Carbon::parse($dateOpt)->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');

        $baseParams = array_merge($decode($endpoint->params), [
            'limit'          => 10,   // page 1 only (sesuai kode awal kamu)
            'page'           => 1,
            'serial_number'  => '',
            'rack'           => '',
            'rack_type'      => '',
            'start_date'     => $date,
            'end_date'       => $date,
        ]);

        // buang null saja (jangan buang empty string)
        $baseParams = array_filter($baseParams, fn($v) => $v !== null);

        // optional speed-up
        InventoryItem::unsetEventDispatcher();

        $onlyLocExt = $this->option('location_external_id');

        $locationsQuery = Location::query()
            ->where('active', 1)
            ->orderBy('display_name');

        if ($onlyLocExt) {
            $locationsQuery->where('external_id', $onlyLocExt);
        }

        $locations = $locationsQuery->get(['id', 'external_id', 'display_name']);

        if ($locations->isEmpty()) {
            $this->warn('Tidak ada lokasi active yang ditemukan.');
            return self::SUCCESS;
        }

        $grandSaved = 0;

        foreach ($locations as $loc) {
            if (!$loc->external_id) {
                $this->warn("Skip: {$loc->display_name} (external_id null)");
                continue;
            }

            $params = $baseParams;
            $params['location_id'] = $loc->external_id;

            Log::info('SCHEDULER SYNC InventoryItem', [
                'location' => $loc->display_name,
                'location_external_id' => $loc->external_id,
                'date' => $date,
                'url' => $url,
                'method' => $method,
                'params' => $params,
            ]);

            $this->info("Sync InventoryItem: {$loc->display_name} | external_id={$loc->external_id} | date={$date}");

            try {
                $response = Http::withHeaders($headers)
                    ->timeout(60)
                    ->retry(2, 500)
                    ->send($method, $url, ['query' => $params]);

                if (!$response->successful()) {
                    $this->error("Gagal: HTTP {$response->status()} | {$response->body()}");
                    continue;
                }

                $items = data_get($response->json(), 'data', []);
                if (!is_array($items) || count($items) === 0) {
                    $this->info("OK: {$loc->display_name} => Upsert: 0");
                    continue;
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

                        /**
                         * custom_field yang DISIMPAN ke kolom inventory_items.custom_field:
                         * PRIORITAS = product.custom_field (yang berisi part_no, cutting_center, dll)
                         * fallback  = custom_field_product
                         * terakhir  = root custom_field (misal delivery_date) jika memang tidak ada yang lain
                         */
                        $customField = data_get($r, 'product.custom_field');
                        if ($customField === null || (is_array($customField) && empty($customField))) {
                            $customField = data_get($r, 'custom_field_product');
                        }
                        if ($customField === null || (is_array($customField) && empty($customField))) {
                            $customField = data_get($r, 'custom_field');
                        }

                        // --- normalize location_payload (kadang [] di response) ---
                        $locationPayload = data_get($r, 'location');
                        if (is_array($locationPayload) && empty($locationPayload)) {
                            $locationPayload = null;
                        }

                        // bikin payload minimal supaya tetap kesimpan walau API kirim []
                        if ($locationPayload === null) {
                            $locationPayload = [
                                '_id'  => data_get($r, 'location_id'),
                                'code' => data_get($r, 'location_code'),
                                'name' => data_get($r, 'location.name'),
                            ];
                        }

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
                            'location_payload' => $locationPayload,
                            'custom_field' => $customField,
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
                            // fallback kalau SN kosong
                            $key['external_id'] = $externalId;
                        }

                        InventoryItem::updateOrCreate($key, $payload);
                        $saved++;
                    }
                });

                $grandSaved += $saved;
                $this->info("OK: {$loc->display_name} => Upsert: {$saved}");

            } catch (\Throwable $e) {
                $this->error("Exception: {$e->getMessage()}");
                continue;
            }
        }

        $this->info("DONE. Total Upsert: {$grandSaved}");
        return self::SUCCESS;
    }
}
