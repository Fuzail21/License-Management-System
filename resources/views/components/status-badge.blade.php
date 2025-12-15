@props(['status'])

@php
    $colors = [
        'active' => 'bg-green-100 text-green-800',
        'inactive' => 'bg-gray-100 text-gray-800',
        'expired' => 'bg-red-100 text-red-800',
        'expiring' => 'bg-yellow-100 text-yellow-800',
        'suspended' => 'bg-purple-100 text-purple-800',
    ];

    $colorClass = $colors[$status] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }} capitalize">
    {{ $status }}
</span>