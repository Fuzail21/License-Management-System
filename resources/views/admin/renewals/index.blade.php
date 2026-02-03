@extends('layouts.admin')

@section('title', 'License Renewals')
@section('page-title', 'License Renewals')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">License Renewal Tracking</h3>
            <p class="text-sm text-gray-500">Licenses sorted by renewal urgency (nearest first)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            License Name
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vendor
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Assigned Users
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Renewal Date
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Remaining Days
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($licenses as $license)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.licenses.show', $license) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $license->license_name }}
                                </a>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                {{ $license->vendor->name ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $license->user_licenses_count }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                {{ $license->renewal_date ? $license->renewal_date->format('M d, Y') : 'Not set' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                @if($license->renewal_date)
                                    @php
                                        // 1. Convert the string/date into a Carbon object
                                        $renewalDate = \Illuminate\Support\Carbon::parse($license->renewal_date)->startOfDay();

                                        // 2. Compare it to 'now' to get the actual INTEGER count of days
                                        // The 'false' parameter allows negative numbers if the date has passed
                                        $remainingDays = now()->startOfDay()->diffInDays($renewalDate, false);
                                    @endphp
                                    @if($remainingDays < 0)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800"
                                              title="Renewal date: {{ $license->renewal_date->format('M d, Y') }}">
                                            Expired ({{ abs($remainingDays) }} days ago)
                                        </span>
                                    @elseif($remainingDays <= 7)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold animate-pulse bg-yellow-100 text-yellow-800"                                             title="Renewal date: {{ $license->renewal_date->format('M d, Y') }}">
                                            {{ $remainingDays }} {{ $remainingDays == 1 ? 'day' : 'days' }} - Expiring Soon
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800"
                                              title="Renewal date: {{ $license->renewal_date->format('M d, Y') }}">
                                            {{ $remainingDays }} days remaining
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-300 text-gray-700">
                                        No date set
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center justify-center space-x-3">
                                    <a href="{{ route('admin.licenses.show', $license) }}"
                                       class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200"
                                       title="View License">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-5 w-5"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.licenses.renew.form', $license) }}"
                                       class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                       title="Renew License">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-5 w-5"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 whitespace-nowrap text-sm text-gray-500 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p>No approved licenses found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <div class="px-5 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-wrap items-center gap-4 text-xs">
                <span class="font-medium text-gray-700">Legend:</span>
                <span class="inline-flex items-center bg-red-100 text-red-800 px-2.5 py-0.5 rounded-full">
                    <span class="w-3 h-3 rounded-full mr-1.5"></span>
                    Expired
                </span>
                <span class="inline-flex items-center bg-yellow-100 text-yellow-800 px-2.5 py-0.5 rounded-full">
                    <span class="w-3 h-3 rounded-full mr-1.5"></span>
                    Warning (0-7 days)
                </span>
                <span class="inline-flex items-center bg-green-100 text-green-800 px-2.5 py-0.5 rounded-full">
                    <span class="w-3 h-3 rounded-full mr-1.5"></span>
                    Safe (7+ days)
                </span>
            </div>
        </div> 
    </div>
@endsection

