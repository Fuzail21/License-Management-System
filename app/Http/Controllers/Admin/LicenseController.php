<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLicenseRequest;
use App\Http\Requests\Admin\UpdateLicenseRequest;
use App\Models\License;
use App\Models\LicenseStatus;
use App\Models\Vendor;
use App\Services\LicenseRenewalService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    protected LicenseRenewalService $renewalService;

    public function __construct(LicenseRenewalService $renewalService)
    {
        $this->renewalService = $renewalService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = License::with(['vendor', 'creator']);

        // Manager scoping - can only see their own licenses
        if ($user->isManager()) {
            $query->where('created_by', $user->id);
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by license type
        if ($request->filled('license_type')) {
            $query->where('license_type', $request->license_type);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('license_name', 'like', '%' . $request->search . '%')
                  ->orWhere('version', 'like', '%' . $request->search . '%');
            });
        }

        $licenses = $query->withCount('userLicenses')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $vendors = Vendor::where('status', 'active')->get();

        return view('admin.licenses.index', compact('licenses', 'vendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', License::class);

        $vendors = Vendor::where('status', 'active')->get();
        return view('admin.licenses.create', compact('vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLicenseRequest $request)
    {
        $this->authorize('create', License::class);

        $user = auth()->user();
        $data = $request->validated();

        // Determine license status based on user role and permissions
        if ($user->isAdmin()) {
            // Admin always creates approved licenses
            $data['status'] = LicenseStatus::Approved->value;
            $data['approved_by'] = $user->id;
            $data['approved_at'] = now();
        } elseif ($user->isManager()) {
            if ($user->can_create_license) {
                // Manager with permission creates approved licenses
                $data['status'] = LicenseStatus::Approved->value;
                $data['approved_by'] = $user->id;
                $data['approved_at'] = now();
            } else {
                // Manager without permission creates pending licenses
                $data['status'] = LicenseStatus::Pending->value;
            }
        }

        $data['created_by'] = $user->id;

        $license = License::create($data);

        // Record initial renewal date in history
        if ($license->renewal_date) {
            $this->renewalService->recordInitialRenewalDate($license);
        }

        $message = $license->status === LicenseStatus::Pending->value
            ? 'License created and pending admin approval.'
            : 'License created successfully.';

        return redirect()->route('admin.licenses.index')
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(License $license)
    {
        $this->authorize('view', $license);

        $license->load(['vendor', 'userLicenses.employee', 'creator', 'approver', 'renewalHistories.changedByUser']);
        $renewalStats = $this->renewalService->getRenewalStatistics($license);

        return view('admin.licenses.show', compact('license', 'renewalStats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(License $license)
    {
        $this->authorize('update', $license);

        $vendors = Vendor::where('status', 'active')->get();
        return view('admin.licenses.edit', compact('license', 'vendors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLicenseRequest $request, License $license)
    {
        $this->authorize('update', $license);

        $validated = $request->validated();
        $oldRenewalDate = $license->renewal_date?->format('Y-m-d');
        $newRenewalDate = $validated['renewal_date'] ?? null;

        // Check if renewal date has changed
        if ($newRenewalDate && $oldRenewalDate !== $newRenewalDate) {
            // Track the renewal date change
            $changeType = $request->input('renewal_change_type', 'renewal');
            $reason = $request->input('renewal_reason');

            $this->renewalService->updateRenewalDate(
                $license,
                $newRenewalDate,
                $changeType,
                $reason,
                $request->input('renewal_cost')
            );

            // Remove renewal_date from validated data since it's already updated
            unset($validated['renewal_date']);
        }

        // Update other fields
        $license->update($validated);

        return redirect()->route('admin.licenses.index')
            ->with('success', 'License updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(License $license)
    {
        $this->authorize('delete', $license);

        $license->delete();

        return redirect()->route('admin.licenses.index')
            ->with('success', 'License deleted successfully.');
    }

    /**
     * Display pending licenses for admin approval.
     */
    public function pending()
    {
        $this->authorize('viewPending', License::class);

        $pendingLicenses = License::with(['vendor', 'creator'])
            ->where('status', LicenseStatus::Pending->value)
            ->orderBy('created_at', 'asc')
            ->get();

        $pendingCount = $pendingLicenses->count();

        return view('admin.licenses.pending', compact('pendingLicenses', 'pendingCount'));
    }

    /**
     * Approve a pending license.
     */
    public function approve(License $license)
    {
        $this->authorize('approve', $license);

        $license->update([
            'status' => LicenseStatus::Approved->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'License approved successfully.');
    }

    /**
     * Reject a pending license.
     */
    public function reject(Request $request, License $license)
    {
        $this->authorize('reject', $license);

        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $license->update([
            'status' => LicenseStatus::Rejected->value,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()
            ->with('success', 'License rejected.');
    }

    /**
     * Show the renewal form for a license.
     */
    public function renewForm(License $license)
    {
        $this->authorize('update', $license);

        $license->load(['vendor', 'renewalHistories.changedByUser']);
        $renewalStats = $this->renewalService->getRenewalStatistics($license);

        return view('admin.licenses.renew', compact('license', 'renewalStats'));
    }

    /**
     * Process the license renewal.
     */
    public function renew(Request $request, License $license)
    {
        $this->authorize('update', $license);

        $request->validate([
            'new_renewal_date' => 'required|date|after:today',
            'change_type' => 'required|in:renewal,extension,correction',
            'reason' => 'required|string|max:1000',
            'renewal_cost' => 'nullable|numeric|min:0',
        ]);

        $this->renewalService->updateRenewalDate(
            $license,
            $request->new_renewal_date,
            $request->change_type,
            $request->reason,
            $request->renewal_cost
        );

        return redirect()->route('admin.licenses.show', $license)
            ->with('success', 'License renewed successfully.');
    }

    /**
     * Get renewal history for a license (AJAX).
     */
    public function renewalHistory(License $license)
    {
        $this->authorize('view', $license);

        $histories = $license->renewalHistories()
            ->with('changedByUser')
            ->orderBy('changed_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'histories' => $histories,
        ]);
    }
}
