@props(['expiryDate', 'status'])

@php
    if (!$expiryDate) return;

    $expiryCarbon = $expiryDate instanceof \Illuminate\Support\Carbon
        ? $expiryDate
        : \Illuminate\Support\Carbon::parse($expiryDate);

    // Calculate days: (Target Date) - (Today)
    // Result is positive if in the future, negative if in the past
    $daysRemaining = (int) \Illuminate\Support\Carbon::today()->diffInDays($expiryCarbon, false);
    
    // Determine the style and text based on the day count
    if ($daysRemaining < 0) {
        $colorClass = 'bg-red-100 text-red-800';
        $absDays = abs($daysRemaining);
        $label = $absDays . ($absDays === 1 ? ' day' : ' days') . ' ago - Expired';
    } elseif ($daysRemaining <= 7) {
        $colorClass = 'bg-yellow-100 text-yellow-800';
        $label = $daysRemaining . ' days - Expiring Soon';
    } else {
        $colorClass = 'bg-green-100 text-green-800';
        $label = $daysRemaining . ' days remaining';
    }
@endphp

<span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
    {{ $label }}
</span>