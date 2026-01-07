@extends('layouts.admin')

@section('title', 'Edit City')
@section('page-title', 'Edit City')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Edit City</h3>
                <p class="mt-1 text-sm text-gray-500">Update city information.</p>
            </div>

            <form action="{{ route('admin.cities.update', $city) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="px-6 py-4 space-y-6">
                    {{-- City Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            City Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $city->name) }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- City Code --}}
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">
                            City Code (Optional)
                        </label>
                        <input type="text" name="code" id="code" value="{{ old('code', $city->code) }}"
                            placeholder="e.g., NYC, LA, CHI"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('code') border-red-300 @enderror">
                        <p class="mt-1 text-sm text-gray-500">A unique code for this city (will be converted to uppercase).
                        </p>
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('status') border-red-300 @enderror">
                            <option value="active" {{ old('status', $city->status) === 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="inactive" {{ old('status', $city->status) === 'inactive' ? 'selected' : '' }}>
                                Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                    <a href="{{ route('admin.cities.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update City
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
