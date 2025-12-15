@extends('layouts.admin')

@section('title', 'Assignment Details')
    <dd class="mt-1 text-sm text-gray-900">
        <a href="{{ route('admin.users.show', $userLicense->user) }}" class="text-indigo-600 hover:text-indigo-900">
            {{ $userLicense->user->name }}
        </a>
    </dd>
    </div>
    <div class="sm:col-span-1">
        <dt class="text-sm font-medium text-gray-500">License</dt>
        <dd class="mt-1 text-sm text-gray-900">
            <a href="{{ route('admin.licenses.show', $userLicense->license) }}"
                class="text-indigo-600 hover:text-indigo-900">
                {{ $userLicense->license->license_name }}
            </a>
        </dd>
    </div>
    <div class="sm:col-span-1">
        <dt class="text-sm font-medium text-gray-500">Vendor</dt>
        <dd class="mt-1 text-sm text-gray-900">{{ $userLicense->license->vendor->name }}</dd>
    </div>
    <div class="sm:col-span-1">
        <dt class="text-sm font-medium text-gray-500">Expiry Date</dt>
        <dd class="mt-1 text-sm text-gray-900">{{ $userLicense->expiry_date->format('M d, Y') }}</dd>
    </div>
    <div class="sm:col-span-1">
        <dt class="text-sm font-medium text-gray-500">Status</dt>
        <dd class="mt-1 text-sm text-gray-900">
            <x-status-badge :status="$userLicense->status->value" />
        </dd>
    </div>
    <div class="sm:col-span-1">
        <dt class="text-sm font-medium text-gray-500">Assigned On</dt>
        <dd class="mt-1 text-sm text-gray-900">{{ $userLicense->created_at->format('M d, Y') }}</dd>
    </div>
    </dl>
    </div>
    </div>
@endsection