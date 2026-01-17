@extends('layouts.admin')

@section('title', 'Renewals')
@section('page-title', 'Renewals')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Renewal History</h3>
            <p class="text-sm text-gray-500">To record a renewal, go to a License detail page and click "Renew License"</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">License
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor
                        </th>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Renewal
                            Date</th>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry</th>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        <th scope="col"
                            class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($renewals as $renewal)
                        <tr>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.licenses.show', $renewal->license) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $renewal->license->license_name }}
                                </a>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                {{ $renewal->license->vendor->name }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                {{ $renewal->renewed_at->format('M d, Y') }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                {{ $renewal->new_expiry_date->format('M d, Y') }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                {{ $appSetting->currency_symbol ?? '$' }}{{ number_format($renewal->renewal_cost, 2) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('admin.licenses.show', $renewal->license) }}"
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No renewal
                                records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-200">
            {{ $renewals->links() }}
        </div>
    </div>
@endsection