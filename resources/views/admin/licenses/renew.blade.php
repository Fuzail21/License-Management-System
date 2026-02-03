@extends('layouts.admin')

@section('title', 'Renew License')
@section('page-title', 'Renew License')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Form --}}
    <div class="lg:col-span-2">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Renew: {{ $license->license_name }}</h3>
                <p class="mt-1 text-sm text-gray-500">Update the renewal date with tracked history</p>
            </div>

            <form action="{{ route('admin.licenses.renew', $license) }}" method="POST" class="px-5 py-6">
                @csrf

                {{-- Current Information --}}
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Current License Status</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Current Renewal Date:</span>
                            <span class="ml-2 font-medium {{ $license->renewal_date && $license->renewal_date->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $license->renewal_date ? $license->renewal_date->format('M d, Y') : 'Not set' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">Days Remaining:</span>

                            @php
                                // Use startOfDay to ensure we are comparing whole days, not hours/minutes
                                $remainingDays = $license->renewal_date
                                    ? now()->startOfDay()->diffInDays(\Illuminate\Support\Carbon::parse($license->renewal_date)->startOfDay(), false)
                                    : null;
                            @endphp
                            
                            @if($remainingDays !== null)
                                <span class="ml-2 font-medium {{ $remainingDays < 0 ? 'text-red-600' : ($remainingDays <= 7 ? 'text-yellow-600' : 'text-green-600') }}">
                                    @if($remainingDays < 0)
                                        {{ abs($remainingDays) }} {{ abs($remainingDays) == 1 ? 'day' : 'days' }} overdue
                                    @elseif($remainingDays == 0)
                                        Due today
                                    @else
                                        {{ $remainingDays }} {{ $remainingDays == 1 ? 'day' : 'days' }}
                                    @endif
                                </span>
                            @else
                                <span class="ml-2 text-gray-400">N/A</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-500">License Cost:</span>
                            <span class="ml-2 font-medium text-gray-900">${{ number_format($license->cost, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Vendor:</span>
                            <span class="ml-2 font-medium text-gray-900">{{ $license->vendor->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Change Type --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-4">Change Type <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-green-500 transition">
                            <input type="radio" name="change_type" value="renewal" class="sr-only" checked>
                            <span class="flex flex-1 flex-col">
                                <span class="block text-sm font-medium text-gray-900">Renewal</span>
                                <span class="mt-1 text-xs text-gray-500">Standard renewal</span>
                            </span>
                            <svg class="h-5 w-5 text-green-600 hidden check-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-blue-500 transition">
                            <input type="radio" name="change_type" value="extension" class="sr-only">
                            <span class="flex flex-1 flex-col">
                                <span class="block text-sm font-medium text-gray-900">Extension</span>
                                <span class="mt-1 text-xs text-gray-500">Extend current period</span>
                            </span>
                            <svg class="h-5 w-5 text-blue-600 hidden check-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none hover:border-yellow-500 transition">
                            <input type="radio" name="change_type" value="correction" class="sr-only">
                            <span class="flex flex-1 flex-col">
                                <span class="block text-sm font-medium text-gray-900">Correction</span>
                                <span class="mt-1 text-xs text-gray-500">Fix incorrect date</span>
                            </span>
                            <svg class="h-5 w-5 text-yellow-600 hidden check-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </label>
                    </div>
                    @error('change_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Renewal Date --}}
                <div class="mb-4">
                    <label for="new_renewal_date" class="block text-sm font-medium text-gray-700 mb-1">
                        New Renewal Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="new_renewal_date" id="new_renewal_date"
                        value="{{ old('new_renewal_date', $license->renewal_date?->addYear()->format('Y-m-d')) }}"
                        min="{{ now()->addDay()->format('Y-m-d') }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        required>
                    @error('new_renewal_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Reason --}}
                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                        Reason for Change <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" id="reason" rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Enter the reason for this renewal/change..."
                        required>{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Renewal Cost --}}
                <div class="mb-6">
                    <label for="renewal_cost" class="block text-sm font-medium text-gray-700 mb-1">
                        Renewal Cost (Optional)($)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500"></span>
                        <input type="number" name="renewal_cost" id="renewal_cost"
                            value="{{ old('renewal_cost', $license->cost) }}"
                            step="0.01" min="0"
                            class="w-full pl-8 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="0.00">
                    </div>
                    @error('renewal_cost')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirmation Preview --}}
                <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                    <h4 class="text-sm font-medium text-indigo-800 mb-2">Change Preview</h4>
                    <div class="flex items-center text-sm">
                        <span class="text-gray-600">Old Date:</span>
                        <span class="ml-2 font-medium text-red-600">{{ $license->renewal_date ? $license->renewal_date->format('M d, Y') : 'Not set' }}</span>
                        <svg class="mx-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        <span class="text-gray-600">New Date:</span>
                        <span class="ml-2 font-medium text-green-600" id="preview-new-date">
                            {{ $license->renewal_date?->addYear()->format('M d, Y') ?? 'Select date' }}
                        </span>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.licenses.show', $license) }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 transition">
                        Confirm Renewal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar: Recent History --}}
    <div class="lg:col-span-1">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900">Recent History</h4>
            </div>
            <div class="px-5 py-4">
                @if($license->renewalHistories && $license->renewalHistories->count() > 0)
                <ul class="space-y-4">
                    @foreach($license->renewalHistories->take(5) as $history)
                    <li class="text-sm">
                        <div class="flex items-center gap-2">
                            @php
                                $badgeClass = match($history->change_type?->value ?? $history->change_type) {
                                    'renewal' => 'bg-green-100 text-green-800',
                                    'extension' => 'bg-blue-100 text-blue-800',
                                    'correction' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeClass }}">
                                {{ ucfirst($history->change_type?->value ?? $history->change_type ?? 'Update') }}
                            </span>
                            <span class="text-gray-500">{{ $history->changed_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-gray-600">
                            {{ $history->old_renewal_date ? $history->old_renewal_date->format('M d, Y') : 'N/A' }}
                            →
                            {{ $history->new_renewal_date->format('M d, Y') }}
                        </p>
                        @if($history->changedByUser)
                        <p class="text-xs text-gray-400">by {{ $history->changedByUser->name }}</p>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-sm text-gray-500 text-center py-4">No history yet</p>
                @endif
            </div>
        </div>

        @if(isset($renewalStats))
        <div class="mt-4 bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900">Statistics</h4>
            </div>
            <div class="px-5 py-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Renewals:</span>
                    <span class="font-medium">{{ $renewalStats['total_renewals'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Extensions:</span>
                    <span class="font-medium">{{ $renewalStats['total_extensions'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Cost Spent:</span>
                    <span class="font-medium">${{ number_format($renewalStats['total_cost'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle radio button styling
    const radioLabels = document.querySelectorAll('input[name="change_type"]');
    radioLabels.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove selected styling from all
            document.querySelectorAll('input[name="change_type"]').forEach(r => {
                r.closest('label').classList.remove('border-green-500', 'border-blue-500', 'border-yellow-500', 'ring-2');
                r.closest('label').querySelector('.check-icon').classList.add('hidden');
            });
            // Add selected styling
            const colors = { renewal: 'green', extension: 'blue', correction: 'yellow' };
            this.closest('label').classList.add(`border-${colors[this.value]}-500`, 'ring-2');
            this.closest('label').querySelector('.check-icon').classList.remove('hidden');
        });
        // Trigger for initially checked
        if (radio.checked) radio.dispatchEvent(new Event('change'));
    });

    // Update preview date
    const dateInput = document.getElementById('new_renewal_date');
    const previewDate = document.getElementById('preview-new-date');
    dateInput.addEventListener('change', function() {
        if (this.value) {
            const date = new Date(this.value);
            previewDate.textContent = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }
    });
});
</script>
@endpush
@endsection
