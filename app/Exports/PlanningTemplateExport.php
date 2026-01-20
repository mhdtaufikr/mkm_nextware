<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PlanningTemplateExport implements WithHeadings, FromArray, WithEvents, ShouldAutoSize
{
    protected $locationCode;
    protected $type;
    protected $month;
    protected $dates = [];

    public function __construct($locationCode, $type, $month)
    {
        $this->locationCode = $locationCode;
        $this->type = $type;
        $this->month = $month;

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $this->dates[] = $cursor->copy();
            $cursor->addDay();
        }
    }

    public function headings(): array
    {
        $headings = ['Group No', 'Cutting Center', 'Destination', 'Type'];

        foreach ($this->dates as $date) {
            $headings[] = $date->format('d');
        }

        return $headings;
    }

    public function array(): array
    {
        $data = [];

        $cuttingCenters = $this->getCuttingCenters();

        foreach ($cuttingCenters as $cc) {
            $codes = $this->getCodesForCuttingCenter($cc);

            foreach ($codes as $code) {
                $row = [
                    $code,
                    $cc,
                    $this->locationCode,
                    $this->type,
                ];

                foreach ($this->dates as $date) {
                    $row[] = '';
                }

                $data[] = $row;
            }
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastColIndex = 4 + count($this->dates);
                $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex);

                $event->sheet->getDelegate()->getStyle('A1:' . $lastColLetter . '1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4']
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);

                $event->sheet->getDelegate()->freezePane('A2');
            },
        ];
    }

    protected function getCuttingCenters()
    {
        if ($this->type === 'inbound') {
            $subquery = DB::table('order_details as od')
                ->join('orders as o', 'o.id', '=', 'od.order_id')
                ->join('locations as loc', 'loc.external_id', '=', 'o.external_location_id')
                ->where('loc.location_code', $this->locationCode)
                ->whereRaw('LOWER(o.type) = ?', ['inbound'])
                ->select(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, "$.product.custom_field.cutting_center")) as cutting_center'))
                ->distinct();

            return DB::table(DB::raw("({$subquery->toSql()}) as centers"))
                ->mergeBindings($subquery)
                ->whereNotNull('cutting_center')
                ->where('cutting_center', '<>', '')
                ->pluck('cutting_center');
        } else {
            $subquery = DB::table('order_details as od')
                ->join('orders as o', 'o.id', '=', 'od.order_id')
                ->join('locations as loc', 'loc.external_id', '=', 'o.external_location_id')
                ->where('loc.location_code', $this->locationCode)
                ->whereRaw('LOWER(o.type) = ?', ['outbound'])
                ->select(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, "$.rack")) as rack'))
                ->distinct();

            return DB::table(DB::raw("({$subquery->toSql()}) as racks"))
                ->mergeBindings($subquery)
                ->whereNotNull('rack')
                ->where('rack', '<>', '')
                ->pluck('rack');
        }
    }

    protected function getCodesForCuttingCenter($cc)
    {
        if ($this->type === 'inbound') {
            return DB::table('order_details as od')
                ->join('orders as o', 'o.id', '=', 'od.order_id')
                ->join('locations as loc', 'loc.external_id', '=', 'o.external_location_id')
                ->where('loc.location_code', $this->locationCode)
                ->whereRaw('LOWER(o.type) = ?', ['inbound'])
                ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, "$.product.custom_field.cutting_center")) = ?', [$cc])
                ->distinct()
                ->pluck('od.code');
        } else {
            return DB::table('order_details as od')
                ->join('orders as o', 'o.id', '=', 'od.order_id')
                ->join('locations as loc', 'loc.external_id', '=', 'o.external_location_id')
                ->where('loc.location_code', $this->locationCode)
                ->whereRaw('LOWER(o.type) = ?', ['outbound'])
                ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(od.raw_payload, "$.rack")) = ?', [$cc])
                ->distinct()
                ->pluck('od.code');
        }
    }
}
