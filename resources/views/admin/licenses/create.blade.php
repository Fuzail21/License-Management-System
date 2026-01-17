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

            @auth
                @if(auth()->user()->isAdmin())
                    <div class="bg-green-50 border border-green-200 rounded-md p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">Immediate Approval</h3>
                                <div class="mt-1 text-sm text-green-700">
                                    <p>As an Admin, licenses you create will be immediately approved and active.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif(auth()->user()->isManager())
                    @if(auth()->user()->can_create_license)
                        <div class="bg-green-50 border border-green-200 rounded-md p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Immediate Approval</h3>
                                    <div class="mt-1 text-sm text-green-700">
                                        <p>You have permission to create licenses without admin approval. Your license will be immediately active.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Pending Admin Approval</h3>
                                    <div class="mt-1 text-sm text-yellow-700">
                                        <p>Your license request will be submitted for admin approval. It will not be active until an admin approves it.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endauth

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
                    <label for="renewal_type" class="block text-sm font-medium text-gray-700">Renewal Type <span class="text-red-500">*</span></label>
                    <div class="mt-1">
                        <select id="renewal_type" name="renewal_type" required
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                            <option value="subscription" {{ old('renewal_type') == 'subscription' ? 'selected' : '' }}>
                                Subscription</option>
                            <option value="perpetual" {{ old('renewal_type') == 'perpetual' ? 'selected' : '' }}>Perpetual
                            </option>
                        </select>
                    </div>
                    @error('renewal_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="renewal_cycle" class="block text-sm font-medium text-gray-700">Renewal Cycle</label>
                    <div class="mt-1">
                        <select id="renewal_cycle" name="renewal_cycle"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                            <option value="" {{ old('renewal_cycle') == '' ? 'selected' : '' }}>Select Cycle</option>
                            <option value="monthly" {{ old('renewal_cycle') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('renewal_cycle') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="yearly" {{ old('renewal_cycle') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            {{-- <option value="perpetual" {{ old('renewal_cycle') == 'perpetual' ? 'selected' : '' }}>Perpetual</option> --}}
                        </select>
                    </div>
                    @error('renewal_cycle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="version" class="block text-sm font-medium text-gray-700">Version / Type</label>
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
                        {{-- <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">{{ $appSetting->currency_symbol ?? '$' }}</span>
                        </div> --}}
                        <input type="number" name="cost" id="cost" value="{{ old('cost') }}" step="0.01" required min="0"
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md p-2 border"
                            placeholder="0.00">
                    </div>
                    @error('cost')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- <div>
                    <label for="number_license_assigned" class="block text-sm font-medium text-gray-700">Number of Licenses<span class="text-red-500">*</span></label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        {{-- <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">{{ $appSetting->currency_symbol ?? '$' }}</span>
                        </div> --}}
                        <input type="number" name="number_license_assigned" id="number_license_assigned" value="{{ old('number_license_assigned') }}" step="1" required min="1"
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md p-2 border"
                            placeholder="0.00">
                    </div>
                    @error('number_license_assigned')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div> -->

                <div>
                    <label for="renewal_date" class="block text-sm font-medium text-gray-700">Renewal Date</label>
                    <div class="mt-1">
                        <input type="date" name="renewal_date" id="renewal_date" value="{{ old('renewal_date') }}"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                    </div>
                    @error('renewal_date')
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
