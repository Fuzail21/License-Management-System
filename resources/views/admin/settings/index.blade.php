@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'System Settings')

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data"
            class="p-6 space-y-8">
            @csrf
            @method('PUT')

            <!-- Branding Section -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Branding & Appearance</h3>
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="app_name" class="block text-sm font-medium text-gray-700">Application Name</label>
                        <div class="mt-1">
                            <input type="text" name="app_name" id="app_name"
                                value="{{ old('app_name', $settings->app_name) }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                            @error('app_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-700">Application Logo</label>
                        <div class="mt-1 flex items-center space-x-4">
                            @if($settings->app_logo)
                                <img src="{{ Storage::url($settings->app_logo) }}" alt="Current Logo"
                                    class="h-12 w-auto rounded-md border p-1">
                            @endif
                            <input type="file" name="app_logo" id="app_logo" accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        @error('app_logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="primary_color" class="block text-sm font-medium text-gray-700">Primary Color</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="color" name="primary_color" id="primary_color"
                                value="{{ old('primary_color', $settings->primary_color) }}"
                                class="h-9 w-9 rounded border border-gray-300 cursor-pointer">
                            <input type="text" name="primary_color"
                                value="{{ old('primary_color', $settings->primary_color) }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border uppercase">
                        </div>
                        @error('primary_color') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="secondary_color" class="block text-sm font-medium text-gray-700">Secondary Color</label>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="color" name="secondary_color" id="secondary_color"
                                value="{{ old('secondary_color', $settings->secondary_color) }}"
                                class="h-9 w-9 rounded border border-gray-300 cursor-pointer">
                            <input type="text" name="secondary_color"
                                value="{{ old('secondary_color', $settings->secondary_color) }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border uppercase">
                        </div>
                        @error('secondary_color') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Regional Settings -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Regional Settings</h3>
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-2">
                        <label for="currency_name" class="block text-sm font-medium text-gray-700">Currency Name (e.g.
                            USD)</label>
                        <div class="mt-1">
                            <input type="text" name="currency_name" id="currency_name"
                                value="{{ old('currency_name', $settings->currency_name) }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        </div>
                        @error('currency_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="currency_symbol" class="block text-sm font-medium text-gray-700">Currency Symbol (e.g.
                            $)</label>
                        <div class="mt-1">
                            <input type="text" name="currency_symbol" id="currency_symbol"
                                value="{{ old('currency_symbol', $settings->currency_symbol) }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        </div>
                        @error('currency_symbol') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                        <div class="mt-1">
                            <select name="timezone" id="timezone"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                                @foreach(timezone_identifiers_list() as $timezone)
                                    <option value="{{ $timezone }}" {{ old('timezone', $settings->timezone) == $timezone ? 'selected' : '' }}>
                                        {{ $timezone }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('timezone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Contact Information</h3>
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="support_email" class="block text-sm font-medium text-gray-700">Support Email</label>
                        <div class="mt-1">
                            <input type="email" name="support_email" id="support_email"
                                value="{{ old('support_email', $settings->support_email) }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        </div>
                        @error('support_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="support_phone" class="block text-sm font-medium text-gray-700">Support Phone</label>
                        <div class="mt-1">
                            <input type="text" name="support_phone" id="support_phone"
                                value="{{ old('support_phone', $settings->support_phone) }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        </div>
                        @error('support_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-6">
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <div class="mt-1">
                            <textarea name="address" id="address" rows="3"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">{{ old('address', $settings->address) }}</textarea>
                        </div>
                        @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-6">
                        <label for="footer_text" class="block text-sm font-medium text-gray-700">Footer Text</label>
                        <div class="mt-1">
                            <input type="text" name="footer_text" id="footer_text"
                                value="{{ old('footer_text', $settings->footer_text) }}"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        </div>
                        @error('footer_text') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="pt-5 border-t border-gray-200">
                <div class="flex justify-end">
                    <button type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection