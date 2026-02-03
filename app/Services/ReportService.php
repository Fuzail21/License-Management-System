<?php

namespace App\Services;

use App\Models\License;
use App\Models\LicenseStatus;
use App\Models\UserLicense;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Division;
use App\Models\City;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Date context options for filtering.
     */
    const DATE_CONTEXT_LICENSE_CREATED = 'license_created';
    const DATE_CONTEXT_LICENSE_RENEWAL = 'license_renewal';
    const DATE_CONTEXT_USER_ASSIGNED = 'user_assigned';

    /**
     * Get filtered report data based on provided filters.
     * All components (summary, tables, charts) use the same filtered dataset.
     */
    public function getReportData(array $filters = []): array
    {
        // First, get the base filtered licenses based on all filters
        $filteredLicenses = $this->getFilteredLicenses($filters);

        // Calculate summary from the filtered data
        $summary = $this->calculateSummaryFromLicenses($filteredLicenses, $filters);

        // Get department distribution from same filtered data
        $departmentDistribution = $this->getDepartmentDistributionFromLicenses($filteredLicenses, $filters);

        // Get vendor distribution from same filtered data
        $vendorDistribution = $this->getVendorDistributionFromLicenses($filteredLicenses);

        // Build active filters summary for display
        $activeFilters = $this->buildActiveFiltersSummary($filters);

        return [
            'summary' => $summary,
            'licenses' => $filteredLicenses,
            'departmentDistribution' => $departmentDistribution,
            'vendorDistribution' => $vendorDistribution,
            'filters' => $filters,
            'activeFilters' => $activeFilters,
            'generatedAt' => now(),
        ];
    }

    /**
     * Get filtered licenses with all filters applied consistently.
     */
    protected function getFilteredLicenses(array $filters): Collection
    {
        $query = License::query()
            ->with(['vendor', 'userLicenses.employee.department.division.city'])
            ->withCount('userLicenses')
            ->approved();

        // Apply license filter
        if (!empty($filters['license_ids'])) {
            $query->whereIn('id', $filters['license_ids']);
        }

        // Apply vendor filter
        if (!empty($filters['vendor_ids'])) {
            $query->whereIn('vendor_id', $filters['vendor_ids']);
        }

        // Apply date context and date range filters
        $dateContext = $filters['date_context'] ?? '';
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        if ($dateContext && ($dateFrom || $dateTo)) {
            switch ($dateContext) {
                case self::DATE_CONTEXT_LICENSE_CREATED:
                    if ($dateFrom) {
                        $query->whereDate('created_at', '>=', $dateFrom);
                    }
                    if ($dateTo) {
                        $query->whereDate('created_at', '<=', $dateTo);
                    }
                    break;

                case self::DATE_CONTEXT_LICENSE_RENEWAL:
                    if ($dateFrom) {
                        $query->whereDate('renewal_date', '>=', $dateFrom);
                    }
                    if ($dateTo) {
                        $query->whereDate('renewal_date', '<=', $dateTo);
                    }
                    break;

                case self::DATE_CONTEXT_USER_ASSIGNED:
                    // For user assigned date, we filter the userLicenses
                    $query->whereHas('userLicenses', function ($q) use ($dateFrom, $dateTo) {
                        if ($dateFrom) {
                            $q->whereDate('assigned_date', '>=', $dateFrom);
                        }
                        if ($dateTo) {
                            $q->whereDate('assigned_date', '<=', $dateTo);
                        }
                    });
                    break;
            }
        }

        // Apply renewal status filter
        if (!empty($filters['renewal_status'])) {
            $today = Carbon::today();
            $query->where(function ($q) use ($filters, $today) {
                foreach ($filters['renewal_status'] as $status) {
                    switch ($status) {
                        case 'expired':
                            $q->orWhere(function ($sq) use ($today) {
                                $sq->whereNotNull('renewal_date')
                                   ->where('renewal_date', '<', $today);
                            });
                            break;
                        case 'expiring_soon':
                            $q->orWhere(function ($sq) use ($today) {
                                $sq->whereNotNull('renewal_date')
                                   ->whereBetween('renewal_date', [$today, $today->copy()->addDays(30)]);
                            });
                            break;
                        case 'active':
                            $q->orWhere(function ($sq) use ($today) {
                                $sq->where(function ($ssq) use ($today) {
                                    $ssq->whereNull('renewal_date')
                                        ->orWhere('renewal_date', '>', $today->copy()->addDays(30));
                                });
                            });
                            break;
                    }
                }
            });
        }

        $licenses = $query->get();

        // Apply organizational filters (city, division, department) by filtering userLicenses
        $hasOrgFilters = !empty($filters['city_ids']) || !empty($filters['division_ids']) || !empty($filters['department_ids']);
        $hasDateContextUserAssigned = ($dateContext === self::DATE_CONTEXT_USER_ASSIGNED) && ($dateFrom || $dateTo);

        $licenses = $licenses->map(function ($license) use ($filters, $hasOrgFilters, $hasDateContextUserAssigned, $dateFrom, $dateTo) {
            $filteredUserLicenses = $license->userLicenses;

            // Filter by organizational hierarchy
            if ($hasOrgFilters) {
                $filteredUserLicenses = $filteredUserLicenses->filter(function ($ul) use ($filters) {
                    if (!$ul->employee || !$ul->employee->department) {
                        return false;
                    }

                    $dept = $ul->employee->department;

                    if (!empty($filters['department_ids']) && !in_array($dept->id, $filters['department_ids'])) {
                        return false;
                    }

                    if (!empty($filters['division_ids']) && !in_array($dept->division_id, $filters['division_ids'])) {
                        return false;
                    }

                    if (!empty($filters['city_ids']) && $dept->division && !in_array($dept->division->city_id, $filters['city_ids'])) {
                        return false;
                    }

                    return true;
                });
            }

            // Filter by assigned date if date context is user_assigned
            if ($hasDateContextUserAssigned) {
                $filteredUserLicenses = $filteredUserLicenses->filter(function ($ul) use ($dateFrom, $dateTo) {
                    if (!$ul->assigned_date) {
                        return false;
                    }
                    $assignedDate = Carbon::parse($ul->assigned_date);
                    if ($dateFrom && $assignedDate->lt(Carbon::parse($dateFrom))) {
                        return false;
                    }
                    if ($dateTo && $assignedDate->gt(Carbon::parse($dateTo))) {
                        return false;
                    }
                    return true;
                });
            }

            $license->filtered_user_licenses = $filteredUserLicenses;
            $license->filtered_count = $filteredUserLicenses->count();

            return $license;
        });

        // If organizational filters are applied, only show licenses with matching assignments
        if ($hasOrgFilters) {
            $licenses = $licenses->filter(fn($l) => $l->filtered_count > 0);
        }

        return $licenses->sortByDesc('filtered_count')->values();
    }

    /**
     * Calculate summary statistics from pre-filtered licenses.
     */
    protected function calculateSummaryFromLicenses(Collection $licenses, array $filters): array
    {
        $totalLicenses = $licenses->count();
        $totalMaxUsers = $licenses->sum('max_users');
        $totalAssigned = $licenses->sum('filtered_count');
        $totalAvailable = $totalMaxUsers - $totalAssigned;
        $totalCost = $licenses->sum('cost');

        // Calculate utilization percentage
        $utilizationPercentage = $totalMaxUsers > 0
            ? round(($totalAssigned / $totalMaxUsers) * 100, 1)
            : 0;

        // Count by renewal status
        $today = Carbon::today();
        $expiredCount = $licenses->filter(fn($l) => $l->renewal_date && $l->renewal_date < $today)->count();
        $expiringSoonCount = $licenses->filter(fn($l) => $l->renewal_date && $l->renewal_date >= $today && $l->renewal_date <= $today->copy()->addDays(30))->count();
        $activeCount = $licenses->filter(fn($l) => !$l->renewal_date || $l->renewal_date > $today->copy()->addDays(30))->count();

        return [
            'total_licenses' => $totalLicenses,
            'total_max_users' => $totalMaxUsers,
            'total_assigned' => $totalAssigned,
            'total_available' => max(0, $totalAvailable),
            'total_cost' => $totalCost,
            'utilization_percentage' => $utilizationPercentage,
            'expired_count' => $expiredCount,
            'expiring_soon_count' => $expiringSoonCount,
            'active_count' => $activeCount,
        ];
    }

    /**
     * Get department distribution from pre-filtered licenses.
     */
    protected function getDepartmentDistributionFromLicenses(Collection $licenses, array $filters): Collection
    {
        $departmentData = [];

        foreach ($licenses as $license) {
            $userLicenses = $license->filtered_user_licenses ?? $license->userLicenses;

            foreach ($userLicenses as $ul) {
                if (!$ul->employee || !$ul->employee->department) {
                    continue;
                }

                $dept = $ul->employee->department;
                $deptId = $dept->id;

                if (!isset($departmentData[$deptId])) {
                    $departmentData[$deptId] = [
                        'id' => $deptId,
                        'name' => $dept->name,
                        'division' => $dept->division->name ?? 'N/A',
                        'city' => $dept->division->city->name ?? 'N/A',
                        'total_employees' => $dept->employees()->count(),
                        'license_count' => 0,
                        'total_cost' => 0,
                    ];
                }

                $departmentData[$deptId]['license_count']++;
                $departmentData[$deptId]['total_cost'] += $license->cost ?? 0;
            }
        }

        return collect($departmentData)
            ->sortByDesc('license_count')
            ->values();
    }

    /**
     * Get vendor distribution from pre-filtered licenses.
     */
    protected function getVendorDistributionFromLicenses(Collection $licenses): Collection
    {
        $vendorData = [];

        foreach ($licenses as $license) {
            if (!$license->vendor) {
                continue;
            }

            $vendorId = $license->vendor->id;

            if (!isset($vendorData[$vendorId])) {
                $vendorData[$vendorId] = [
                    'id' => $vendorId,
                    'name' => $license->vendor->name,
                    'total_licenses' => 0,
                    'total_assigned' => 0,
                    'total_cost' => 0,
                ];
            }

            $vendorData[$vendorId]['total_licenses']++;
            $vendorData[$vendorId]['total_assigned'] += $license->filtered_count ?? $license->user_licenses_count;
            $vendorData[$vendorId]['total_cost'] += $license->cost ?? 0;
        }

        return collect($vendorData)
            ->sortByDesc('total_assigned')
            ->values();
    }

    /**
     * Build active filters summary for display.
     */
    protected function buildActiveFiltersSummary(array $filters): array
    {
        $summary = [];

        // Date Context
        if (!empty($filters['date_context'])) {
            $contextLabels = [
                self::DATE_CONTEXT_LICENSE_CREATED => 'Created At (Licenses)',
                self::DATE_CONTEXT_LICENSE_RENEWAL => 'Renewal Date (Licenses)',
                self::DATE_CONTEXT_USER_ASSIGNED => 'Assigned Date (User Licenses)',
            ];
            $summary['date_context'] = $contextLabels[$filters['date_context']] ?? $filters['date_context'];
        }

        // Date Range
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $from = $filters['date_from'] ?? 'Any';
            $to = $filters['date_to'] ?? 'Any';
            $summary['date_range'] = "$from to $to";
        }

        // Renewal Status
        if (!empty($filters['renewal_status'])) {
            $statusLabels = [
                'active' => 'Active',
                'expiring_soon' => 'Expiring Soon',
                'expired' => 'Expired',
            ];
            $statuses = array_map(fn($s) => $statusLabels[$s] ?? $s, $filters['renewal_status']);
            $summary['renewal_status'] = implode(', ', $statuses);
        }

        // Licenses
        if (!empty($filters['license_ids'])) {
            $summary['licenses'] = count($filters['license_ids']) . ' selected';
        }

        // Vendors
        if (!empty($filters['vendor_ids'])) {
            $summary['vendors'] = count($filters['vendor_ids']) . ' selected';
        }

        // Cities
        if (!empty($filters['city_ids'])) {
            $summary['cities'] = count($filters['city_ids']) . ' selected';
        }

        // Divisions
        if (!empty($filters['division_ids'])) {
            $summary['divisions'] = count($filters['division_ids']) . ' selected';
        }

        // Departments
        if (!empty($filters['department_ids'])) {
            $summary['departments'] = count($filters['department_ids']) . ' selected';
        }

        return $summary;
    }

    /**
     * Get filter options for the report form.
     */
    public function getFilterOptions(): array
    {
        return [
            'date_contexts' => [
                ['value' => '', 'label' => '-- Select Date Context --'],
                ['value' => self::DATE_CONTEXT_LICENSE_CREATED, 'label' => 'Created At - Licenses'],
                ['value' => self::DATE_CONTEXT_LICENSE_RENEWAL, 'label' => 'Renewal Date - Licenses'],
                ['value' => self::DATE_CONTEXT_USER_ASSIGNED, 'label' => 'Assigned Date - User Licenses'],
            ],
            'licenses' => License::approved()
                ->with('vendor')
                ->orderBy('license_name')
                ->get()
                ->map(fn($l) => [
                    'id' => $l->id,
                    'name' => $l->license_name,
                    'vendor' => $l->vendor->name ?? 'N/A',
                ]),
            'vendors' => Vendor::orderBy('name')
                ->get()
                ->map(fn($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                ]),
            'cities' => City::orderBy('name')
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                ]),
            'divisions' => Division::with('city')
                ->orderBy('name')
                ->get()
                ->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'city_id' => $d->city_id,
                    'city' => $d->city->name ?? 'N/A',
                ]),
            'departments' => Department::with('division.city')
                ->orderBy('name')
                ->get()
                ->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'division_id' => $d->division_id,
                    'division' => $d->division->name ?? 'N/A',
                    'city_id' => $d->division->city_id ?? null,
                ]),
        ];
    }

    /**
     * Get data formatted for Excel export.
     */
    public function getExportData(array $filters = []): array
    {
        $reportData = $this->getReportData($filters);

        $exportRows = [];

        foreach ($reportData['licenses'] as $license) {
            $userLicenses = $license->filtered_user_licenses ?? $license->userLicenses;

            if ($userLicenses->isEmpty()) {
                $exportRows[] = [
                    'License Name' => $license->license_name,
                    'Vendor' => $license->vendor->name ?? 'N/A',
                    'Max Users' => $license->max_users,
                    'Assigned' => 0,
                    'Available' => $license->max_users,
                    'Cost' => $license->cost,
                    'Renewal Date' => $license->renewal_date?->format('Y-m-d') ?? 'N/A',
                    'Employee Name' => 'No assignments',
                    'Employee Email' => '',
                    'Department' => '',
                    'Division' => '',
                    'City' => '',
                    'Assigned Date' => '',
                ];
            } else {
                foreach ($userLicenses as $ul) {
                    $employee = $ul->employee;
                    $dept = $employee?->department;
                    $division = $dept?->division;
                    $city = $division?->city;

                    $exportRows[] = [
                        'License Name' => $license->license_name,
                        'Vendor' => $license->vendor->name ?? 'N/A',
                        'Max Users' => $license->max_users,
                        'Assigned' => $license->filtered_count ?? $license->user_licenses_count,
                        'Available' => max(0, $license->max_users - ($license->filtered_count ?? $license->user_licenses_count)),
                        'Cost' => $license->cost,
                        'Renewal Date' => $license->renewal_date?->format('Y-m-d') ?? 'N/A',
                        'Employee Name' => $employee?->full_name ?? 'N/A',
                        'Employee Email' => $employee?->email ?? 'N/A',
                        'Department' => $dept?->name ?? 'N/A',
                        'Division' => $division?->name ?? 'N/A',
                        'City' => $city?->name ?? 'N/A',
                        'Assigned Date' => $ul->assigned_date?->format('Y-m-d') ?? 'N/A',
                    ];
                }
            }
        }

        return [
            'summary' => $reportData['summary'],
            'rows' => $exportRows,
            'filters' => $reportData['activeFilters'],
            'generated_at' => $reportData['generatedAt'],
        ];
    }
}
