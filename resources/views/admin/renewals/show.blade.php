@extends('layouts.admin')

@section('title', 'Renewal Details')
    <dd class="mt-1 text-sm text-gray-900">
        <a href="{{ route('admin.licenses.show', $renewal->license) }}" class="text-indigo-600 hover:text-indigo-900">
            {{ $renewal->license->license_name }}
        </a>
    </dd>
    </div>
    <div class="sm:col-span-1">
        <dt class="text-sm font-medium text-gray-500">Vendor</dt>
        <dd class="mt-1 text-sm text-gray-900">{{ $renewal->license->vendor->name }}</dd>
    </div>
    <div class="sm:col-span-1">
        <dt class="text-sm font-medium text-gray-500">Renewal Date</dt>
        <dd class="mt-1 text-sm text-gray-900">{{ $renewal->renewal_date->format('M d, Y') }}</dd>
    </div>
    <div class="sm:col-span-1">
        <dt class="text-sm font-medium text-gray-500">New Expiry Date</dt>
        <dd class="mt-1 text-sm text-gray-900">{{ $renewal->new_expiry_date->format('M d, Y') }}</dd>
    </div>
    <div class="sm:col-span-1">
        <dt class="text-sm font-medium text-gray-500">Cost</dt>
        <dd class="mt-1 text-sm text-gray-900">${{ number_format($renewal->cost, 2) }}</dd>
    </div>
    <div class="sm:col-span-1">
        <dt class="text-sm font-medium text-gray-500">Recorded By</dt>
        <dd class="mt-1 text-sm text-gray-900">{{ $renewal->user->name ?? 'Unknown' }}</dd>
    </div>
    <div class="sm:col-span-2">
        <dt class="text-sm font-medium text-gray-500">Notes</dt>
        <dd class="mt-1 text-sm text-gray-900">{{ $renewal->notes ?? 'N/A' }}</dd>
    </div>
    </dl>
    </div>
    </div>
@endsection