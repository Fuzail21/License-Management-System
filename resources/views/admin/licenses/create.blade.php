@extends('layouts.admin')

@section('title', 'Create License')
@section('page-title', 'Create License')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden max-w-2xl mx-auto">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">License Details</h3>
        </div>
        <form action="{{ route('admin.licenses.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div>
                <label for="license_name" class="block text-sm font-medium text-gray-700">License Name <span class="text-red-500">*</span></label>
                <div class="mt-1">
                    <input type="text" name="license_name" id="license_name" value="{{ old('license_name') }}" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
                @error('license_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="vendor_id" class="block text-sm font-medium text-gray-700">Vendor <span class="text-red-500">*</span></label>
                <div class="mt-1">
                    <select id="vendor_id" name="vendor_id" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        <option value="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('vendor_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <div>
                    <label for="license_type" class="block text-sm font-medium text-gray-700">License Type <span class="text-red-500">*</span></label>
                    <div class="mt-1">
                        <select id="license_type" name="license_type" required
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                            <option value="subscription" {{ old('license_type') == 'subscription' ? 'selected' : '' }}>
                                Subscription</option>
                            <option value="perpetual" {{ old('license_type') == 'perpetual' ? 'selected' : '' }}>Perpetual
                            </option>
                        </select>
                    </div>
                    @error('license_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="version" class="block text-sm font-medium text-gray-700">Version</label>
                    <div class="mt-1">
                        <input type="text" name="version" id="version" value="{{ old('version') }}"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                            placeholder="e.g., 1.0.0">
                    </div>
                    @error('version')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="max_users" class="block text-sm font-medium text-gray-700">Max Users</label>
                    <div class="mt-1">
                        <input type="number" name="max_users" id="max_users" value="{{ old('max_users') }}" min="1"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                    </div>
                    @error('max_users')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cost" class="block text-sm font-medium text-gray-700">Cost <span class="text-red-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">{{ $appSetting->currency_symbol ?? '$' }}</span>
                        </div>
                        <input type="number" name="cost" id="cost" value="{{ old('cost') }}" step="0.01" required min="0"
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md p-2 border"
                            placeholder="0.00">
                    </div>
                    @error('cost')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <div class="mt-1">
                    <textarea id="description" name="description" rows="3"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">{{ old('description') }}</textarea>
                </div>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.licenses.index') }}"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create License
                </button>
            </div>
        </form>
    </div>
@endsection
