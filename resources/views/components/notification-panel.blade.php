@php
    use App\Models\License;

    // Get pending licenses
    $displayCount = License::where('status', 'pending')->count();

    // Check if user is admin
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
@endphp

@if($isAdmin)
<div x-data="{ open: false }" class="relative">
    <!-- Bell Button -->
    <button
        @click="open = !open"
        style="
            position: relative;
            padding: 8px;
            border-radius: 50%;
            outline: none;
            border: none;
            background: transparent;
            cursor: pointer;
            color: #6b7280;
            box-shadow: 0 0 0 2px #6366f1, 0 0 0 4px #e0e7ff;
            transition: color 0.2s ease, box-shadow 0.2s ease;
        "
        onmouseover="this.style.color='#374151'"
        onmouseout="this.style.color='#6b7280'"
    >
        <!-- Bell Icon -->
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Notification Badge -->
        @if($displayCount > 0)
            <span
                style="
                    position: absolute;
                    top: -8px;
                    right: -4px;
                    min-width: 20px;
                    height: 20px;
                    padding: 2px 5px;
                    font-size: 12px;
                    font-weight: bold;
                    color: #ffffff;
                    background-color: red;
                    border-radius: 9999px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    line-height: 1;
                ">
                {{ $displayCount > 99 ? '99+' : $displayCount }}
            </span>
        @endif
    </button>



    <!-- Dropdown Panel -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         @click.outside="open = false"
         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 z-50"
         style="display: none;">

        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Pending Approvals</h3>
                @if($displayCount > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                        {{ $displayCount }} pending
                    </span>
                @endif
            </div>
        </div>

        <!-- Notification List -->
        <div class="max-h-80 overflow-y-auto">
            @forelse($licenses as $license)
                <div class="px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-b-0">
                    <div class="flex items-start gap-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0 w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ $license->license_name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                by {{ $license->creator->name ?? 'Unknown' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $license->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <!-- Status -->
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                            Pending
                        </span>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="px-4 py-8 text-center">
                    <div class="w-12 h-12 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-900">All caught up!</p>
                    <p class="text-xs text-gray-500 mt-1">No pending license approvals</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 rounded-b-lg">
            <a href="{{ route('admin.licenses.pending') }}"
               @click="open = false"
               class="block w-full text-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                View all pending approvals
            </a>
        </div>
    </div>
</div>
@endif
