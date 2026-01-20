<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRenewalRequest;
use App\Models\LicenseRenewal;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LicenseRenewalController extends Controller
{
    /**
     * Display a listing of licenses with renewal tracking.
     */
    public function index(Request $request)
    {
        $licenses = License::with('vendor')
            ->withCount('userLicenses')
            ->approved()
            ->get()
            ->sortBy(function ($license) {
                return $license->remaining_days;
            })
            ->values();

        return view('admin.renewals.index', compact('licenses'));
    }

    /**
     * Show the form for creating a new renewal.
     */
    public function create(License $license)
    {
        // Load vendor for display
        $license->load('vendor');

        return view('admin.renewals.create', compact('license'));
    }

    /**
     * Store a newly created renewal.
     */
    public function store(StoreRenewalRequest $request, License $license)
    {
        $data = $request->validated();

        // Link this renewal to the master License
        $data['license_id'] = $license->id;

        // Try to populate old_expiry_date from the License if available,
        // otherwise fall back to today to satisfy the not-null DB column.
        $data['old_expiry_date'] = $license->expiry_date ?? now()->toDateString();

        $data['renewed_by'] = Auth::user()->id;
        $data['renewed_at'] = now();

        // Create renewal record
        LicenseRenewal::create($data);

        // Master-level renewal does not alter individual UserLicense rows here.

        return redirect()->route('admin.licenses.show', $license)
            ->with('success', 'Master license renewed successfully.');
    }

    /**
     * Show the form for editing the specified renewal.
     */
    public function edit(LicenseRenewal $renewal)
    {
        // Load related license and prepare license list for the select box
        $renewal->load('license.vendor');

        $licenses = License::with('vendor')->orderBy('license_name')->get();

        return view('admin.renewals.edit', compact('renewal', 'licenses'));
    }
}
