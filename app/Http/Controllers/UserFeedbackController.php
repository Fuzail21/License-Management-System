<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserFeedback;

class UserFeedbackController extends Controller
{
    /**
     * Handle YES or NO selection
     */

    public function index()
    {
        return view('admin.reviews.create');
    }

    // UserFeedbackController.php

    public function satisfied(Request $request)
    {
        $request->validate([
            'satisfied' => 'required|boolean',
        ]);

        // Get employee_id from authenticated user if exists
        $employeeId = \App\Models\Employee::where('email', auth()->user()->email)->first()?->id;

        UserFeedback::create([
            'user_id'     => auth()->id(),
            'employee_id' => $employeeId,
            'satisfied'   => $request->boolean('satisfied'),
        ]);

        // Return JSON instead of redirecting
        return response()->json(['success' => true, 'message' => 'Feedback saved']);
    }

    public function issue(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'issue_type' => 'required|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        // Get employee_id from authenticated user if exists
        $employeeId = \App\Models\Employee::where('email', auth()->user()->email)->first()?->id;

        UserFeedback::create([
            'user_id'        => auth()->id(),
            'employee_id'    => $employeeId,
            'satisfied'      => 0,
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'issue_type'     => $request->issue_type,
            'message'        => $request->message,
        ]);

        // CHANGE THIS: Instead of redirect()->back()
        return response()->json([
            'status' => 'success',
            'message' => 'Thank you for your feedback.'
        ]);
    }
}

