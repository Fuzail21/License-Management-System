<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>License Management Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4f46e5;
        }
        .header h1 {
            font-size: 20px;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11px;
            color: #666;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-item {
            display: table-cell;
            width: 16.66%;
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #4f46e5;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #dee2e6;
        }
        th {
            background-color: #4f46e5;
            color: white;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
        }
        td {
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-gray { background: #e5e7eb; color: #374151; }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>License Management Report</h1>
            <p>Generated on {{ $reportData['generatedAt']->format('F d, Y \a\t h:i A') }}</p>
        </div>

        {{-- Summary Section --}}
        <div class="section">
            <div class="section-title">Summary Overview</div>
            <table>
                <tr>
                    <th class="text-center">Total Licenses</th>
                    <th class="text-center">Max Users</th>
                    <th class="text-center">Total Assigned</th>
                    <th class="text-center">Available</th>
                    <th class="text-center">Utilization</th>
                    <th class="text-center">Total Cost</th>
                </tr>
                <tr>
                    <td class="text-center" style="font-weight: bold; font-size: 12px;">{{ $reportData['summary']['total_licenses'] }}</td>
                    <td class="text-center" style="font-weight: bold; font-size: 12px;">{{ $reportData['summary']['total_max_users'] }}</td>
                    <td class="text-center" style="font-weight: bold; font-size: 12px;">{{ $reportData['summary']['total_assigned'] }}</td>
                    <td class="text-center" style="font-weight: bold; font-size: 12px;">{{ $reportData['summary']['total_available'] }}</td>
                    <td class="text-center" style="font-weight: bold; font-size: 12px;">{{ $reportData['summary']['utilization_percentage'] }}%</td>
                    <td class="text-center" style="font-weight: bold; font-size: 12px;">${{ number_format($reportData['summary']['total_cost'], 2) }}</td>
                </tr>
            </table>

            <table style="width: 50%; margin: 0 auto;">
                <tr>
                    <th class="text-center" style="background: #10b981;">Active</th>
                    <th class="text-center" style="background: #f59e0b;">Expiring Soon</th>
                    <th class="text-center" style="background: #ef4444;">Expired</th>
                </tr>
                <tr>
                    <td class="text-center" style="font-weight: bold;">{{ $reportData['summary']['active_count'] }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ $reportData['summary']['expiring_soon_count'] }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ $reportData['summary']['expired_count'] }}</td>
                </tr>
            </table>
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
                        @endphp
                        <tr>
                            <td>{{ $license->license_name }}</td>
                            <td>{{ $license->vendor->name ?? 'N/A' }}</td>
                            <td class="text-center">{{ $license->max_users }}</td>
                            <td class="text-center">{{ $assigned }}</td>
                            <td class="text-center">{{ $available }}</td>
                            <td class="text-center">{{ $utilization }}%</td>
                            <td class="text-right">${{ number_format($license->cost, 2) }}</td>
                            <td class="text-center">
                                @if($license->renewal_date)
                                    <span class="badge {{ $remainingDays < 0 ? 'badge-gray' : ($remainingDays <= 2 ? 'badge-red' : ($remainingDays <= 30 ? 'badge-yellow' : 'badge-green')) }}">
                                        {{ $license->renewal_date->format('M d, Y') }}
                                    </span>
                                @else
                                    N/A
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
                        <th class="text-center">Licenses</th>
                        <th class="text-right">Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['departmentDistribution'] as $dept)
                        <tr>
                            <td>{{ $dept['name'] }}</td>
                            <td>{{ $dept['division'] }}</td>
                            <td>{{ $dept['city'] }}</td>
                            <td class="text-center">{{ $dept['total_employees'] }}</td>
                            <td class="text-center">{{ $dept['license_count'] }}</td>
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
                            <td>{{ $vendor['name'] }}</td>
                            <td class="text-center">{{ $vendor['total_licenses'] }}</td>
                            <td class="text-center">{{ $vendor['total_assigned'] }}</td>
                            <td class="text-right">${{ number_format($vendor['total_cost'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>License Management System - Confidential Report</p>
            <p>Generated by {{ auth()->user()->name ?? 'System' }} on {{ now()->format('F d, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>
