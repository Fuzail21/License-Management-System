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
                <a href="{{ route('admin.licenses.renew.form', $license) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg text-green-800 bg-green-100 hover:bg-green-200 transition">
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

    {{-- Renewal History Section --}}
    <div class="mt-8 bg-white shadow rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Renewal History</h3>
                    <p class="mt-1 text-sm text-gray-500">Track all changes to the license renewal date</p>
                </div>
                @if(isset($renewalStats))
                <div class="flex gap-4 text-sm">
                    <div class="text-center px-3 py-1 bg-green-50 rounded">
                        <span class="block font-semibold text-green-700">{{ $renewalStats['total_renewals'] }}</span>
                        <span class="text-green-600 text-xs">Renewals</span>
                    </div>
                    <div class="text-center px-3 py-1 bg-blue-50 rounded">
                        <span class="block font-semibold text-blue-700">{{ $renewalStats['total_extensions'] }}</span>
                        <span class="text-blue-600 text-xs">Extensions</span>
                    </div>
                    <div class="text-center px-3 py-1 bg-yellow-50 rounded">
                        <span class="block font-semibold text-yellow-700">{{ $renewalStats['total_corrections'] }}</span>
                        <span class="text-yellow-600 text-xs">Corrections</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if($license->renewalHistories && $license->renewalHistories->count() > 0)
        <div class="px-5 py-4">
            {{-- Timeline View --}}
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($license->renewalHistories as $index => $history)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    @php
                                        $iconBg = match($history->change_type?->value ?? $history->change_type) {
                                            'renewal' => 'bg-green-500',
                                            'extension' => 'bg-blue-500',
                                            'correction' => 'bg-yellow-500',
                                            'initial' => 'bg-gray-400',
                                            default => 'bg-gray-400',
                                        };
                                    @endphp
                                    <span class="h-8 w-8 rounded-full {{ $iconBg }} flex items-center justify-center ring-8 ring-white">
                                        @if(($history->change_type?->value ?? $history->change_type) === 'renewal')
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        @elseif(($history->change_type?->value ?? $history->change_type) === 'extension')
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        @elseif(($history->change_type?->value ?? $history->change_type) === 'correction')
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        @else
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm text-gray-900">
                                            <span class="font-medium">
                                                {{ ucfirst($history->change_type?->value ?? $history->change_type ?? 'Update') }}
                                            </span>
                                            @if($history->old_renewal_date)
                                                - Changed from
                                                <span class="font-medium text-red-600">{{ $history->old_renewal_date->format('M d, Y') }}</span>
                                                to
                                            @else
                                                - Set to
                                            @endif
                                            <span class="font-medium text-green-600">{{ $history->new_renewal_date->format('M d, Y') }}</span>
                                        </p>
                                        @if($history->reason)
                                        <p class="mt-1 text-sm text-gray-500">
                                            <span class="font-medium">Reason:</span> {{ $history->reason }}
                                        </p>
                                        @endif
                                        @if($history->renewal_cost)
                                        <p class="mt-1 text-sm text-gray-500">
                                            <span class="font-medium">Cost:</span> ${{ number_format($history->renewal_cost, 2) }}
                                        </p>
                                        @endif
                                    </div>
                                    <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                        <div>{{ $history->changed_at->format('M d, Y') }}</div>
                                        <div class="text-xs">{{ $history->changed_at->format('h:i A') }}</div>
                                        @if($history->changedByUser)
                                        <div class="text-xs text-gray-400 mt-1">by {{ $history->changedByUser->name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @else
        <div class="px-5 py-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500">No renewal history recorded yet.</p>
            <p class="text-xs text-gray-400">History will be tracked when the renewal date is updated.</p>
        </div>
        @endif
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
