@extends('layouts.admin')

@section('title', 'Edit Department')
@section('page-title', 'Edit Department')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden max-w-2xl mx-auto">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Department: {{ $department->name }}</h3>
        </div>
        <form action="{{ route('admin.departments.update', $department) }}" method="POST" class="p-6 space-y-6">
            @csrf
            {{-- REQUIRED: The update method uses the PUT HTTP verb --}}
            @method('PUT')

            {{-- Department Name Field --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Department Name</label>
                <div class="mt-1">
                    {{-- Use old('name', $department->name) to handle validation errors AND show current value --}}
                    <input type="text" name="name" id="name" value="{{ old('name', $department->name) }}" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description Field --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <div class="mt-1">
                    {{-- For a textarea, the value goes between the opening and closing tags --}}
                    <textarea id="description" name="description" rows="3"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">{{ old('description', $department->description) }}</textarea>
                </div>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Division Field --}}
            <div>
                <label for="division_id" class="block text-sm font-medium text-gray-700">Division</label>
                <div class="mt-1">
                    <select id="division_id" name="division_id"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        <option value="">-- Select Division (Optional) --</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ old('division_id', $department->division_id) == $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('division_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Assign this department to a division.</p>
            </div>

            {{-- Status Field (Dropdown) --}}
            {{-- NOTE: I'm using the 'status' field from your create view, not the 'is_active' checkbox from your partially
            complete edit view --}}
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <div class="mt-1">
                    <select id="status" name="status" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">

                        <option value="active" {{ old('status', $department->status->value) == 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ old('status', $department->status->value) == 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.departments.index') }}"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update Department
                </button>
            </div>
        </form>
    </div>
@endsection