@extends('layouts.admin')

@section('title', 'Assignment Details')
@section('page-title', 'Assignment Details')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">License Assignment Details</h3>
            <div class="flex gap-3">
                <a href="{{ route('admin.user-licenses.edit', $userLicense) }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            </div>
        </div>

        <div class="px-5 py-4">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Employee</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('admin.employees.show', $userLicense->employee) }}" class="text-indigo-600 hover:text-indigo-900">
                            {{ $userLicense->employee->first_name }} {{ $userLicense->employee->last_name }}
                        </a>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $userLicense->employee->email }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Department</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($userLicense->employee->department)
                            <a href="{{ route('admin.departments.show', $userLicense->employee->department) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $userLicense->employee->department->name }}
                            </a>
                        @else
                            <span class="text-gray-400">No department</span>
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">License</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('admin.licenses.show', $userLicense->license) }}"
                            class="text-indigo-600 hover:text-indigo-900">
                            {{ $userLicense->license->license_name }}
                        </a>
                    </dd>
                </div>
                {{-- <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Vendor</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('admin.vendors.show', $userLicense->license->vendor) }}" class="text-indigo-600 hover:text-indigo-900">
                            {{ $userLicense->license->vendor->name }}
                        </a>
                    </dd>
                </div> --}}
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Renewal Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($userLicense->license->renewal_type) }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Assigned Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $userLicense->assigned_date->format('M d, Y') }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <x-status-badge :status="$userLicense->status->value" />
                    </dd>
                </div>
                @if($userLicense->renewal_cost)
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Renewal Cost</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $appSetting->currency_symbol ?? '$' }}{{ number_format($userLicense->renewal_cost, 2) }}
                    </dd>
                </div>
                @endif
                @if($userLicense->renewed_at)
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Last Renewed</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $userLicense->renewed_at->format('M d, Y') }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- @if($userLicense->renewals->count() > 0)
        <div class="px-5 py-4 border-t border-gray-200">
            <h4 class="text-md font-medium text-gray-900 mb-4">Renewal History</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Renewal Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Old Expiry</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">New Expiry</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Renewed By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($userLicense->renewals as $renewal)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $renewal->renewed_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $renewal->old_expiry_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $renewal->new_expiry_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $appSetting->currency_symbol ?? '$' }}{{ number_format($renewal->renewal_cost, 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($renewal->renewer)
                                    {{ $renewal->renewer->name }}
                                @else
                                    <span class="text-gray-400">Unknown</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif --}}
    </div>
@endsection
