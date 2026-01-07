@props(['active', 'icon'])

@php
    $classes = ($active ?? false)
        ? 'bg-indigo-50 text-indigo-600 group flex items-center px-2 py-2 text-sm font-medium rounded-md'
        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-2 py-2 text-sm font-medium rounded-md';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($icon))
        @if($icon === 'home')
            <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        @elseif($icon === 'building-office')
            <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        @elseif($icon === 'users')
            <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        @elseif($icon === 'building-storefront')
            <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
        @elseif($icon === 'key')
            <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
        @elseif($icon === 'identification')
            <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884-.5 2-2 2h-4c-1.5 0-2-1.116-2-2" />
            </svg>
        @elseif($icon === 'arrow-path')
            <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        @elseif($icon === 'cog-6-tooth')
            <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        @elseif($icon === 'star')
            <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @elseif($icon === 'map-pin')
        <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24"  
            fill="currentColor" viewBox="0 0 24 24" >
            <!--Boxicons v3.0.6 https://boxicons.com | License  https://docs.boxicons.com/free-->
            <path d="M16 10c0-2.21-1.79-4-4-4s-4 1.79-4 4 1.79 4 4 4 4-1.79 4-4m-6 0c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2"></path><path d="M11.42 21.81c.17.12.38.19.58.19s.41-.06.58-.19c.3-.22 7.45-5.37 7.42-11.82 0-4.41-3.59-8-8-8s-8 3.59-8 8c-.03 6.44 7.12 11.6 7.42 11.82M12 4c3.31 0 6 2.69 6 6 .02 4.44-4.39 8.43-6 9.74-1.61-1.31-6.02-5.29-6-9.74 0-3.31 2.69-6 6-6"></path>
        </svg>
        @elseif($icon === 'building-office-2')
        <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24"  
            fill="currentColor" viewBox="0 0 24 24" >
            <!--Boxicons v3.0.6 https://boxicons.com | License  https://docs.boxicons.com/free-->
            <path d="m20,6h-4v-2c0-1.1-.9-2-2-2H4c-1.1,0-2,.9-2,2v17c0,.55.45,1,1,1h18c.55,0,1-.45,1-1v-13c0-1.1-.9-2-2-2Zm0,14H4V4h10v3c0,.55.45,1,1,1h5v12Z"></path><path d="M10 6H12V8H10z"></path><path d="M6 6H8V8H6z"></path><path d="M6 10H8V12H6z"></path><path d="M10 10H12V12H10z"></path><path d="M16 10H18V12H16z"></path><path d="M10 14H12V16H10z"></path><path d="M6 14H8V16H6z"></path><path d="M16 14H18V16H16z"></path>
        </svg>
        @elseif($icon === 'roles')
        <svg class="{{ ($active ?? false) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 flex-shrink-0 h-6 w-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24"  
            fill="currentColor" viewBox="0 0 24 24" >
            <!--Boxicons v3.0.6 https://boxicons.com | License  https://docs.boxicons.com/free-->
            <path d="M13 9h5v2h-5zM14 13h4v2h-4z"></path><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2M4 18V6h16v12z"></path><path d="M9 8a2 2 0 1 0 0 4 2 2 0 1 0 0-4M9 13c-1.66 0-3 1.34-3 3h6c0-1.66-1.34-3-3-3"></path>
        </svg>
        @endif
    @endif
    {{ $slot }}
</a>