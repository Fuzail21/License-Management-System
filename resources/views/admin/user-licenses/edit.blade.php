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

            <div>
                <label class="block text-sm font-medium text-gray-700">User</label>
                <div class="mt-1 p-2 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                    {{ $userLicense->user->name }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">License</label>
                <div class="mt-1 p-2 bg-gray-50 border border-gray-300 rounded-md text-gray-700">
                    {{ $userLicense->license->license_name }}
                </div>
            </div>

            <div>
                <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                <div class="mt-1">
                    <input type="date" name="expiry_date" id="expiry_date"
                        value="{{ old('expiry_date', $userLicense->expiry_date->format('Y-m-d')) }}" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
                @error('expiry_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <div class="mt-1">
                    <select id="status" name="status" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        <option value="active" {{ old('status', $userLicense->status->value) == 'active' ? 'selected' : '' }}>
                            Active</option>
                        <option value="inactive" {{ old('status', $userLicense->status->value) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ old('status', $userLicense->status->value) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
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