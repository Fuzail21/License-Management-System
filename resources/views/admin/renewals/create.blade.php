@extends('layouts.admin')

@section('title', 'Record Renewal')
@section('page-title', 'Record Renewal')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden max-w-2xl mx-auto">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Renewal Details</h3>
        </div>
        <form action="{{ route('admin.renewals.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div>
                <label for="license_id" class="block text-sm font-medium text-gray-700">License</label>
                <div class="mt-1">
                    <select id="license_id" name="license_id" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        <option value="">Select License</option>
                        @foreach($licenses as $license)
                            <option value="{{ $license->id }}" {{ old('license_id') == $license->id ? 'selected' : '' }}>
                                {{ $license->license_name }} ({{ $license->vendor->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('license_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <div>
                    <label for="renewal_date" class="block text-sm font-medium text-gray-700">Renewal Date</label>
                    <div class="mt-1">
                        <input type="date" name="renewal_date" id="renewal_date" value="{{ old('renewal_date') }}" required
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                    </div>
                    @error('renewal_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

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
                    <label for="cost" class="block text-sm font-medium text-gray-700">Renewal Cost</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="cost" id="cost" value="{{ old('cost') }}" step="0.01" required
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md p-2 border"
                            placeholder="0.00">
                    </div>
                    @error('cost')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <div class="mt-1">
                    <textarea id="notes" name="notes" rows="3"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">{{ old('notes') }}</textarea>
                </div>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.renewals.index') }}"
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