<?php

namespace App\Http\Controllers\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\VendorReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected VendorReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $hotelIds = Hotel::where('vendor_id', auth()->id())->pluck('id')->toArray();

        $period = $request->get('period', 'month');
        $from = $request->get('from');
        $to = $request->get('to');

        $occupancy = $this->reportService->occupancyByPeriod($hotelIds, $period);
        $revenueByRoom = $this->reportService->revenueByRoomType($hotelIds, $from, $to);
        $comparison = $this->reportService->periodComparison($hotelIds, $period);
        $revenueChart = $this->reportService->revenueChartData($hotelIds, 6);

        return view('admin.vendor.reports.index', compact(
            'occupancy',
            'revenueByRoom',
            'comparison',
            'revenueChart',
            'period',
            'from',
            'to'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $hotelIds = Hotel::where('vendor_id', auth()->id())->pluck('id')->toArray();
        $from = $request->get('from');
        $to = $request->get('to');

        $rows = $this->reportService->exportCsv($hotelIds, $from, $to);

        $filename = 'vendor-report-' . now()->format('Y-m-d-His') . '.csv';

        return Response::streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
