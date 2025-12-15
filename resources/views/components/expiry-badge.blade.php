@props(['expiryDate', 'status'])

@php
    if ($status === 'expired') {
        return;
    }

    $daysRemaining = now()->diffInDays($expiryDate, false);
    $isExpiringSoon = $daysRemaining <= 30 && $daysRemaining >= 0;
@endphp

@if($isExpiringSoon)
    <span
        class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
        Expiring in {{ $daysRemaining }} days
    </span>
@endif