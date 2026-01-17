@extends('layouts.admin')

@section('title', 'Assign License')
@section('page-title', 'Assign License')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-auto max-w-2xl mx-auto">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Assignment Details</h3>
        </div>
        <form action="{{ route('admin.user-licenses.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            {{-- Searchable Employee Selection --}}
            <div x-data="{
                isOpen: false,
                searchQuery: '',
                selectedEmployee: {{ old('employee_id') ?: 'null' }},
                employees: {{ json_encode($employees->map(fn($e) => [
                    'id' => $e->id,
                    'name' => $e->first_name . ' ' . $e->last_name,
                    'email' => $e->email
                ])->toArray()) }},
                get filteredEmployees() {
                    if (!this.searchQuery) return this.employees;
                    return this.employees.filter(emp =>
                        emp.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        emp.email.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
                },
                selectEmployee(empId) {
                    this.selectedEmployee = empId;
                    this.isOpen = false;
                    this.searchQuery = '';
                },
                getSelectedEmployeeName() {
                    if (!this.selectedEmployee) return 'Select Employee';
                    const emp = this.employees.find(e => e.id === this.selectedEmployee);
                    return emp ? emp.name + ' (' + emp.email + ')' : 'Select Employee';
                }
            }">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Employee <span class="text-red-500">*</span>
                </label>

                <input type="hidden" name="employee_id" :value="selectedEmployee">

                <div class="relative">
                    <button type="button"
                        @click="isOpen = !isOpen"
                        class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <span class="block truncate" :class="{'text-gray-400': !selectedEmployee}" x-text="getSelectedEmployeeName()"></span>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>

                    <div x-show="isOpen"
                         @click.away="isOpen = false"
                         x-transition
                         class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">

                        <div class="sticky top-0 bg-white px-2 py-2">
                            <input type="text"
                                x-model="searchQuery"
                                @click.stop
                                placeholder="Search by name or email..."
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <template x-for="emp in filteredEmployees" :key="emp.id">
                            <div @click="selectEmployee(emp.id)"
                                 :class="{'bg-indigo-50': selectedEmployee === emp.id}"
                                 class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50">
                                <div class="flex flex-col">
                                    <span class="font-medium block truncate" x-text="emp.name"></span>
                                    <span class="text-gray-500 text-xs" x-text="emp.email"></span>
                                </div>
                                <span x-show="selectedEmployee === emp.id" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </template>

                        <div x-show="filteredEmployees.length === 0" class="py-2 px-3 text-sm text-gray-500">
                            No employees found
                        </div>
                    </div>
                </div>

                @error('employee_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Multiple License Selection with Tags --}}
            <div x-data="{
                selectedLicenses: {{ json_encode(old('license_ids', [])) }},
                searchQuery: '',
                isOpen: false,
                licenses: {{ json_encode($licenses->map(fn($l) => ['id' => $l->id, 'name' => $l->license_name, 'vendor' => $l->vendor->name ?? 'N/A'])->toArray()) }},
                get filteredLicenses() {
                    return this.licenses.filter(license =>
                        !this.selectedLicenses.includes(license.id) &&
                        (license.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                         license.vendor.toLowerCase().includes(this.searchQuery.toLowerCase()))
                    );
                },
                addLicense(licenseId) {
                    if (!this.selectedLicenses.includes(licenseId)) {
                        this.selectedLicenses.push(licenseId);
                    }
                    this.searchQuery = '';
                    this.isOpen = false;
                },
                removeLicense(licenseId) {
                    this.selectedLicenses = this.selectedLicenses.filter(id => id !== licenseId);
                },
                getLicenseName(licenseId) {
                    const license = this.licenses.find(l => l.id === licenseId);
                    return license ? license.name : '';
                },
                getLicenseVendor(licenseId) {
                    const license = this.licenses.find(l => l.id === licenseId);
                    return license ? license.vendor : '';
                }
            }">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Licenses <span class="text-red-500">*</span>
                </label>

                {{-- Hidden inputs for selected licenses --}}
                <template x-for="licenseId in selectedLicenses" :key="licenseId">
                    <input type="hidden" name="license_ids[]" :value="licenseId">
                </template>

                {{-- Selected Licenses Tags --}}
                <div class="flex flex-wrap gap-2 mb-3 min-h-[32px]">
                    <template x-for="licenseId in selectedLicenses" :key="licenseId">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                            <span x-text="getLicenseName(licenseId)"></span>
                            <span class="ml-1 text-xs text-indigo-500" x-text="'(' + getLicenseVendor(licenseId) + ')'"></span>
                            <button type="button" @click="removeLicense(licenseId)"
                                class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full text-indigo-400 hover:bg-indigo-200 hover:text-indigo-600 focus:outline-none">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </span>
                    </template>
                    <span x-show="selectedLicenses.length === 0" class="text-sm text-gray-400 italic">No licenses selected</span>
                </div>

                {{-- Search Input --}}
                <div class="relative">
                    <input type="text"
                        x-model="searchQuery"
                        @focus="isOpen = true"
                        @click.away="isOpen = false"
                        placeholder="Search and select licenses..."
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">

                    {{-- Dropdown --}}
                    <div x-show="isOpen && filteredLicenses.length > 0"
                         x-transition
                         class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                        <template x-for="license in filteredLicenses" :key="license.id">
                            <div @click="addLicense(license.id)"
                                 class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50">
                                <div class="flex items-center">
                                    <span class="font-medium block truncate" x-text="license.name"></span>
                                    <span class="ml-2 text-gray-500 text-sm" x-text="'(' + license.vendor + ')'"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- No results message --}}
                    <div x-show="isOpen && searchQuery.length > 0 && filteredLicenses.length === 0"
                         class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-2 px-3 text-sm text-gray-500">
                        No licenses found matching "<span x-text="searchQuery"></span>"
                    </div>
                </div>

                <p class="mt-2 text-xs text-gray-500">
                    <span x-text="selectedLicenses.length"></span> license(s) selected
                </p>

                @error('license_ids')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('license_ids.*')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <div>
                    <label for="assigned_date" class="block text-sm font-medium text-gray-700">
                        Assigned Date
                    </label>
                    <div class="mt-1">
                        <input
                            type="date"
                            name="assigned_date"
                            id="assigned_date"
                            value="{{ old('assigned_date', now()->format('Y-m-d')) }}"
                            readonly
                            class="shadow-sm bg-gray-100 cursor-not-allowed
                                   focus:ring-0 focus:border-gray-300
                                   block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                    </div>
                </div>


                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <div class="mt-1">
                        <select id="status" name="status" required
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.user-licenses.index') }}"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Assign License(s)
                </button>
            </div>
        </form>
    </div>
@endsection
