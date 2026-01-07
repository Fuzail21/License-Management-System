@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden max-w-2xl mx-auto">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit User: {{ $user->name }}</h3>
        </div>
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                <div class="mt-1">
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                <div class="mt-1">
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password (Leave blank to keep current)</label>
                    <div class="mt-1">
                        <input type="password" name="password" id="password" minlength="8"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <div class="mt-1">
                        <input type="password" name="password_confirmation" id="password_confirmation" minlength="8"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                    </div>
                </div>
            </div>

            <div>
                <label for="role_id" class="block text-sm font-medium text-gray-700">Role <span class="text-red-500">*</span></label>
                <div class="mt-1">
                    <select id="role_id" name="role_id" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('role_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="license-permission-section" class="hidden">
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-blue-800">Manager License Permissions</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Control whether this manager can create licenses without admin approval.</p>
                            </div>
                            <div class="mt-4">
                                <div class="flex items-center">
                                    <input id="can_create_license" name="can_create_license" type="checkbox" value="1"
                                        {{ old('can_create_license', $user->can_create_license) ? 'checked' : '' }}
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="can_create_license" class="ml-2 block text-sm font-medium text-gray-700">
                                        Allow this manager to create licenses without approval
                                    </label>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    <strong>Enabled:</strong> Licenses created by this manager will be immediately approved.<br>
                                    <strong>Disabled:</strong> Licenses will require admin approval before becoming active.
                                </p>
                                @if($user->can_create_license && $user->getPendingLicensesCount() > 0)
                                    <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded p-2">
                                        <p class="text-xs text-yellow-800">
                                            <strong>Warning:</strong> This manager has {{ $user->getPendingLicensesCount() }} pending license(s). Disabling this permission will not affect existing pending licenses.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @error('can_create_license')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700">Department <span class="text-red-500">*</span></label>
                <div class="mt-1">
                    <select id="department_id" name="department_id" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('department_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div> -->

            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <div class="mt-1">
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                            placeholder="+1 (555) 123-4567">
                    </div>
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- <div>
                    <label for="designation" class="block text-sm font-medium text-gray-700">Designation</label>
                    <div class="mt-1">
                        <input type="text" name="designation" id="designation" value="{{ old('designation', $user->designation) }}"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                            placeholder="Software Engineer">
                    </div>
                    @error('designation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div> -->
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                <div class="mt-1">
                    <select id="status" name="status" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        <option value="active" {{ old('status', $user->status->value) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->status->value) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- <div>
                <label for="head" class="block text-sm font-medium text-gray-700">Head <span class="text-red-500">*</span></label>
                <div class="mt-1">
                    <select id="head" name="head" required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        <option value="1" {{ old('head', $user->head) == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('head', $user->head) == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                @error('head')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div> -->

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update User
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role_id');
            const licensePermissionSection = document.getElementById('license-permission-section');
            const managerRoleId = {{ $managerRoleId ?? 'null' }};

            function toggleLicensePermission() {
                const selectedRoleId = parseInt(roleSelect.value);
                if (selectedRoleId === managerRoleId) {
                    licensePermissionSection.classList.remove('hidden');
                } else {
                    licensePermissionSection.classList.add('hidden');
                }
            }

            roleSelect.addEventListener('change', toggleLicensePermission);
            toggleLicensePermission(); // Run on page load
        });
    </script>
    @endpush
@endsection
