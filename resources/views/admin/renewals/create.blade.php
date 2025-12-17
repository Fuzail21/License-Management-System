@extends('layouts.admin')

@section('title', 'Record Renewal')
@section('page-title', 'Record Renewal')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden max-w-2xl mx-auto">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Renewal Details</h3>
            <p class="mt-1 text-sm text-gray-500">
                Renewing license for <strong>{{ $userLicense->user->name }}</strong> - 
                <strong>{{ $userLicense->license->license_name }}</strong>
            </p>
        </div>
        <form action="{{ route('admin.renewals.store', $userLicense) }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="bg-gray-50 p-4 rounded-md">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Current License Information</h4>
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <dt class="text-gray-500">Current Expiry:</dt>
                    <dd class="text-gray-900">{{ $userLicense->expiry_date->format('M d, Y') }}</dd>
                    <dt class="text-gray-500">Status:</dt>
                    <dd class="text-gray-900">
                        <x-status-badge :status="$userLicense->status->value" />
                    </dd>
                </dl>
            </div>

            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <div>
                    <label for="new_expiry_date" class="block text-sm font-medium text-gray-700">New Expiry Date</label>
                    <div class="mt-1">
                        <input type="date" name="new_expiry_date" id="new_expiry_date" value="{{ old('new_expiry_date') }}"
                            required
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                    </div>
                    @error('new_expiry_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="renewal_cost" class="block text-sm font-medium text-gray-700">Renewal Cost</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        {{-- <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">{{ $appSetting->currency_symbol ?? '$' }}</span>
                        </div> --}}
                        <input type="number" name="renewal_cost" id="renewal_cost" value="{{ old('renewal_cost') }}" step="0.01" required
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md p-2 border"
                            placeholder="0.00">
                    </div>
                    @error('renewal_cost')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                <div class="mt-1">
                    <textarea id="notes" name="notes" rows="3"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">{{ old('notes') }}</textarea>
                </div>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.user-licenses.show', $userLicense) }}"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Record Renewal
                </button>
            </div>
        </form>
    </div>
@endsection
