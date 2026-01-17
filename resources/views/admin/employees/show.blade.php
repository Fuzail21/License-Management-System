@extends('layouts.admin')

@section('title', 'Employee Details')
@section('page-title', 'Employee Details')

@section('content')
    <div class="space-y-6">
        {{-- Employee Information --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Employee Information</h3>
                @can('update', $employee)
                    <a href="{{ route('admin.employees.edit', $employee) }}"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                @endcan
            </div>

            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Employee Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->employee_number ?? 'N/A' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->first_name }} {{ $employee->last_name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->email ?? 'N/A' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->phone ?? 'N/A' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Department</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($employee->department)
                                <a href="{{ route('admin.departments.show', $employee->department) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $employee->department->name }}
                                </a>
                            @else
                                <span class="text-gray-400">No department assigned</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Division</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($employee->department && $employee->department->division)
                                <a href="{{ route('admin.divisions.show', $employee->department->division) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $employee->department->division->name }}
                                </a>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">City</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($employee->department && $employee->department->division && $employee->department->division->city)
                                {{ $employee->department->division->city->name }}
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Job Title</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->job_title ?? 'N/A' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Hire Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @if ($employee->status === \App\Models\EmployeeStatus::Active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->created_at->format('M d, Y H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $employee->updated_at->format('M d, Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

    @if ($employee->userLicenses->count() > 0)
    <div class="mt-8 bg-white shadow rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Assigned Licenses</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($employee->userLicenses as $employeeLicense)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.user-licenses.show', $employeeLicense) }}"
                                    class="text-indigo-600 hover:text-indigo-900">
                                    {{ $employeeLicense->license->license_name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a href="{{ route('admin.vendors.show', $employeeLicense->license->vendor) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $employeeLicense->license->vendor->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <x-status-badge :status="$employeeLicense->status->value" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.user-licenses.show', $employeeLicense) }}"
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
    @else
    <div class="mt-8 bg-white shadow rounded-lg overflow-hidden">
        <div class="px-5 py-4">
            <p class="text-sm text-gray-500 text-center">No licenses assigned to this employee.</p>
        </div>
    </div>
    @endif
@endsection
