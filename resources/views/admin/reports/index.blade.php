@extends('layouts.admin')

@section('title', 'License Reports')
@section('page-title', 'License Reports')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .filter-chip {
        transition: all 0.2s ease;
    }
    .filter-chip:hover {
        transform: translateY(-1px);
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    @media print {
        .no-print { display: none !important; }
        .print-break { page-break-before: always; }
    }
</style>
@endpush

@section('content')
<div x-data="reportApp()" x-init="init()">
    {{-- Filter Panel --}}
    <div class="bg-white shadow rounded-lg mb-6 no-print">
        <div class="px-5 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">Report Filters</h3>
                </div>
                <div class="flex items-center space-x-2">
                    <a type="button" href="{{ route('admin.reports.index') }}" style="margin-right: 10px;" class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50 transition">
                        Reset
                    </a>
                    <button type="button" @click="applyFilters()" class="px-4 py-1.5 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-md transition flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Generate Report</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-5">
            <form id="reportForm" method="GET" action="{{ route('admin.reports.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                    {{-- License Filter --}}
                    <div style="margin-top: 2px; padding: 10px; border-right: 1px solid #dddddd;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">License</label>
                        <select name="license_ids[]" multiple x-model="filters.license_ids" style="border: 1px solid #dddddd; border-radius: 0.375rem; padding: 0.5rem;"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @foreach($filterOptions['licenses'] as $license)
                                <option value="{{ $license['id'] }}" {{ in_array($license['id'], $filters['license_ids'] ?? []) ? 'selected' : '' }}>
                                    {{ $license['name'] }} ({{ $license['vendor'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Vendor Filter --}}
                    <div style="margin-top: 2px; padding: 10px; border-right: 1px solid #dddddd;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                        <select name="vendor_ids[]" multiple x-model="filters.vendor_ids" style="border: 1px solid #dddddd; border-radius: 0.375rem; padding: 0.5rem;"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @foreach($filterOptions['vendors'] as $vendor)
                                <option value="{{ $vendor['id'] }}" {{ in_array($vendor['id'], $filters['vendor_ids'] ?? []) ? 'selected' : '' }}>
                                    {{ $vendor['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- City Filter --}}
                    <div style="margin-top: 2px; padding: 10px; border-right: 1px solid #dddddd;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <select name="city_ids[]" multiple x-model="filters.city_ids" @change="updateDivisions()" style="border: 1px solid #dddddd; border-radius: 0.375rem; padding: 0.5rem;"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @foreach($filterOptions['cities'] as $city)
                                <option value="{{ $city['id'] }}" {{ in_array($city['id'], $filters['city_ids'] ?? []) ? 'selected' : '' }}>
                                    {{ $city['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Division Filter --}}
                    <div style="margin-top: 2px; padding: 10px; border-right: 1px solid #dddddd;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Division</label>
                        <select name="division_ids[]" multiple x-model="filters.division_ids" @change="updateDepartments()" style="border: 1px solid #dddddd; border-radius: 0.375rem; padding: 0.5rem;"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <template x-for="division in filteredDivisions" :key="division.id">
                                <option :value="division.id" x-text="division.name + ' (' + division.city + ')'"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Department Filter --}}
                    <div style="margin-top: 2px; padding: 10px; border-right: 1px solid #dddddd;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select name="department_ids[]" multiple x-model="filters.department_ids" style="border: 1px solid #dddddd; border-radius: 0.375rem; padding: 0.5rem;"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <template x-for="dept in filteredDepartments" :key="dept.id">
                                <option :value="dept.id" x-text="dept.name + ' (' + dept.division + ')'"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Second Row: Date Context, Date Range & Status --}}
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4 mt-4" style="margin-top: 20px; padding: 20px; border-top: 1px solid #dddddd">
                    {{-- Date Context (Required before date range) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Date Context <span class="text-red-500">*</span>
                        </label>
                        <select name="date_context" x-model="filters.date_context" style="border: 1px solid #dddddd; border-radius: 0.375rem; padding: 0.5rem;"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            :class="{ 'border-red-300 bg-red-50': dateContextError }">
                            @foreach($filterOptions['date_contexts'] as $context)
                                <option value="{{ $context['value'] }}" {{ ($filters['date_context'] ?? '') === $context['value'] ? 'selected' : '' }}>
                                    {{ $context['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <p x-show="dateContextError" x-cloak class="mt-1 text-xs text-red-500">
                            Select date context before using date range
                        </p>
                    </div>

                    {{-- Date From --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" name="date_from" x-model="filters.date_from"
                            :disabled="!filters.date_context"
                            :max="filters.date_to || ''"
                            @change="validateDateRange()"
                            style="border: 1px solid #dddddd; border-radius: 0.375rem; padding: 0.5rem;"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                            :class="{ 'border-red-300': dateRangeError }"
                            value="{{ $filters['date_from'] ?? '' }}">
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" name="date_to" x-model="filters.date_to"
                            :disabled="!filters.date_context"
                            :min="filters.date_from || ''"
                            @change="validateDateRange()"
                            style="border: 1px solid #dddddd; border-radius: 0.375rem; padding: 0.5rem;"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                            :class="{ 'border-red-300': dateRangeError }"
                            value="{{ $filters['date_to'] ?? '' }}">
                        <p x-show="dateRangeError" x-cloak class="mt-1 text-xs text-red-500">
                            End date must be after start date
                        </p>
                    </div>

                    {{-- Renewal Status --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Renewal Status</label>
                        <div class="flex flex-wrap gap-3 mt-1">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="renewal_status[]" value="active" x-model="filters.renewal_status"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                <span class="ml-2 text-sm text-gray-600">Active</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="renewal_status[]" value="expiring_soon" x-model="filters.renewal_status"
                                    class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500">
                                <span class="ml-2 text-sm text-gray-600">Expiring Soon (30 days)</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="renewal_status[]" value="expired" x-model="filters.renewal_status"
                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm text-gray-600">Expired</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Quick Filters --}}
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <span class="text-sm font-medium text-gray-700 mr-3">Quick Filters:</span>
                    <div class="inline-flex flex-wrap gap-2 mt-2">
                        <button type="button" @click="quickFilter('expiring')"
                            class="filter-chip px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 hover:bg-yellow-200 transition">
                            Expiring Soon
                        </button>
                        <button type="button" @click="quickFilter('expired')"
                            class="filter-chip px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 hover:bg-red-200 transition">
                            Expired
                        </button>
                        <button type="button" @click="quickFilter('active')"
                            class="filter-chip px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 hover:bg-green-200 transition">
                            Active
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Export Buttons --}}
    <div class="bg-white shadow rounded-lg p-4 mb-6 no-print">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h4 class="text-sm font-medium text-gray-700">Export Report:</h4>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.reports.export.excel', request()->query()) }}"
                    style="background-color: #16a34a; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; font-weight: 500;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px; margin-right: 8px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export to Excel (CSV)
                </a>
                <a href="{{ route('admin.reports.print', request()->query()) }}"
                    style="background-color: #dc2626; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; font-weight: 500;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px; margin-right: 8px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Export to PDF / Print
                </a>
            </div>
        </div>
    </div>

    {{-- Active Filters Summary Panel --}}
    @if(!empty($reportData['activeFilters']))
    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6 no-print">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h4 class="text-sm font-semibold text-indigo-800 mb-2">Active Filters Applied</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($reportData['activeFilters'] as $key => $value)
                        @php
                            $labels = [
                                'date_context' => 'Date Context',
                                'date_range' => 'Date Range',
                                'renewal_status' => 'Renewal Status',
                                'licenses' => 'Licenses',
                                'vendors' => 'Vendors',
                                'cities' => 'Cities',
                                'divisions' => 'Divisions',
                                'departments' => 'Departments',
                            ];
                            $label = $labels[$key] ?? ucfirst(str_replace('_', ' ', $key));
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white border border-indigo-300 text-indigo-700 shadow-sm">
                            <span class="font-semibold mr-1">{{ $label }}:</span> {{ $value }}
                        </span>
                    @endforeach
                </div>
            </div>
            <div class="flex-shrink-0 ml-4">
                <button type="button" @click="resetFilters()" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    Clear All
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-6">
        {{-- Total Licenses --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 rounded-full bg-indigo-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Licenses</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $reportData['summary']['total_licenses'] }}</p>
                </div>
            </div>
        </div>

        {{-- Total Assigned --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 rounded-full bg-green-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Assigned</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $reportData['summary']['total_assigned'] }}</p>
                </div>
            </div>
        </div>

        {{-- Available --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 rounded-full bg-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Available</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $reportData['summary']['total_available'] }}</p>
                </div>
            </div>
        </div>

        {{-- Utilization --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 rounded-full bg-purple-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Utilization</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $reportData['summary']['utilization_percentage'] }}%</p>
                </div>
            </div>
        </div>

        {{-- Total Cost --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 rounded-full bg-yellow-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Cost</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($reportData['summary']['total_cost'], 2) }}</p>
                </div>
            </div>
        </div>

        {{-- Status Summary --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="space-y-2">
                <p class="text-sm font-medium text-gray-500">Status</p>
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-1"></span>Active</span>
                    <span class="font-semibold">{{ $reportData['summary']['active_count'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-yellow-500 mr-1"></span>Expiring</span>
                    <span class="font-semibold">{{ $reportData['summary']['expiring_soon_count'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-1"></span>Expired</span>
                    <span class="font-semibold">{{ $reportData['summary']['expired_count'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Department Distribution Chart --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h4 class="text-lg font-medium text-gray-900 mb-4">License Distribution by Department</h4>
            <div class="chart-container">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>

        {{-- Vendor Distribution Chart --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h4 class="text-lg font-medium text-gray-900 mb-4">License Distribution by Vendor</h4>
            <div class="chart-container">
                <canvas id="vendorChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Detailed License Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Detailed License Report</h3>
            <p class="text-sm text-gray-500 mt-1">Click on a row to expand and see assigned employees</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            License Name
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vendor
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Max Users
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Assigned
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Available
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Utilization
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cost
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Renewal Date
                        </th>
                    </tr>
                </thead>
                    @forelse($reportData['licenses'] as $index => $license)
                       @php
                            $assigned = $license->filtered_count ?? $license->user_licenses_count;
                            $available = max(0, $license->max_users - $assigned);
                            $utilization = $license->max_users > 0 ? round(($assigned / $license->max_users) * 100, 1) : 0;

                            // Use Carbon's parse method to ensure $renewal_date is a Carbon object
                            $renewalDate = \Illuminate\Support\Carbon::parse($license->renewal_date)->startOfDay();
                            $today = now()->startOfDay();

                            // Calculate difference: positive if in the future, negative if in the past
                            $remainingDays = $today->diffInDays($renewalDate, false);

                            $userLicenses = $license->filtered_user_licenses ?? $license->userLicenses;
                        @endphp
                        {{-- Using tbody with x-data to scope both rows --}}
                        <tbody x-data="{ expanded: false }" class="border-b border-gray-200">
                            {{-- Main Row --}}
                            <tr class="hover:bg-gray-50" @click="expanded = !expanded" style="cursor: pointer;">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="w-4 h-4 mr-2 text-indigo-600 transition-transform duration-200"
                                             :class="expanded ? 'rotate-90' : 'rotate-0'"
                                             fill="none" 
                                             viewBox="0 0 24 24" 
                                             stroke="currentColor">
                                            <path stroke-linecap="round" 
                                                  stroke-linejoin="round" 
                                                  stroke-width="2.5" 
                                                  d="M9 5l7 7-7 7" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900">{{ $license->license_name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $license->vendor->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                    {{ $license->max_users }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                    <span style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: 500; background-color: #dbeafe; color: #1e40af;">
                                        {{ $assigned }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                    <span style="display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 12px; font-weight: 500; background-color: {{ $available > 0 ? '#d1fae5' : '#fee2e2' }}; color: {{ $available > 0 ? '#065f46' : '#991b1b' }};">
                                        {{ $available }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                    <div class="flex items-center justify-center">
                                        <div style="width: 60px; height: 8px; background-color: #e5e7eb; border-radius: 4px; margin-right: 8px;">
                                            <div style="height: 8px; border-radius: 4px; width: {{ min($utilization, 100) }}%; background-color: {{ $utilization >= 90 ? '#ef4444' : ($utilization >= 70 ? '#f59e0b' : '#10b981') }};"></div>
                                        </div>
                                        <span class="text-xs font-medium">{{ $utilization }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                    ${{ number_format($license->cost, 2) }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                    @if($license->renewal_date)
                                        @php
                                            // Badge colors based on remaining days:
                                            // Red: Expired (negative days)
                                            // Yellow: Expiring soon (0-7 days)
                                            // Green: Safe (more than 7 days)
                                            if ($remainingDays < 0) {
                                                $colorClass = 'bg-red-100 text-red-800';
                                                $daysText = abs($remainingDays) . 'd ago expired';
                                            } elseif ($remainingDays <= 7) {
                                                $colorClass = 'bg-yellow-100 text-yellow-800';
                                                $daysText = $remainingDays . 'd remaining';
                                            } else {
                                                $colorClass = 'bg-green-100 text-green-800';
                                                $daysText = $remainingDays . 'd remaining';
                                            }
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                            {{ $license->renewal_date->format('M d, Y') }}
                                            <span style="margin-left: 4px; opacity: 0.9;">({{ $daysText }})</span>
                                        </span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            {{-- Expanded Employee Details Row --}}
                            <tr x-show="expanded" x-cloak>
                                <td colspan="8" style="background-color: #f3f4f6; padding: 20px 24px;">
                                    <div style="margin-left: 20px;">
                                        <h5 style="font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 12px; display: flex; align-items: center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px; margin-right: 8px; color: #6366f1;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Assigned Employees ({{ $assigned }})
                                        </h5>
                                        @if($userLicenses && $userLicenses->count() > 0)
                                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
                                                @foreach($userLicenses as $ul)
                                                    @if($ul->employee)
                                                    <div style="display: flex; align-items: center; padding: 12px; background-color: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                                        <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; background-color: #e0e7ff; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                                            <span style="font-size: 14px; font-weight: 600; color: #4f46e5;">
                                                                {{ strtoupper(substr($ul->employee->first_name ?? 'N', 0, 1)) }}{{ strtoupper(substr($ul->employee->last_name ?? 'A', 0, 1)) }}
                                                            </span>
                                                        </div>
                                                        <div style="flex: 1; min-width: 0;">
                                                            <p style="font-size: 14px; font-weight: 500; color: #111827; margin: 0;">
                                                                {{ $ul->employee->full_name ?? 'N/A' }}
                                                            </p>
                                                            <p style="font-size: 12px; color: #6b7280; margin: 2px 0 0 0;">
                                                                {{ $ul->employee->department->name ?? 'N/A' }}
                                                                @if($ul->employee->email)
                                                                    &bull; {{ $ul->employee->email }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <p style="font-size: 14px; color: #6b7280; font-style: italic; padding: 20px; text-align: center; background-color: #fff; border-radius: 8px; border: 1px dashed #d1d5db;">
                                                No employees assigned to this license.
                                            </p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p>No licenses found matching the selected filters.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
            </table>
        </div>
    </div>

    {{-- Department Summary Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Department Summary</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Division</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Employees</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Licenses Assigned</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reportData['departmentDistribution'] as $dept)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $dept['name'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $dept['division'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $dept['city'] }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-900">{{ $dept['total_employees'] }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $dept['license_count'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">${{ number_format($dept['total_cost'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No department data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Generated At Footer --}}
    <div class="text-center text-sm text-gray-500 py-4">
        Report generated at: {{ $reportData['generatedAt']->format('F d, Y h:i A') }}
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function reportApp() {
    return {
        filters: {
            license_ids: @json($filters['license_ids'] ?? []),
            vendor_ids: @json($filters['vendor_ids'] ?? []),
            city_ids: @json($filters['city_ids'] ?? []),
            division_ids: @json($filters['division_ids'] ?? []),
            department_ids: @json($filters['department_ids'] ?? []),
            date_context: '{{ $filters['date_context'] ?? '' }}',
            date_from: '{{ $filters['date_from'] ?? '' }}',
            date_to: '{{ $filters['date_to'] ?? '' }}',
            renewal_status: @json($filters['renewal_status'] ?? []),
        },
        allDivisions: @json($filterOptions['divisions']),
        allDepartments: @json($filterOptions['departments']),
        filteredDivisions: [],
        filteredDepartments: [],
        dateContextError: false,
        dateRangeError: false,

        init() {
            this.updateDivisions();
            this.updateDepartments();
            this.initCharts();
            // Watch for date context changes to clear error
            this.$watch('filters.date_context', (value) => {
                if (value) {
                    this.dateContextError = false;
                }
            });
        },

        updateDivisions() {
            if (this.filters.city_ids.length === 0) {
                this.filteredDivisions = this.allDivisions;
            } else {
                this.filteredDivisions = this.allDivisions.filter(d =>
                    this.filters.city_ids.includes(String(d.city_id)) ||
                    this.filters.city_ids.includes(d.city_id)
                );
            }
            this.updateDepartments();
        },

        updateDepartments() {
            if (this.filters.division_ids.length === 0) {
                if (this.filters.city_ids.length === 0) {
                    this.filteredDepartments = this.allDepartments;
                } else {
                    this.filteredDepartments = this.allDepartments.filter(d =>
                        this.filters.city_ids.includes(String(d.city_id)) ||
                        this.filters.city_ids.includes(d.city_id)
                    );
                }
            } else {
                this.filteredDepartments = this.allDepartments.filter(d =>
                    this.filters.division_ids.includes(String(d.division_id)) ||
                    this.filters.division_ids.includes(d.division_id)
                );
            }
        },

        validateDateRange() {
            if (this.filters.date_from && this.filters.date_to) {
                this.dateRangeError = new Date(this.filters.date_from) > new Date(this.filters.date_to);
            } else {
                this.dateRangeError = false;
            }
            return !this.dateRangeError;
        },

        validateFilters() {
            // Check if date range is used without date context
            if ((this.filters.date_from || this.filters.date_to) && !this.filters.date_context) {
                this.dateContextError = true;
                return false;
            }
            this.dateContextError = false;

            // Validate date range
            if (!this.validateDateRange()) {
                return false;
            }

            return true;
        },

        resetFilters() {
            this.filters = {
                license_ids: [],
                vendor_ids: [],
                city_ids: [],
                division_ids: [],
                department_ids: [],
                date_context: '',
                date_from: '',
                date_to: '',
                renewal_status: [],
            };
            this.dateContextError = false;
            this.dateRangeError = false;
            this.updateDivisions();
            document.getElementById('reportForm').submit();
        },

        applyFilters() {
            if (this.validateFilters()) {
                document.getElementById('reportForm').submit();
            }
        },

        quickFilter(type) {
            this.filters = {
                license_ids: [],
                vendor_ids: [],
                city_ids: [],
                division_ids: [],
                department_ids: [],
                date_context: '',
                date_from: '',
                date_to: '',
                renewal_status: [],
            };
            this.dateContextError = false;
            this.dateRangeError = false;

            if (type === 'expiring') {
                this.filters.renewal_status = ['expiring_soon'];
            } else if (type === 'expired') {
                this.filters.renewal_status = ['expired'];
            } else if (type === 'active') {
                this.filters.renewal_status = ['active'];
            }
            this.updateDivisions();
            this.$nextTick(() => {
                document.getElementById('reportForm').submit();
            });
        },

        initCharts() {
            // Department Chart
            const deptData = @json($reportData['departmentDistribution']->take(10));
            const deptCtx = document.getElementById('departmentChart');
            if (deptCtx && deptData.length > 0) {
                new Chart(deptCtx, {
                    type: 'bar',
                    data: {
                        labels: deptData.map(d => {
                            let label = d.name;
                            let extra = [];
                            if (d.division) extra.push(d.division);
                            if (d.city) extra.push(d.city);
                            if (extra.length > 0) label += ' (' + extra.join(' - ') + ')';
                            return label;
                        }),
                        datasets: [{
                            label: 'Licenses Assigned',
                            data: deptData.map(d => d.license_count),
                            backgroundColor: 'rgba(99, 102, 241, 0.7)',
                            borderColor: 'rgba(99, 102, 241, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            // Vendor Chart
            const vendorData = @json($reportData['vendorDistribution']->take(10));
            const vendorCtx = document.getElementById('vendorChart');
            if (vendorCtx && vendorData.length > 0) {
                new Chart(vendorCtx, {
                    type: 'doughnut',
                    data: {
                        labels: vendorData.map(v => v.name),
                        datasets: [{
                            data: vendorData.map(v => v.total_assigned),
                            backgroundColor: [
                                'rgba(99, 102, 241, 0.7)',
                                'rgba(16, 185, 129, 0.7)',
                                'rgba(245, 158, 11, 0.7)',
                                'rgba(239, 68, 68, 0.7)',
                                'rgba(139, 92, 246, 0.7)',
                                'rgba(6, 182, 212, 0.7)',
                                'rgba(236, 72, 153, 0.7)',
                                'rgba(107, 114, 128, 0.7)',
                                'rgba(34, 197, 94, 0.7)',
                                'rgba(251, 146, 60, 0.7)',
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { boxWidth: 12, padding: 15 }
                            }
                        }
                    }
                });
            }
        }
    }
}
</script>
@endpush
