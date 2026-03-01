<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

class AdminRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $requests = AttendanceRequest::with('user')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin_request-list', compact('requests', 'status'));
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {

            $request = AttendanceRequest::findOrFail($id);

            if ($request->status === 'approved') {
                return;
            }

            $attendance = Attendance::firstOrCreate([
                'user_id' => $request->user_id,
                'work_date' => $request->target_date,
            ]);

            // 日付＋時刻を結合
            $clockIn = $request->clock_in
                ? Carbon::parse($request->work_date . ' ' . $request->clock_in)
                : null;

            $clockOut = $request->clock_out
                ? Carbon::parse($request->work_date . ' ' . $request->clock_out)
                : null;

            $attendance->update([
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
            ]);

            // 休憩削除
            $attendance->breaks()->delete();

            // 休憩再登録
            if ($request->breaks) {
                foreach ($request->breaks as $break) {
                    $attendance->breaks()->create([
                        'break_start' => Carbon::parse($request->work_date.' '.$break['start']),
                        'break_end' => Carbon::parse($request->work_date.' '.$break['end']),
                    ]);
                }
            }

            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return back();
    }

    public function show($id)
{
    $request = AttendanceRequest::with('user')
        ->findOrFail($id);

    $user = $request->user;
    $date = Carbon::parse($request->target_date);

    $breaks = collect($request->breaks ?? [])
    ->filter(function ($break) {
        return !empty($break['start']) || !empty($break['end']);
    })
    ->values()
    ->toArray();

return view('admin_approval', compact(
    'request',
    'user',
    'date',
    'breaks'
));
}
}

