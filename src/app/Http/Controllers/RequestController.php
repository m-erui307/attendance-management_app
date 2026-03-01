<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Carbon\Carbon;


class RequestController extends Controller
{
    public function store(Request $request)
{
    $user = auth()->user();

    $existing = AttendanceRequest::where('user_id', $user->id)
        ->where('target_date', $request->target_date)
        ->where('status', 'pending')
        ->first();

    if ($existing) {
        return back();
    }

    AttendanceRequest::create([
        'user_id' => $user->id,
        'target_date' => $request->target_date,
        'clock_in' => $request->clock_in,
        'clock_out' => $request->clock_out,
        'breaks' => $request->breaks ?? [],
        'remark' => $request->remark,
        'status' => 'pending',
    ]);

    return back();
}

    public function index()
    {
        $status = request('status', 'pending');

    $query = auth()->user()
        ->attendanceRequests()
        ->latest();

    if ($status) {
        $query->where('status', $status);
    }

    $requests = $query->get();

    return view('request-list', compact('requests', 'status'));
    }

}
