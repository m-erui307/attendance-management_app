<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * 勤怠画面表示
     */
    public function index()
    {
        $attendance = Attendance::with('breaks')
            ->where('user_id', auth()->id())
            ->where('work_date', today())
            ->first();

        return view('attendance', compact('attendance'));
    }

    /**
     * 出勤
     */
    public function start()
    {
        Attendance::create([
            'user_id'   => auth()->id(),
            'work_date' => today(),
            'clock_in'  => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 退勤
     */
    public function end()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today())
            ->firstOrFail();

        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->route('attendance.index');
    }
}
