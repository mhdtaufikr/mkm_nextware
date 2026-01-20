<?php

namespace App\Imports;

use App\Models\Planning;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PlanningImport implements ToCollection, WithHeadingRow
{
    protected $stats = [
        'inserted' => 0,
        'updated' => 0,
        'skipped' => 0,
    ];

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                // Get required fields
                $code = $row['group_no'] ?? null;
                $cuttingCenter = $row['cutting_center'] ?? null;
                $destination = $row['destination'] ?? null;
                $type = $row['type'] ?? null;

                // Validate required fields
                if (empty($code) || empty($cuttingCenter) || empty($destination) || empty($type)) {
                    $this->stats['skipped']++;
                    continue;
                }

                // Get all column keys (untuk ambil tanggal)
                $allKeys = $row->keys()->toArray();

                // Process each date column (skip first 4 columns)
                $dayCounter = 1;
                foreach ($allKeys as $key) {
                    // Skip metadata columns
                    if (in_array($key, ['group_no', 'cutting_center', 'destination', 'type'])) {
                        continue;
                    }

                    // Get qty value
                    $qty = $row[$key] ?? null;

                    // Skip empty cells
                    if ($qty === null || $qty === '' || $qty === 0) {
                        $dayCounter++;
                        continue;
                    }

                    $qty = (int) $qty;

                    // Skip if qty is 0 or negative
                    if ($qty <= 0) {
                        $dayCounter++;
                        continue;
                    }

                    // Build plan_date from current year-month and day counter
                    try {
                        // Ambil year-month dari sekarang atau bisa dari parameter
                        $yearMonth = date('Y-m');
                        $planDate = Carbon::createFromFormat('Y-m-d',
                            $yearMonth . '-' . str_pad($dayCounter, 2, '0', STR_PAD_LEFT)
                        );
                    } catch (\Exception $e) {
                        $dayCounter++;
                        continue;
                    }

                    // Check if record exists
                    $exists = Planning::where([
                        'location_code' => $destination,
                        'cutting_center' => $cuttingCenter,
                        'code' => $code,
                        'type' => $type,
                        'plan_date' => $planDate->toDateString(),
                    ])->exists();

                    // Insert or update
                    Planning::updateOrCreate(
                        [
                            'location_code' => $destination,
                            'cutting_center' => $cuttingCenter,
                            'code' => $code,
                            'type' => $type,
                            'plan_date' => $planDate->toDateString(),
                        ],
                        [
                            'qty' => $qty,
                        ]
                    );

                    // Update stats
                    if ($exists) {
                        $this->stats['updated']++;
                    } else {
                        $this->stats['inserted']++;
                    }

                    $dayCounter++;
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getStats()
    {
        return $this->stats;
    }
}
