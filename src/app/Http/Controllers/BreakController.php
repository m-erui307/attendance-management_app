<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Http\Request;

class BreakController extends Controller
{
    /**
     * 休憩開始
     */
    public function start()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today())
            ->firstOrFail();

        $isBreaking = $attendance->breaks()
            ->whereNull('break_end')
            ->exists();

        if (!$isBreaking) {
            $attendance->breaks()->create([
                'break_start' => now(),
            ]);
        }

        return redirect()->route('attendance.index');
    }

    /**
     * 休憩終了
     */
    public function end()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today())
            ->firstOrFail();

        $break = $attendance->breaks()
            ->whereNull('break_end')
            ->firstOrFail();

        $break->update([
            'break_end' => now(),
        ]);

        return redirect()->route('attendance.index');
    }
}
