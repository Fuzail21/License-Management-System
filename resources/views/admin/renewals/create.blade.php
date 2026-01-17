@extends('layouts.admin')

@section('title', 'Record License Renewal')
@section('page-title', 'Record License Renewal')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden max-w-2xl mx-auto">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Contract Renewal Details</h3>
            <p class="mt-1 text-sm text-gray-500">
                Renewing master contract for: <strong>{{ $license->license_name }}</strong>
            </p>
        </div>
        
        {{-- Submit to the store route which now expects the License id --}}
        <form action="{{ route('admin.renewals.store', $license->id) }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="bg-gray-50 p-4 rounded-md">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Current License Status</h4>
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <dt class="text-gray-500">License Type:</dt>
                    <dd class="text-gray-900 uppercase">{{ $license->renewal_type }}</dd>
                    
                    <dt class="text-gray-500">Total Licenses:</dt>
                    <dd class="text-gray-900">{{ $license->max_users ?? 'Unlimited' }}</dd>

                    <dt class="text-gray-500">Assigned Licenses:</dt>
                    <dd class="text-gray-900">{{ $license->userLicenses()->count() }}</dd>
                </dl>
            </div>

            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <div>
                    <label for="new_expiry_date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                    <div class="mt-1">
                        <input type="date" name="new_expiry_date" id="new_expiry_date" 
                            value="{{ old('new_expiry_date') }}"
                            required
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                    </div>
                    @error('new_expiry_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="renewal_cost" class="block text-sm font-medium text-gray-700">Total Renewal Cost</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <input type="number" name="renewal_cost" id="renewal_cost" 
                            value="{{ old('renewal_cost', $license->cost) }}" 
                            step="0.01" required
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
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
                        placeholder="Add details about this payment or contract extension..."
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">{{ old('notes') }}</textarea>
                </div>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.licenses.show', $license->id) }}"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Process Master Renewal
                </button>
            </div>
        </form>
    </div>
@endsection