<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\ApiEndpoint;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderDetail;

class SyncOrderItemsToday extends Command
{
    protected $signature = 'order-items:sync {--location_external_id=}';
    protected $description = 'Sync Orders (Inbound & Outbound) untuk semua Location active, data hari ini saja';

    public function handle(): int
    {
        set_time_limit(300);

        $endpoint = ApiEndpoint::where('code', 'IWS_Get_Orders')->first();
        if (!$endpoint) {
            $this->error('API Endpoint IWS_Get_Orders tidak ditemukan.');
            return self::FAILURE;
        }

        $decode = fn ($v) => is_array($v) ? $v : (json_decode($v, true) ?? []);

        $url = rtrim($endpoint->base_url, '/') . '/' . ltrim($endpoint->path, '/');

        $headers = $decode($endpoint->headers);
        if ($endpoint->auth_type === 'api_key') {
            $headers[$endpoint->auth_key] = $endpoint->auth_value;
        }

        // 📅 HARI INI SAJA
        $startDate = now()->startOfDay()->format('Y-m-d H:i:s');
        $endDate   = now()->endOfDay()->format('Y-m-d H:i:s');

        $baseParams = array_filter(array_merge(
            $decode($endpoint->params),
            [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'limit'      => 100,
            ]
        ), fn ($v) => $v !== null);

        $onlyLocExt = $this->option('location_external_id');

        $locations = Location::where('active', 1)
            ->when($onlyLocExt, fn ($q) => $q->where('external_id', $onlyLocExt))
            ->orderBy('display_name')
            ->get(['external_id', 'display_name']);

        if ($locations->isEmpty()) {
            $this->warn('Tidak ada location active.');
            return self::SUCCESS;
        }

        $types = ['inbound', 'outbound'];
        $totalInserted = 0;

        foreach ($locations as $loc) {
            if (!$loc->external_id) {
                $this->warn("Skip {$loc->display_name} (external_id null)");
                continue;
            }

            foreach ($types as $type) {
                $params = $baseParams;
                $params['location_id'] = $loc->external_id;
                $params['type'] = $type;

                $this->info("Sync {$type} | {$loc->display_name}");

                try {
                    $response = Http::withHeaders($headers)
                        ->timeout(60)
                        ->retry(2, 500)
                        ->get($url, $params);

                    if (!$response->successful()) {
                        $this->error("HTTP {$response->status()} | {$response->body()}");
                        continue;
                    }

                    $orders = data_get($response->json(), 'data', []);
                    if (empty($orders)) {
                        $this->info("No data.");
                        continue;
                    }

                    DB::transaction(function () use ($orders, &$totalInserted) {

                        foreach ($orders as $row) {
                            $externalId = data_get($row, '_id');

                            // 🧠 kalau order sudah ada → SKIP (no update)
                            if (Order::where('external_id', $externalId)->exists()) {
                                continue;
                            }

                            $order = Order::create([
                                'external_id' => $externalId,
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
                            ]);

                            foreach (data_get($row, 'item', []) as $item) {
                                OrderDetail::create([
                                    'order_id' => $order->id,
                                    'code' => data_get($item, 'code'),
                                    'serial_number' => data_get($item, 'serial_number'),
                                    'qty' => data_get($item, 'qty') ?? 0,
                                    'qty_process' => data_get($item, 'qty_process') ?? 0,
                                    'rack' => data_get($item, 'rack'),
                                    'rack_source' => data_get($item, 'rack_source'),
                                    'external_location_id' => data_get($item, 'location_id'),
                                    'external_location_id_source' => data_get($item, 'location_id_source'),
                                    'ref_number_outbound' => data_get($item, 'refNumberOutbound'),
                                    'status' => data_get($item, 'status'),
                                    'raw_payload' => $item,
                                ]);
                            }

                            $totalInserted++;
                        }

                    });

                } catch (\Throwable $e) {
                    Log::error($e);
                    $this->error($e->getMessage());
                }
            }
        }

        $this->info("DONE. Total Inserted Orders: {$totalInserted}");
        return self::SUCCESS;
    }
}
