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
    protected array $stats = [
        'inserted' => 0,
        'updated'  => 0,
        'skipped'  => 0,
    ];

    protected string $month; // format Y-m

    public function __construct(string $month)
    {
        $this->month = $month;
    }

    public function collection(Collection $rows)
    {
        $baseMonth = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();

        DB::beginTransaction();

        try {
            foreach ($rows as $rowIndex => $row) {

                // =========================
                // 1️⃣ REQUIRED FIELDS
                // =========================
                $code          = trim((string) ($row['group_no'] ?? ''));
                $cuttingCenter = trim((string) ($row['cutting_center'] ?? ''));
                $destination   = trim((string) ($row['destination'] ?? ''));
                $type           = trim((string) ($row['type'] ?? ''));

                if (!$code || !$cuttingCenter || !$destination || !$type) {
                    $this->stats['skipped']++;
                    continue;
                }

                // =========================
                // 2️⃣ LOOP DATE COLUMNS
                // =========================
                foreach ($row as $header => $qtyRaw) {

                    // skip metadata columns
                    if (in_array($header, ['group_no', 'cutting_center', 'destination', 'type'])) {
                        continue;
                    }

                    // header HARUS numeric day (1–31)
                    if (!is_numeric($header)) {
                        continue;
                    }

                    $day = (int) $header;
                    if ($day < 1 || $day > 31) {
                        continue;
                    }

                    // qty boleh 0 (VALID UPDATE)
                    if ($qtyRaw === null || $qtyRaw === '') {
                        continue;
                    }

                    $qty = (int) $qtyRaw;

                    // =========================
                    // 3️⃣ BUILD PLAN DATE
                    // =========================
                    try {
                        $planDate = $baseMonth->copy()->day($day);
                    } catch (\Throwable $e) {
                        $this->stats['skipped']++;
                        continue;
                    }

                    // =========================
                    // 4️⃣ UPSERT (0 = VALID)
                    // =========================
                    $exists = Planning::where([
                        'location_code'  => $destination,
                        'cutting_center' => $cuttingCenter,
                        'code'            => $code,
                        'type'            => $type,
                        'plan_date'       => $planDate->toDateString(),
                    ])->exists();

                    Planning::updateOrCreate(
                        [
                            'location_code'  => $destination,
                            'cutting_center' => $cuttingCenter,
                            'code'            => $code,
                            'type'            => $type,
                            'plan_date'       => $planDate->toDateString(),
                        ],
                        [
                            'qty' => $qty, // ← 0 AKAN DISIMPAN
                        ]
                    );

                    $exists
                        ? $this->stats['updated']++
                        : $this->stats['inserted']++;
                }
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}
