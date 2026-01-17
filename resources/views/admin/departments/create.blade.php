@extends('layouts.admin')

@section('title', 'Create Department')
@section('page-title', 'Create Department')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Create New Department</h3>
                <p class="mt-1 text-sm text-gray-500">Add a new department to one or more divisions.</p>
            </div>

            <form action="{{ route('admin.departments.store') }}" method="POST">
                @csrf

                <div class="px-6 py-4 space-y-6">
                    {{-- Division Selection with Select All --}}
                    <div x-data="{
                        selectAll: false,
                        selectedDivisions: [],
                        allDivisionIds: [{{ $divisions->pluck('id')->implode(',') }}],
                        init() {
                            this.selectedDivisions = {{ json_encode(array_map('intval', old('division_ids', []))) }};
                            this.updateSelectAll();
                        },
                        toggleSelectAll() {
                            if (this.selectAll) {
                                this.selectedDivisions = [...this.allDivisionIds];
                            } else {
                                this.selectedDivisions = [];
                            }
                        },
                        updateSelectAll() {
                            this.selectAll = this.selectedDivisions.length === this.allDivisionIds.length && this.allDivisionIds.length > 0;
                        },
                        isSelected(id) {
                            return this.selectedDivisions.includes(id);
                        },
                        toggleDivision(id) {
                            const index = this.selectedDivisions.indexOf(id);
                            if (index > -1) {
                                this.selectedDivisions.splice(index, 1);
                            } else {
                                this.selectedDivisions.push(id);
                            }
                            this.updateSelectAll();
                        }
                    }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Divisions <span class="text-red-500">*</span>
                        </label>

                        {{-- Select All Checkbox --}}
                        <div class="mb-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                    x-model="selectAll"
                                    @change="toggleSelectAll()"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">Select All Divisions</span>
                            </label>
                        </div>

                        {{-- Divisions Multi-select --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-4 border border-gray-200 rounded-md max-h-60 overflow-y-auto bg-gray-50">
                            @foreach ($divisions as $division)
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                        name="division_ids[]"
                                        value="{{ $division->id }}"
                                        :checked="isSelected({{ $division->id }})"
                                        @change="toggleDivision({{ $division->id }})"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $division->name }} <span class="text-gray-400">({{ $division->city->name ?? 'N/A' }})</span></span>
                                </label>
                            @endforeach
                        </div>

                        <p class="mt-2 text-xs text-gray-500">
                            <span x-text="selectedDivisions.length"></span> of {{ $divisions->count() }} divisions selected
                        </p>

                        @error('division_ids')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('division_ids.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Department Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Department Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                        @error('name')
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
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                    <a href="{{ route('admin.departments.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Create Department
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
