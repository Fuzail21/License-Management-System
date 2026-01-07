@extends('layouts.admin')

@section('title', 'Manage City Managers')
@section('page-title', 'Manage City Managers')

@section('content')
    <div class="space-y-6">
        {{-- City Information --}}
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $city->name }}</h2>
                    <p class="mt-1 text-sm text-gray-500">Manage managers assigned to this city</p>
                </div>
                <a href="{{ route('admin.cities.show', $city) }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to City
                </a>
            </div>
        </div>

        {{-- Assign New Manager --}}
        @if ($availableManagers->isNotEmpty())
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Assign New Manager</h3>
                    <p class="mt-1 text-sm text-gray-500">Select a manager to assign to this city</p>
                </div>

                <form action="{{ route('admin.cities.assign-manager', $city) }}" method="POST" class="px-6 py-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Select Manager</label>
                            <select name="user_id" id="user_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('user_id') border-red-300 @enderror">
                                <option value="">-- Select a Manager --</option>
                                @foreach ($availableManagers as $manager)
                                    <option value="{{ $manager->id }}" {{ old('user_id') == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->name }} ({{ $manager->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Assign Manager
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="ml-3 text-sm text-yellow-700">
                        All available managers have been assigned to this city.
                    </p>
                </div>
            </div>
        @endif

        {{-- Assigned Managers --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Assigned Managers</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $city->managers->count() }} {{ Str::plural('manager', $city->managers->count()) }} currently
                    assigned to this city
                </p>
            </div>

            @if ($city->managers->isEmpty())
                <div class="px-6 py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No managers assigned</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by assigning a manager to this city.</p>
                </div>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($city->managers as $manager)
                        <li class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex items-center flex-1 min-w-0">
                                <div
                                    class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                    {{ strtoupper(substr($manager->name, 0, 2)) }}
                                </div>
                                <div class="ml-4 flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $manager->name }}</p>
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                            {{ $manager->role->name ?? 'Manager' }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 truncate">{{ $manager->email }}</p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Assigned {{ $manager->pivot->created_at->diffForHumans() }}
                                        @if ($manager->pivot->assigned_at)
                                            ({{ \Carbon\Carbon::parse($manager->pivot->assigned_at)->format('M d, Y') }})
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="ml-4 flex-shrink-0">
                                <form action="{{ route('admin.cities.remove-manager', [$city, $manager]) }}" method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('Are you sure you want to remove this manager from this city? They will lose access to all departments, divisions, and employees in this city.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                        <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
