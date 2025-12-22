<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::getSettings();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name'         => 'required|string|max:255',
            'app_logo'         => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'primary_color'    => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'secondary_color'  => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'currency_name'    => 'nullable|string|max:10',
            'currency_symbol'  => 'nullable|string|max:5',
            'support_email'    => 'nullable|email|max:255',
            'support_phone'    => 'nullable|string|max:20',
            'address'          => 'nullable|string',
            'footer_text'      => 'nullable|string',
            'timezone'         => 'nullable|string|timezone',
        ]);

        $settings = Setting::first();

        if ($request->hasFile('app_logo')) {

            if ($settings && $settings->app_logo) {
                Storage::disk('public')->delete($settings->app_logo);
            }

            $validated['app_logo'] = $request
                ->file('app_logo')
                ->store('settings', 'public');
        }

        if ($settings) {
            $settings->update($validated);
        } else {
            Setting::create($validated);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

}
