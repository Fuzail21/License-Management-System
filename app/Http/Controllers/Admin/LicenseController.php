<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLicenseRequest;
use App\Http\Requests\Admin\UpdateLicenseRequest;
use App\Models\License;
use App\Models\Vendor;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = License::with('vendor');

        if ($request->has('vendor_id') && $request->vendor_id !== '') {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->has('license_type') && $request->license_type !== '') {
            $query->where('license_type', $request->license_type);
        }

        if ($request->has('search') && $request->search !== '') {
            $query->where(function($q) use ($request) {
                $q->where('license_name', 'like', '%' . $request->search . '%')
                  ->orWhere('version', 'like', '%' . $request->search . '%');
            });
        }

        $licenses = $query->withCount('userLicenses')->orderBy('created_at', 'desc')->paginate(15);
        $vendors = Vendor::where('status', 'active')->get();

        return view('admin.licenses.index', compact('licenses', 'vendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vendors = Vendor::where('status', 'active')->get();
        return view('admin.licenses.create', compact('vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLicenseRequest $request)
    {
        License::create($request->validated());

        return redirect()->route('admin.licenses.index')
            ->with('success', 'License created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(License $license)
    {
        $license->load(['vendor', 'userLicenses.employee']);
        return view('admin.licenses.show', compact('license'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(License $license)
    {
        $vendors = Vendor::where('status', 'active')->get();
        return view('admin.licenses.edit', compact('license', 'vendors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLicenseRequest $request, License $license)
    {
        $license->update($request->validated());

        return redirect()->route('admin.licenses.index')
            ->with('success', 'License updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(License $license)
    {
        $license->delete();

        return redirect()->route('admin.licenses.index')
            ->with('success', 'License deleted successfully.');
    }
}
