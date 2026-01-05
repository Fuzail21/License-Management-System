@extends('layouts.admin')

@section('title', 'Division Details')
@section('page-title', 'Division Details')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Division Information Card --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Division Information</h3>
                <div class="flex gap-2">
                    <a href="{{ route('admin.divisions.edit', $division) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Division
                    </a>
                    <a href="{{ route('admin.divisions.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Back to List
                    </a>
                </div>
            </div>
            <div class="px-5 py-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Division Name</label>
                        <p class="mt-1 text-sm text-gray-900 font-semibold">{{ $division->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">General Manager</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $division->gm ? $division->gm->name : 'Not Assigned' }}
                            @if($division->gm)
                                <span class="text-gray-500">({{ $division->gm->email }})</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Total Departments</label>
                        <p class="mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $division->departments_count }} {{ Str::plural('department', $division->departments_count) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Created At</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $division->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
                @if($division->description)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Description</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $division->description }}</p>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $division->updated_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        {{-- Departments in this Division --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Departments in this Division</h3>
            </div>
            @if($division->departments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($division->departments as $department)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $department->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $department->description ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <x-status-badge :status="$department->status->value" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($division->departments_count > 10)
                    <div class="px-5 py-4 border-t border-gray-200 text-center">
                        <p class="text-sm text-gray-500">Showing first 10 departments. Total: {{ $division->departments_count }} departments</p>
                    </div>
                @endif
            @else
                <div class="px-6 py-10 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No departments assigned</h3>
                    <p class="mt-1 text-sm text-gray-500">No departments are currently assigned to this division.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
