@extends('layouts.admin')

@section('title', 'License Details')
@section('page-title', 'License Details')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <!-- Left: Title -->
            <h3 class="text-lg font-semibold text-gray-900">
                License Information
            </h3>

            <!-- Right: Buttons -->
            <div class="flex items-center gap-3">
                <!-- Edit Button -->
                <a href="{{ route('admin.licenses.edit', $license) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>

                <!-- Renew Button -->
                <a href="{{ route('admin.renewals.create', $license->id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg text-black border border-black border-solid">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Renew License
                </a>
            </div>
        </div>

        <div class="px-5 py-4">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">License Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $license->license_name }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Vendor</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('admin.vendors.show', $license->vendor) }}"
                            class="text-indigo-600 hover:text-indigo-900">
                            {{ $license->vendor->name }}
                        </a>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Renewal Type</dt>
                    <dd class="mt-1 text-sm text-gray-900 capitalize">{{ $license->renewal_type }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Renewal Cycle</dt>
                    <dd class="mt-1 text-sm text-gray-900 capitalize">
                        {{ $license->renewal_cycle ?? 'N/A' }}
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Version</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $license->version ?? 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Cost</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $appSetting->currency_symbol ?? '$' }}{{ number_format($license->cost, 2) }}
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Max Employees</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $license->max_users ?? 'Unlimited' }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Number of Licenses Assigned</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $license->userLicenses()->count() }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Renewal Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($license->renewal_date)
                            {{ $license->renewal_date->format('M d, Y') }}
                            @if($license->renewal_date->isPast())
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Overdue
                                </span>
                            @elseif($license->renewal_date->diffInDays(now()) <= 30)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Due Soon
                                </span>
                            @endif
                        @else
                            N/A
                        @endif
                    </dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $license->description ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    @if($license->userLicenses->count() > 0)
    <div class="mt-8 bg-white shadow rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Assigned Employees ({{ $license->userLicenses->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($license->userLicenses as $userLicense)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.employees.show', $userLicense->employee) }}"
                                    class="text-indigo-600 hover:text-indigo-900">
                                    {{ $userLicense->employee->first_name }} {{ $userLicense->employee->last_name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $userLicense->employee->department->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <x-status-badge :status="$userLicense->status->value" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.user-licenses.show', $userLicense) }}"
                                    class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endsection
