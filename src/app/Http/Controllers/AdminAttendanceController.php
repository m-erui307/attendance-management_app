<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
{
    // 日付指定（例: 2026-02-12）
    $date = $request->query('date');

    $targetDate = $date
        ? Carbon::createFromFormat('Y-m-d', $date)
        : Carbon::today();

    $prevDate = $targetDate->copy()->subDay()->format('Y-m-d');
    $nextDate = $targetDate->copy()->addDay()->format('Y-m-d');

    // 全ユーザー取得
    $users = User::all();

    $calendar = [];

    foreach ($users as $user) {

        // その日の勤怠を取得
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $targetDate)
            ->first();

        $calendar[] = [
            'user' => $user,
            'attendance' => $attendance,
        ];
    }

    return view('admin_attendance-list', compact(
        'targetDate',
        'prevDate',
        'nextDate',
        'calendar'
    ));
}

    public function show($userId, $date)
{
    $date = \Carbon\Carbon::parse($date);

    $user = \App\Models\User::findOrFail($userId);

    $attendance = \App\Models\Attendance::with('breaks')
        ->where('user_id', $user->id)
        ->where('work_date', $date)
        ->first();

    $breaks = collect($attendance?->breaks ?? [])
        ->filter(function ($break) {
            // 空の休憩は除外
            return !empty($break->break_start) || !empty($break->break_end);
        })
        ->map(function ($break) {
            return [
                'break_start' => $break->break_start,
                'break_end' => $break->break_end,
            ];
        })
        ->values()
        ->toArray();

    return view('admin_attendance-detail', [
        'attendance' => $attendance,
        'user' => $user,
        'date' => $date,
        'breaks' => $breaks,
    ]);
}

    public function logout(Request $request)
{
    Auth::guard('admin')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/admin/login');
}

    public function exportCsv($user, Request $request)
{
    $month = $request->query('month', now()->format('Y-m'));
    $targetMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

    // 勤怠データ取得
    $attendances = Attendance::with('breaks')
        ->where('user_id', $user)
        ->whereYear('work_date', $targetMonth->year)
        ->whereMonth('work_date', $targetMonth->month)
        ->get();

    $filename = $targetMonth->format('Y_m') . '_attendance.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename={$filename}",
    ];

    $callback = function () use ($attendances) {
        $handle = fopen('php://output', 'w');

        // ヘッダー行
        fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

        foreach ($attendances as $attendance) {
            $breakMinutes = $attendance->breaks->sum(function ($b) {
                if (!$b->break_start || !$b->break_end) return 0;
                return Carbon::parse($b->break_start)
                    ->diffInMinutes(Carbon::parse($b->break_end));
            });

            $workMinutes = ($attendance->clock_in && $attendance->clock_out)
                ? Carbon::parse($attendance->clock_in)->diffInMinutes(Carbon::parse($attendance->clock_out)) - $breakMinutes
                : 0;

            $breakTime = sprintf('%02d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
            $totalTime = sprintf('%02d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);

            fputcsv($handle, [
                $attendance->work_date->format('Y/m/d'),
                $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                $breakTime,
                $totalTime,
            ]);
        }

        fclose($handle);
    };

    return Response::stream($callback, 200, $headers);
}

    public function update(Request $request, $userId, $date)
{
    $date = Carbon::parse($date);

    // 対象ユーザー取得
    $user = User::findOrFail($userId);

    // 勤怠を取得（なければ作成）
    $attendance = Attendance::firstOrCreate(
        [
            'user_id' => $user->id,
            'work_date' => $date,
        ]
    );

    // 勤怠更新
    $attendance->clock_in = $request->clock_in ?: null;
    $attendance->clock_out = $request->clock_out ?: null;
    $attendance->save();

    // 既存の休憩を全削除
    $attendance->breaks()->delete();

    // 休憩を再登録
    if ($request->breaks) {
        foreach ($request->breaks as $break) {

            // 空の休憩は保存しない
            if (empty($break['start']) && empty($break['end'])) {
                continue;
            }

            $attendance->breaks()->create([
                'break_start' => $break['start'] ?: null,
                'break_end'   => $break['end'] ?: null,
            ]);
        }
    }

    return redirect()
        ->route('admin.attendance.list', [
            'date' => $date->format('Y-m-d')
        ]);
}
}
