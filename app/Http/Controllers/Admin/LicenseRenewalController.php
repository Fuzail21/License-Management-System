<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRenewalRequest;
use App\Models\LicenseRenewal;
use App\Models\UserLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LicenseRenewalController extends Controller
{
    /**
     * Display a listing of renewals.
     */
    public function index(Request $request)
    {
        $query = LicenseRenewal::with('userLicense.user', 'userLicense.license.vendor');

        if ($request->has('user_license_id') && $request->user_license_id !== '') {
            $query->where('user_license_id', $request->user_license_id);
        }

        $renewals = $query->orderBy('renewed_at', 'desc')->paginate(15);

        return view('admin.renewals.index', compact('renewals'));
    }

    /**
     * Show the form for creating a new renewal.
     */
    public function create(UserLicense $userLicense)
    {
        $userLicense->load(['user', 'license.vendor']);
        return view('admin.renewals.create', compact('userLicense'));
    }

    /**
     * Store a newly created renewal.
     */
    public function store(StoreRenewalRequest $request, UserLicense $userLicense)
    {
        $data = $request->validated();
        $data['user_license_id'] = $userLicense->id;
        $data['old_expiry_date'] = $userLicense->expiry_date;
        $data['renewed_by'] = Auth::user()->id;
        $data['renewed_at'] = now();

        // Create renewal record
        LicenseRenewal::create($data);

        // Update user license
        $userLicense->expiry_date = $data['new_expiry_date'];
        $userLicense->renewal_cost = $data['renewal_cost'];
        $userLicense->renewed_at = now();
        $userLicense->status = 'active';
        $userLicense->save();

        return redirect()->route('admin.user-licenses.show', $userLicense)
            ->with('success', 'License renewed successfully.');
    }
}
