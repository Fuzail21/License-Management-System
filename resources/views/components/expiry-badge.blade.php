@props(['expiryDate', 'status'])

@php
    if ($status === 'expired') {
        return;
    }

    // Using diffInDays with false as the second parameter returns a float/signed value
    // We wrap it in ceil() to ensure it's a whole number
    $daysRemaining = ceil(now()->diffInDays($expiryDate, false));
    $isExpiringSoon = $daysRemaining <= 30 && $daysRemaining >= 0;
@endphp

@if($isExpiringSoon)
    <span
        class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
        {{-- Casting to (int) removes any trailing decimals for display --}}
        Expiring in {{ (int)$daysRemaining }} days
    </span>
@endif