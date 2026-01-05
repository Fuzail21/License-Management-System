@extends('layouts.admin')

@section('title', 'Edit Assignment')
@section('page-title', 'Edit Assignment')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden max-w-2xl mx-auto">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Assignment</h3>
        </div>
        <form action="{{ route('admin.user-licenses.update', $userLicense) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <input type="hidden" name="employee_id" value="{{ $userLicense->employee_id }}">
            <input type="hidden" name="license_id" value="{{ $userLicense->license_id }}">

            <div>
                <label class="block text-sm font-medium text-gray-700">Employee</label>
                <div class="mt-1 p-2 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                    {{ $userLicense->employee->name }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">License</label>
                <div class="mt-1 p-2 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                    {{ $userLicense->license->license_name }}
                </div>
            </div>
            <div>
                <label for="assigned_date" class="block text-sm font-medium text-gray-700">
                    Assigned Date
                </label>
                <div class="mt-1">
                    <input
                        type="date"
                        name="assigned_date"
                        id="assigned_date"
                        value="{{ old('assigned_date', $userLicense->assigned_date->format('Y-m-d')) }}"
                        readonly
                        class="shadow-sm bg-gray-100 cursor-not-allowed
                               focus:ring-0 focus:border-gray-300
                               block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <div class="mt-1">
                        <select id="status" name="status" required
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                            <option value="active" {{ old('status', $userLicense->status->value) == 'active' ? 'selected' : '' }}>
                                Active</option>
                            <option value="expired" {{ old('status', $userLicense->status->value) == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="suspended" {{ old('status', $userLicense->status->value) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.user-licenses.index') }}"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Assignment
                </button>
            </div>
        </form>
    </div>
@endsection