<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the reports page with filters.
     */
    public function index(Request $request)
    {
        $filterOptions = $this->reportService->getFilterOptions();

        // Get filters from request
        $filters = $this->extractFilters($request);

        // Get report data
        $reportData = $this->reportService->getReportData($filters);

        return view('admin.reports.index', [
            'filterOptions' => $filterOptions,
            'reportData' => $reportData,
            'filters' => $filters,
        ]);
    }

    /**
     * Generate report with AJAX (for dynamic filtering).
     */
    public function generate(Request $request)
    {
        $filters = $this->extractFilters($request);
        $reportData = $this->reportService->getReportData($filters);

        return response()->json([
            'success' => true,
            'data' => $reportData,
        ]);
    }

    /**
     * Export report to Excel/CSV.
     */
    public function exportExcel(Request $request)
    {
        $filters = $this->extractFilters($request);
        $exportData = $this->reportService->getExportData($filters);

        $filename = 'license_report_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($exportData) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Summary section
            fputcsv($file, ['LICENSE MANAGEMENT REPORT']);
            fputcsv($file, ['Generated At:', $exportData['generated_at']->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Active Filters section
            if (!empty($exportData['filters'])) {
                fputcsv($file, ['FILTERS APPLIED']);
                foreach ($exportData['filters'] as $key => $value) {
                    $label = ucfirst(str_replace('_', ' ', $key));
                    fputcsv($file, [$label . ':', $value]);
                }
                fputcsv($file, []);
            }

            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Total Licenses:', $exportData['summary']['total_licenses']]);
            fputcsv($file, ['Total Max Users:', $exportData['summary']['total_max_users']]);
            fputcsv($file, ['Total Assigned:', $exportData['summary']['total_assigned']]);
            fputcsv($file, ['Total Available:', $exportData['summary']['total_available']]);
            fputcsv($file, ['Total Cost:', '$' . number_format($exportData['summary']['total_cost'], 2)]);
            fputcsv($file, ['Utilization:', $exportData['summary']['utilization_percentage'] . '%']);
            fputcsv($file, []);
            fputcsv($file, ['DETAILED DATA']);

            // Headers
            if (!empty($exportData['rows'])) {
                fputcsv($file, array_keys($exportData['rows'][0]));

                // Data rows
                foreach ($exportData['rows'] as $row) {
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export report to PDF (redirects to print view for browser PDF export).
     */
    public function exportPdf(Request $request)
    {
        // Redirect to print view - user can use browser's "Print to PDF" feature
        return redirect()->route('admin.reports.print', $request->query())
            ->with('info', 'Use your browser\'s "Print" function (Ctrl+P) and select "Save as PDF" to export.');
    }

    /**
     * Print view for the report.
     */
    public function print(Request $request)
    {
        $filters = $this->extractFilters($request);
        $reportData = $this->reportService->getReportData($filters);

        return view('admin.reports.print', [
            'reportData' => $reportData,
            'filters' => $filters,
        ]);
    }

    /**
     * Extract filters from request.
     */
    protected function extractFilters(Request $request): array
    {
        return [
            'license_ids' => $request->input('license_ids', []),
            'vendor_ids' => $request->input('vendor_ids', []),
            'city_ids' => $request->input('city_ids', []),
            'division_ids' => $request->input('division_ids', []),
            'department_ids' => $request->input('department_ids', []),
            'date_context' => $request->input('date_context', ''),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'renewal_status' => $request->input('renewal_status', []),
        ];
    }
}
