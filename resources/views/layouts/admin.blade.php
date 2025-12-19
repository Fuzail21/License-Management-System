<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <?php
        use App\Models\Setting;
        $appSetting = Setting::first();

        $primaryColor   = $appSetting->primary_color   ?? '#4f46e5'; // default indigo
        $secondaryColor = $appSetting->secondary_color ?? '#6366f1';
    ?>

    <title>@yield('title', 'Dashboard') - {{ $appSetting->app_name ?? 'LMS' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    @stack('styles')
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto"
            :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">

            <!-- Logo -->
            <div class="flex items-center justify-center h-16 border-b border-gray-200 px-4">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-2 text-xl font-bold {{ $primaryColor }}">
                    @if($appSetting->app_logo)
                        <img src="{{ Storage::url($appSetting->app_logo) }}" alt="Logo" class="h-8 w-auto">
                    @else
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    @endif
                    {{-- <span class="truncate">{{ $appSetting->app_name ?? 'LMS' }}</span> --}}
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')"
                    icon="home">
                    Dashboard
                </x-nav-link>

                <div class="pt-4 pb-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Management
                </div>

                <x-nav-link :href="route('admin.departments.index')" :active="request()->routeIs('admin.departments.*')"
                    icon="building-office">
                    Departments
                </x-nav-link>

                <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')"
                    icon="users">
                    Users
                </x-nav-link>

                <x-nav-link :href="route('admin.vendors.index')" :active="request()->routeIs('admin.vendors.*')"
                    icon="building-storefront">
                    Vendors
                </x-nav-link>

                <x-nav-link :href="route('admin.licenses.index')" :active="request()->routeIs('admin.licenses.*')"
                    icon="key">
                    Licenses
                </x-nav-link>

                <x-nav-link :href="route('admin.user-licenses.index')"
                    :active="request()->routeIs('admin.user-licenses.*')" icon="identification">
                    User Licenses
                </x-nav-link>

                <x-nav-link :href="route('admin.renewals.index')" :active="request()->routeIs('admin.renewals.*')"
                    icon="arrow-path">
                    Renewals
                </x-nav-link>

                <x-nav-link :href="route('admin.reviews.index')" :active="request()->routeIs('admin.reviews.*')"
                    icon="star">
                    Reviews
                </x-nav-link>

                <div class="pt-4 pb-2 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    System
                </div>

                <x-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')"
                    icon="cog-6-tooth">
                    Settings
                </x-nav-link>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-gray-200 text-xs text-center text-gray-500">
                {{ $appSetting->footer_text ?? '© ' . date('Y') . ' LMS' }}
            </div>
        </aside>

        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity
            class="fixed inset-0 z-40 bg-gray-900 bg-opacity-50 lg:hidden"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Topbar -->
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-4 ml-auto">
                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false"
                                class="flex items-center gap-2 focus:outline-none">
                                <span
                                    class="text-sm font-medium text-gray-700 hidden sm:block">{{ auth()->user()->name ?? 'Admin' }}</span>
                                <div
                                    class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition
                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 focus:outline-none"
                                style="display: none;">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div
                        class="mb-4 p-4 rounded-md bg-green-50 border border-green-200 text-green-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 rounded-md bg-red-50 border border-red-200 text-red-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                @hasSection('page-title')
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">@yield('page-title')</h1>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>