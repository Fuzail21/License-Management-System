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
        // 1. Remove dd($request->all()); it will break your JS fetch!

        $request->validate([
            'satisfied' => 'required|boolean',
        ]);

        UserFeedback::create([
            'user_id'   => auth()->id(),
            'satisfied' => $request->boolean('satisfied'),
        ]);


        // Return JSON instead of redirecting
        return response()->json(['success' => true, 'message' => 'Feedback saved']);
    }

    public function issue(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'issue_type' => 'required|string|max:50',
        ]);

        UserFeedback::create([
            'user_id' => auth()->id(),
            'satisfied' => 0,
            'issue_type' => $request->issue_type,
            'message' => $request->message,
        ]);

        // CHANGE THIS: Instead of redirect()->back()
        return response()->json([
            'status' => 'success',
            'message' => 'Thank you for your feedback.'
        ]);
    }
}

