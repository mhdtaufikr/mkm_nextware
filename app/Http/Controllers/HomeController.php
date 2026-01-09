<?php

namespace App\Http\Controllers;
use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\AuditDetail;
use App\Models\AuditHeader;
use App\Models\AuditWorkflowLog;
use App\Models\AuditWorkflowLogDetail;

class HomeController extends Controller
{

    public function index()
    {

        return view('home.index');
    }



    private function latestAuditId(): ?int
{
    return AuditHeader::latest('created_at')->value('id');
}

private function resolveAuditId(?int $auditHeaderId): ?int
{
    return $auditHeaderId ?: $this->latestAuditId();
}


private function calculateSummary($auditHeaderId = null)
{
    // default ke audit terbaru kalau null/kosong
    $auditHeaderId = $this->resolveAuditId($auditHeaderId);

    $query = AuditDetail::with('workflowLogs.workflowLogDetails')
              ->where('audit_header_id', $auditHeaderId);

    $allDetails = $query->get();

    $openCount = $progressCount = $approvalCount = $verificationCount = $closeCount = 0;

    foreach ($allDetails as $detail) {
        $maxLvl = $detail->workflowLogs
            ->flatMap(fn($wl) => $wl->workflowLogDetails)
            ->max(fn($ld) => (int) $ld->level);

        if ($maxLvl === null || $maxLvl < 1)       $openCount++;
        elseif ($maxLvl == 1)                      $progressCount++;
        elseif ($maxLvl == 2)                      $approvalCount++;
        elseif ($maxLvl == 3)                      $verificationCount++;
        elseif ($maxLvl >= 4)                      $closeCount++;
    }

    $total = $allDetails->count();

    return [
        'total' => $total,
        'openCount' => $openCount,
        'openPercentage' => $total ? round(($openCount / $total) * 100, 1) : 0,
        'progressCount' => $progressCount,
        'progressPercentage' => $total ? round(($progressCount / $total) * 100, 1) : 0,
        'approvalCount' => $approvalCount,
        'approvalPercentage' => $total ? round(($approvalCount / $total) * 100, 1) : 0,
        'verificationCount' => $verificationCount,
        'verificationPercentage' => $total ? round(($verificationCount / $total) * 100, 1) : 0,
        'closeCount' => $closeCount,
        'closePercentage' => $total ? round(($closeCount / $total) * 100, 1) : 0,
    ];
}



    public function summary(Request $request)
    {
        $auditId = $request->input('audit_id');
        $summary = $this->calculateSummary($auditId); // Panggil private function yg sudah kamu punya

        return response()->json($summary);
    }

    /**
     * Helper function untuk menyiapkan data chart.
     */
    private function prepareChartData($detailsByDept)
    {
        $deptMap = [ /* ... mapping departemen Anda ... */ ];
        $openData = ['category' => 'Open'];
        $progressData = ['category' => 'Progress'];
        $approvalData = ['category' => 'Approval'];
        $verificationData = ['category' => 'Verification'];
        $closeData = ['category' => 'Closed'];

        foreach ($detailsByDept as $fullDept => $details) {
            if ($fullDept === 'Unassigned') continue;
            $key = strtoupper(trim($fullDept));
            $abbr = $deptMap[$key] ?? $fullDept;

            $openCount = 0; $progressCount = 0; $approvalCount = 0; $verificationCount = 0; $closeCount = 0;

            foreach ($details as $detail) {
                $maxLvl = $detail->workflowLogs->flatMap(fn($wl) => $wl->workflowLogDetails)->max(fn($ld) => (int)$ld->level);
                if ($maxLvl === null || $maxLvl < 1) $openCount++;
                elseif ($maxLvl == 1) $progressCount++;
                elseif ($maxLvl == 2) $approvalCount++;
                elseif ($maxLvl == 3) $verificationCount++;
                elseif ($maxLvl >= 4) $closeCount++;
            }

            $openData[$abbr] = $openCount;
            $progressData[$abbr] = $progressCount;
            $approvalData[$abbr] = $approvalCount;
            $verificationData[$abbr] = $verificationCount;
            $closeData[$abbr] = $closeCount;
        }

        return [$openData, $progressData, $approvalData, $verificationData, $closeData];
    }

    public function getChartData($auditHeaderId = null)
    {
        // pakai default latest kalau null/kosong
        $auditHeaderId = $this->resolveAuditId($auditHeaderId);

        $deptMap = [
            'QUALITY MANAGEMENT' => 'QM',
            'PROCUREMENT' => 'PROC',
            'STAMPING OPERATION' => 'SO',
            'ENGINE OPERATION' => 'EO',
            'HR & ADMINISTRATION' => 'HRA',
            'CONTROLLING' => 'CO',
            'PPC' => 'PPC',
            'MANAGEMENT REPRESENTATIVE' => 'MR',
            'MANUFACTURING ENGINEERING ENGINE' => 'MEE',
            'MANUFACTURING ENGINEERING STAMPING' => 'MES',
        ];

        $detailsQuery = AuditDetail::with('workflowLogs.workflowLogDetails')
            ->where('audit_header_id', $auditHeaderId);

        $detailsByDept = $detailsQuery->get()->groupBy(function($detail) {
            $lastLog = $detail->workflowLogs->sortByDesc('created_at')->first();
            return $lastLog ? $lastLog->department_assigned : 'Unassigned';
        });

        $openData         = ['category' => 'Open'];
        $progressData     = ['category' => 'Progress'];
        $approvalData     = ['category' => 'Approval'];
        $verificationData = ['category' => 'Verification'];
        $closeData        = ['category' => 'Closed'];

        foreach ($detailsByDept as $fullDept => $details) {
            if ($fullDept === 'Unassigned') continue;

            $key  = strtoupper(trim($fullDept));
            $abbr = $deptMap[$key] ?? $fullDept;

            $openCount = $progressCount = $approvalCount = $verificationCount = $closeCount = 0;

            foreach ($details as $detail) {
                $maxLvl = $detail->workflowLogs
                    ->flatMap(fn($wl) => $wl->workflowLogDetails)
                    ->max(fn($ld) => (int)$ld->level);

                if ($maxLvl === null || $maxLvl < 1) $openCount++;
                elseif ($maxLvl == 1)               $progressCount++;
                elseif ($maxLvl == 2)               $approvalCount++;
                elseif ($maxLvl == 3)               $verificationCount++;
                elseif ($maxLvl >= 4)               $closeCount++;
            }

            $openData[$abbr]         = $openCount;
            $progressData[$abbr]     = $progressCount;
            $approvalData[$abbr]     = $approvalCount;
            $verificationData[$abbr] = $verificationCount;
            $closeData[$abbr]        = $closeCount;
        }

        return response()->json([
            $openData, $progressData, $approvalData, $verificationData, $closeData
        ]);
    }


        public function summaryData($auditHeaderId = null)
        {
            $auditHeaderId = $this->resolveAuditId($auditHeaderId);
            // 0) mapping dept → singkatan (uppercase)
            $deptMap = [
                'QUALITY MANAGEMENT'                    => 'QM',
                'PROCUREMENT'                         => 'PROC',
                'STAMPING OPERATION'                    => 'SO',
                'ENGINE OPERATION'                      => 'EO',
                'HR & ADMINISTRATION'                   => 'HRA',
                'CONTROLLING'                           => 'CO',
                'PPC'                                   => 'PPC',
                'MANAGEMENT REPRESENTATIVE'             => 'MR',
                'MANUFACTURING ENGINEERING ENGINE'      => 'MEE',
                'MANUFACTURING ENGINEERING STAMPING'    => 'MES',
            ];

            // 1) query dasar
            $query = AuditWorkflowLog::with('workflowLogDetails','auditDetail');
            // 2) filter kalau ada ID dan valid
            if ($auditHeaderId && AuditHeader::where('id',$auditHeaderId)->exists()) {
                $query->whereHas('auditDetail', fn($q)=>
                    $q->where('audit_header_id',$auditHeaderId)
                );
            }
            // 3) ambil & groupBy
            $logs = $query->get()->groupBy('department_assigned');

            // 4) bangun data array per-dept
            $data = [];
            foreach($logs as $fullDept => $group){
                $key  = strtoupper(trim($fullDept));
                $abbr = $deptMap[$key] ?? $key;  // fallback ke uppercase nama aslinya
                // hitung close / open
                $closed = $group->filter(fn($log)=>
                    $log->workflowLogDetails->max('level') >= 4
                )->count();
                $opened = $group->count() - $closed;

                $data[] = [
                    'department' => $abbr,
                    'open'       => $opened,
                    'close'      => $closed,
                ];
            }

            return response()->json($data);
        }

        public function getCategorySummary($auditHeaderId = null)
        {
            $auditHeaderId = $this->resolveAuditId($auditHeaderId);
            // 1) mapping nama dept → singkatan (uppercase)
            $deptMap = [
                'QUALITY MANAGEMENT'                   => 'QM',
                'PROCUREMENT'                          => 'PROC',
                'STAMPING OPERATION'                   => 'SO',
                'ENGINE OPERATION'                     => 'EO',
                'HR & ADMINISTRATION'                  => 'HRA',
                'CONTROLLING'                          => 'CO',
                'PPC'                                  => 'PPC',
                'MANAGEMENT REPRESENTATIVE'            => 'MR',
                'MANUFACTURING ENGINEERING ENGINE'     => 'MEE',
                'MANUFACTURING ENGINEERING STAMPING'   => 'MES',
            ];

            // 2) ambil semua workflow log (dgn optional filter header),
            //    but only those that actually have a detail
            $query = AuditWorkflowLog::with('auditDetail')
                        ->whereHas('auditDetail');

            if ($auditHeaderId && AuditHeader::where('id', $auditHeaderId)->exists()) {
                $query->whereHas('auditDetail', fn($q) =>
                    $q->where('audit_header_id', $auditHeaderId)
                );
            }

            $logs = $query->get();

            // 3) inisiasi 3 kategori
            $cats = ['Major','Minor','OFI'];
            $result = [];
            foreach ($cats as $c) {
                $result[$c] = ['category' => $c];
            }

            // 4) group by dept, lalu hitung per kategori
            $byDept = $logs->groupBy('department_assigned');
            foreach ($byDept as $fullDept => $group) {
                $key  = strtoupper(trim($fullDept));
                $abbr = $deptMap[$key] ?? $key;

                foreach ($cats as $c) {
                    // guard for any missing auditDetail just in case
                    $count = $group->filter(fn($log) =>
                        $log->auditDetail
                        && strcasecmp($log->auditDetail->classification, $c) === 0
                    )->count();

                    $result[$c][$abbr] = $count;
                }
            }

            // 5) return array terurut: Major, Minor, OFI
            return response()->json([
                $result['Major'],
                $result['Minor'],
                $result['OFI'],
            ]);
        }

        public function getDeptClassificationSummary($auditHeaderId = null)
        {
            $auditHeaderId = $this->resolveAuditId($auditHeaderId);
            // mapping nama dept → singkatan (uppercase)
            $deptMap = [
                'QUALITY MANAGEMENT'                  => 'QM',
                'PROCUREMENT'                         => 'PROC',
                'STAMPING OPERATION'                  => 'SO',
                'ENGINE OPERATION'                    => 'EO',
                'HR & ADMINISTRATION'                 => 'HRA',
                'CONTROLLING'                         => 'CO',
                'PPC'                                 => 'PPC',
                'MANAGEMENT REPRESENTATIVE'           => 'MR',
                'MANUFACTURING ENGINEERING ENGINE'    => 'MEE',
                'MANUFACTURING ENGINEERING STAMPING'  => 'MES',
            ];

            // 1) Query dasar + optional filter by auditHeader
            $query = AuditWorkflowLog::with('auditDetail');
            if ($auditHeaderId && AuditHeader::where('id',$auditHeaderId)->exists()) {
                $query->whereHas('auditDetail', fn($q) =>
                    $q->where('audit_header_id',$auditHeaderId)
                );
            }
            $logs = $query->get();

            // 2) Group by department_assigned
            $byDept = $logs->groupBy('department_assigned');

            // 3) Kalkulasi per dept
            $data = [];
            foreach ($byDept as $fullDept => $group) {
                $key  = strtoupper(trim($fullDept));
                $abbr = $deptMap[$key] ?? $key;

                $countOfi   = $group->where('auditDetail.classification', 'OFI')->count();
                $countMajor = $group->where('auditDetail.classification', 'Major')->count();
                $countMinor = $group->where('auditDetail.classification', 'Minor')->count();

                $data[] = [
                    'department' => $abbr,
                    'OFI'        => $countOfi,
                    'Major'      => $countMajor,
                    'Minor'      => $countMinor,
                ];
            }

            return response()->json($data);
        }





    public function getMonthlySubmittedParts(Request $request)
    {
        if ($request->ajax()) {
            $currentMonth = now()->month;
            $currentYear = now()->year;

            $records = DB::table('supplier_monthly_records')
                ->join('mst_parts', 'supplier_monthly_records.id_part', '=', 'mst_parts.id_part')
                ->join('mst_suppliers', 'mst_parts.id_supplier', '=', 'mst_suppliers.id') // Join with supplier table
                ->select(
                    'supplier_monthly_records.id',
                    'mst_parts.part_no',
                    'mst_parts.description',
                    'supplier_monthly_records.date',
                    'supplier_monthly_records.sample_accuracy',
                    'supplier_monthly_records.actual_accuracy',
                    'supplier_monthly_records.signals',
                    'supplier_monthly_records.qm_check',
                    'supplier_monthly_records.attachment',
                    'mst_suppliers.supplier_name' // Include supplier name
                )
                ->whereMonth('supplier_monthly_records.date', $currentMonth)
                ->whereYear('supplier_monthly_records.date', $currentYear)
                ->orderBy('supplier_monthly_records.date', 'desc') // Sort by newest date
                ->get();

            return DataTables::of($records)
                ->addIndexColumn()
                ->editColumn('signals', function ($row) {
                    return $row->signals === 'Y' ? 'Yellow' : 'Green';
                })
                ->editColumn('attachment', function ($row) {
                    if ($row->attachment) {
                        $attachments = json_decode($row->attachment, true);
                        $links = '';
                        foreach ($attachments as $attachment) {
                            $links .= '<a href="' . url($attachment) . '" target="_blank">View</a><br>';
                        }
                        return $links;
                    }
                    return 'No Attachment';
                })
                ->rawColumns(['attachment']) // To allow HTML links in the attachment column
                ->make(true);
        }
    }














}