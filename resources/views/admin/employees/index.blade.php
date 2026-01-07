@extends('layouts.admin')

@section('title', 'Employees')
@section('page-title', 'Employees')

@section('content')
    <div class="space-y-6">
        {{-- Filters --}}
        <div class="bg-white shadow rounded-lg p-6">
            <form method="GET" action="{{ route('admin.employees.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Search by name, email, employee number..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                {{-- Division Filter --}}
                <div>
                    <label for="division_id" class="block text-sm font-medium text-gray-700">Division</label>
                    <select name="division_id" id="division_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Divisions</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                {{-- Submit Buttons --}}
                <div class="md:col-span-4 flex justify-end gap-3">
                    <a href="{{ route('admin.employees.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Clear
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        {{-- Employees Table --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Employees</h3>

                    {{-- Bulk Actions --}}
                    <div id="bulkActions" class="hidden">
                        @can('bulkDelete', App\Models\Employee::class)
                            <button type="button" onclick="confirmBulkDelete()"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700">
                                <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Selected
                            </button>
                        @endcan
                    </div>
                </div>

                <div class="flex gap-2">
                    {{-- Export Button --}}
                    <!-- <a href="{{ route('admin.employees.export', request()->query()) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </a> -->

                    @can('create', App\Models\Employee::class)
                        <a href="{{ route('admin.employees.create') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Employee
                        </a>
                    @endcan
                </div>
            </div>

            {{-- Bulk Delete Form --}}
            <form id="bulkDeleteForm" action="{{ route('admin.employees.bulk-delete') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="employee_ids" id="bulkDeleteIds">
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            {{-- Bulk Select Checkbox --}}
                            @can('bulkDelete', App\Models\Employee::class)
                                <th scope="col" class="px-6 py-3 text-left">
                                    <input type="checkbox" id="selectAll"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        onclick="toggleSelectAll(this)">
                                </th>
                            @endcan
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Employee #</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Division</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Job Title</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-gray-50">
                                {{-- Bulk Select Checkbox --}}
                                @can('bulkDelete', App\Models\Employee::class)
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" class="employee-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            value="{{ $employee->id }}" onclick="toggleBulkActions()">
                                    </td>
                                @endcan
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $employee->employee_number ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $employee->email ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $employee->division->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $employee->job_title ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($employee->status === \App\Models\EmployeeStatus::Active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end items-center gap-3">
                                        @can('view', $employee)
                                            <a href="{{ route('admin.employees.show', $employee) }}"
                                                class="text-gray-600 hover:text-gray-900 transition-colors duration-200" title="View">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        @endcan
                                        @can('update', $employee)
                                            <a href="{{ route('admin.employees.edit', $employee) }}"
                                                class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200"
                                                title="Edit">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        @endcan
                                        @can('delete', $employee)
                                            <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="inline-block"
                                                onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                    title="Delete">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No employees found</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new employee.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employees->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Bulk Operations JavaScript --}}
    <script>
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.employee-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            toggleBulkActions();
        }

        function toggleBulkActions() {
            const checkboxes = document.querySelectorAll('.employee-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectAll = document.getElementById('selectAll');

            if (checkboxes.length > 0) {
                bulkActions.classList.remove('hidden');
            } else {
                bulkActions.classList.add('hidden');
            }

            // Update "select all" checkbox state
            const allCheckboxes = document.querySelectorAll('.employee-checkbox');
            if (allCheckboxes.length > 0) {
                selectAll.checked = checkboxes.length === allCheckboxes.length;
                selectAll.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
            }
        }

        function confirmBulkDelete() {
            const checkboxes = document.querySelectorAll('.employee-checkbox:checked');
            const count = checkboxes.length;

            if (count === 0) {
                alert('Please select at least one employee to delete.');
                return;
            }

            if (confirm(`Are you sure you want to delete ${count} employee(s)? This action cannot be undone.`)) {
                const ids = Array.from(checkboxes).map(cb => cb.value);
                document.getElementById('bulkDeleteIds').value = JSON.stringify(ids);
                document.getElementById('bulkDeleteForm').submit();
            }
        }
    </script>
@endsection
