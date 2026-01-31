<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\License;
use App\Models\Role;
use App\Models\UserLicense;
use App\Models\Vendor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        $user = auth()->user();

        // For managers, scope data to their assigned cities
        // For admins, show global data
        $stats = [
            'cities' => City::forManager()->where('cities.status', 'active')->count(),
            'departments' => Department::forManager()->where('departments.status', 'active')->count(),
            'divisions' => Division::forManager()->where('divisions.status', 'active')->count(),
            'employees' => Employee::forManager()->where('employees.status', 'active')->count(),
            'vendors' => Vendor::where('status', 'active')->count(),
            'licenses' => License::count(),
            'roles' => Role::count(),
        ];

        // Get employee IDs within manager's scope for license filtering
        $scopedEmployeeIds = Employee::forManager()->pluck('id')->toArray();

        if ($user->isManager() && !empty($scopedEmployeeIds)) {
            $stats['active_licenses'] = UserLicense::whereIn('employee_id', $scopedEmployeeIds)
                ->where('status', 'active')
                ->count();
            $stats['expired'] = UserLicense::whereIn('employee_id', $scopedEmployeeIds)
                ->where('status', 'expired')
                ->count();
        } else {
            // Admin sees all licenses
            $stats['active_licenses'] = UserLicense::where('status', 'active')->count();
            $stats['expired'] = UserLicense::where('status', 'expired')->count();
        }

        // Recent licenses - scoped for managers, all for admins
        $recentLicensesQuery = UserLicense::with(['employee.department.division.city', 'license.vendor'])
            ->orderBy('created_at', 'desc')
            ->limit(10);

        if ($user->isManager() && !empty($scopedEmployeeIds)) {
            $recentLicensesQuery->whereIn('employee_id', $scopedEmployeeIds);
        }

        $recentLicenses = $recentLicensesQuery->get();

        return view('admin.dashboard', compact('stats', 'recentLicenses'));
    }
}
