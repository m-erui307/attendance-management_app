<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
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

    /**
     * 勤怠一覧
     */
    public function list(Request $request)
    {
    // 表示する月
    $targetMonth = $request->month
        ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
        : Carbon::now()->startOfMonth();

    $prevMonth = $targetMonth->copy()->subMonth()->format('Y-m');
    $nextMonth = $targetMonth->copy()->addMonth()->format('Y-m');

    // 月の日付一覧
    $days = [];
    $start = $targetMonth->copy();
    $end = $targetMonth->copy()->endOfMonth();
    while ($start <= $end) {
        $days[] = $start->copy();
        $start->addDay();
    }

    // 勤怠データ取得
    $attendances = Attendance::with('breaks')
    ->where('user_id', auth()->id())
    ->whereYear('work_date', $targetMonth->year)
    ->whereMonth('work_date', $targetMonth->month)
    ->get()
        ->keyBy(fn ($a) => $a->work_date->format('Y-m-d')); // 文字列キーに統一

    // カレンダー用配列作成
    $calendar = [];
    foreach ($days as $day) {
        $dayKey = $day->format('Y-m-d');
        $attendance = $attendances->get($dayKey);

        if ($attendance) {
            // clock_in / clock_out を Carbon に変換（nullチェック）
            $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

            // 休憩時間（分）
            $breakMinutes = $attendance->breaks->sum(function ($break) {
                if (!$break->break_start || !$break->break_end) return 0;
                return Carbon::parse($break->break_start)
                    ->diffInMinutes(Carbon::parse($break->break_end));
            });

            // 勤務時間（分）
            $workMinutes = ($clockIn && $clockOut) ? $clockIn->diffInMinutes($clockOut) - $breakMinutes : 0;

            // 表示用
            $attendance->clock_in = $clockIn;
            $attendance->clock_out = $clockOut;
            $attendance->break_time = sprintf('%02d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
            $attendance->total_time = sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
        }

        $calendar[] = [
            'date' => $day,
            'attendance' => $attendance,
        ];
    }

    return view('attendance-list', compact(
        'calendar',
        'targetMonth',
        'prevMonth',
        'nextMonth'
    ));
}

    /**
    * 勤怠詳細
    */
    public function show($date)
{
    $date = Carbon::parse($date);

    $attendance = Attendance::with('breaks')
        ->where('user_id', auth()->id())
        ->where('work_date', $date)
        ->first();

    if ($attendance) {
        // 休憩（分）
        $breakMinutes = $attendance->breaks->sum(function ($break) {
            if (!$break->break_start || !$break->break_end) {
                return 0;
            }

            return Carbon::parse($break->break_start)
                ->diffInMinutes(Carbon::parse($break->break_end));
        });

        // 勤務時間（分）
        $workMinutes = 0;
        if ($attendance->clock_in && $attendance->clock_out) {
            $workMinutes =
                Carbon::parse($attendance->clock_in)
                    ->diffInMinutes(Carbon::parse($attendance->clock_out))
                - $breakMinutes;
        }

        $attendance->break_time = sprintf(
            '%02d:%02d',
            intdiv($breakMinutes, 60),
            $breakMinutes % 60
        );

        $attendance->total_time = sprintf(
            '%02d:%02d',
            intdiv($workMinutes, 60),
            $workMinutes % 60
        );
    }

    return view('attendance-detail', [
        'attendance' => $attendance,
        'date' => $date,
        'user' => auth()->user(),
    ]);
}

    public function update(Request $request, $date)
{
    $date = Carbon::parse($date);

    $attendance = Attendance::firstOrCreate(
        [
            'user_id' => auth()->id(),
            'work_date' => $date,
        ],
        [
            'clock_in' => $request->clock_in,
        ]
    );

    $attendance->update([
        'clock_in'  => $request->clock_in,
        'clock_out' => $request->clock_out,
        'remark'    => $request->remark,
    ]);

    // 休憩（最大2回）
    foreach ($request->breaks ?? [] as $index => $breakData) {

        if (empty($breakData['start']) && empty($breakData['end'])) {
            continue;
        }

        $break = $attendance->breaks[$index] ?? null;

        if ($break) {
            $break->update([
                'break_start' => $breakData['start'],
                'break_end'   => $breakData['end'],
            ]);
        } else {
            $attendance->breaks()->create([
                'break_start' => $breakData['start'],
                'break_end'   => $breakData['end'],
            ]);
        }
    }

    return redirect()->route(
        'attendance.show',
        $date->format('Y-m-d')
    );
}
}
