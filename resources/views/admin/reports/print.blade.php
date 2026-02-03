<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>License Management Report - Print</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #1f2937;
            background: white;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4f46e5;
        }
        .header h1 {
            font-size: 24px;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        .header p {
            color: #6b7280;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-card {
            text-align: center;
            padding: 15px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .summary-card .value {
            font-size: 24px;
            font-weight: 700;
            color: #4f46e5;
        }
        .summary-card .label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background-color: #f9fafb;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
        }
        tr:hover {
            background-color: #f9fafb;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 500;
        }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .progress-bar {
            width: 60px;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
        }
        .progress-fill {
            height: 100%;
            border-radius: 3px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .back-btn:hover {
            background: #4338ca;
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <a href="{{ route('admin.reports.index', request()->query()) }}" class="back-btn">Back to Reports</a>
        <button onclick="window.print()" class="back-btn" style="background: #059669;">Print Report</button>
    </div>

    <div class="header">
        <h1>License Management Report</h1>
        <p>Generated on {{ $reportData['generatedAt']->format('F d, Y \a\t h:i A') }}</p>
    </div>

    {{-- Active Filters Applied --}}
    @if(!empty($reportData['activeFilters']))
    <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 15px; margin-bottom: 25px;">
        <div style="font-size: 13px; font-weight: 600; color: #0369a1; margin-bottom: 10px;">Filters Applied to This Report:</div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            @foreach($reportData['activeFilters'] as $key => $value)
                @php
                    $labels = [
                        'date_context' => 'Date Context',
                        'date_range' => 'Date Range',
                        'renewal_status' => 'Status',
                        'licenses' => 'Licenses',
                        'vendors' => 'Vendors',
                        'cities' => 'Cities',
                        'divisions' => 'Divisions',
                        'departments' => 'Departments',
                    ];
                    $label = $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
                @endphp
                <span style="display: inline-block; padding: 4px 10px; background: white; border: 1px solid #7dd3fc; border-radius: 9999px; font-size: 11px; color: #0369a1;">
                    <strong>{{ $label }}:</strong> {{ $value }}
                </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="summary-grid">
        <div class="summary-card">
            <div class="value">{{ $reportData['summary']['total_licenses'] }}</div>
            <div class="label">Total Licenses</div>
        </div>
        <div class="summary-card">
            <div class="value">{{ $reportData['summary']['total_assigned'] }}</div>
            <div class="label">Assigned</div>
        </div>
        <div class="summary-card">
            <div class="value">{{ $reportData['summary']['total_available'] }}</div>
            <div class="label">Available</div>
        </div>
        <div class="summary-card">
            <div class="value">{{ $reportData['summary']['utilization_percentage'] }}%</div>
            <div class="label">Utilization</div>
        </div>
        <div class="summary-card">
            <div class="value">${{ number_format($reportData['summary']['total_cost'], 0) }}</div>
            <div class="label">Total Cost</div>
        </div>
        <div class="summary-card">
            <div class="value" style="color: #ef4444;">{{ $reportData['summary']['expired_count'] }}</div>
            <div class="label">Expired</div>
        </div>
    </div>

    {{-- License Details --}}
    <div class="section">
        <div class="section-title">License Details</div>
        <table>
            <thead>
                <tr>
                    <th>License Name</th>
                    <th>Vendor</th>
                    <th class="text-center">Max Users</th>
                    <th class="text-center">Assigned</th>
                    <th class="text-center">Available</th>
                    <th class="text-center">Utilization</th>
                    <th class="text-right">Cost</th>
                    <th class="text-center">Renewal Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['licenses'] as $license)
                    @php
                        $assigned = $license->filtered_count ?? $license->user_licenses_count;
                        $available = max(0, $license->max_users - $assigned);
                        $utilization = $license->max_users > 0 ? round(($assigned / $license->max_users) * 100, 1) : 0;
                        $remainingDays = $license->remaining_days;
                        $barColor = $utilization >= 90 ? '#ef4444' : ($utilization >= 70 ? '#f59e0b' : '#10b981');
                    @endphp
                    <tr>
                        <td><strong>{{ $license->license_name }}</strong></td>
                        <td>{{ $license->vendor->name ?? 'N/A' }}</td>
                        <td class="text-center">{{ $license->max_users }}</td>
                        <td class="text-center">
                            <span class="badge badge-blue">{{ $assigned }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $available > 0 ? 'badge-green' : 'badge-red' }}">{{ $available }}</span>
                        </td>
                        <td class="text-center">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ min($utilization, 100) }}%; background: {{ $barColor }};"></div>
                            </div>
                            {{ $utilization }}%
                        </td>
                        <td class="text-right">${{ number_format($license->cost, 2) }}</td>
                        <td class="text-center">
                            @if($license->renewal_date)
                                <span class="badge {{ $remainingDays < 0 ? 'badge-gray' : ($remainingDays <= 2 ? 'badge-red' : ($remainingDays <= 30 ? 'badge-yellow' : 'badge-green')) }}">
                                    {{ $license->renewal_date->format('M d, Y') }}
                                </span>
                            @else
                                <span class="badge badge-gray">N/A</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Department Summary --}}
    @if($reportData['departmentDistribution']->isNotEmpty())
    <div class="section page-break">
        <div class="section-title">Department Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Division</th>
                    <th>City</th>
                    <th class="text-center">Employees</th>
                    <th class="text-center">Licenses Assigned</th>
                    <th class="text-right">Total Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['departmentDistribution'] as $dept)
                    <tr>
                        <td><strong>{{ $dept['name'] }}</strong></td>
                        <td>{{ $dept['division'] }}</td>
                        <td>{{ $dept['city'] }}</td>
                        <td class="text-center">{{ $dept['total_employees'] }}</td>
                        <td class="text-center">
                            <span class="badge badge-blue">{{ $dept['license_count'] }}</span>
                        </td>
                        <td class="text-right">${{ number_format($dept['total_cost'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Vendor Summary --}}
    @if($reportData['vendorDistribution']->isNotEmpty())
    <div class="section">
        <div class="section-title">Vendor Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th class="text-center">Total Licenses</th>
                    <th class="text-center">Total Assigned</th>
                    <th class="text-right">Total Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['vendorDistribution'] as $vendor)
                    <tr>
                        <td><strong>{{ $vendor['name'] }}</strong></td>
                        <td class="text-center">{{ $vendor['total_licenses'] }}</td>
                        <td class="text-center">
                            <span class="badge badge-blue">{{ $vendor['total_assigned'] }}</span>
                        </td>
                        <td class="text-right">${{ number_format($vendor['total_cost'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p><strong>License Management System</strong> - Confidential Report</p>
        <p>Generated by {{ auth()->user()->name ?? 'System' }} on {{ now()->format('F d, Y h:i A') }}</p>
    </div>
</body>
</html>
