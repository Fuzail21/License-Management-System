<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\License;
use App\Models\User;
use App\Models\UserLicense;
use App\Models\Vendor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'departments' => Department::where('status', 'active')->count(),
            'users' => User::where('status', 'active')->count(),
            'vendors' => Vendor::where('status', 'active')->count(),
            'licenses' => License::count(),
            'active_licenses' => UserLicense::where('status', 'active')->count(),
            'expiring_soon' => UserLicense::where('status', 'active')
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                ->count(),
            'expired' => UserLicense::where('status', 'expired')->count(),
        ];

        $recentLicenses = UserLicense::with(['user', 'license.vendor'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentLicenses'));
    }
}
