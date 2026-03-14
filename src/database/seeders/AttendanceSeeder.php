<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        $start = Carbon::now()->subMonth()->startOfMonth();
        $end = Carbon::yesterday();

        foreach ($users as $user) {

            $current = $start->copy();

            while ($current <= $end) {

                $monthStart = $current->copy()->startOfMonth();
                $monthEnd = $current->copy()->endOfMonth();

                if ($monthEnd > $end) {
                    $monthEnd = $end->copy();
                }

                // 平日取得
                $workDays = collect();
                $date = $monthStart->copy();

                while ($date <= $monthEnd) {

                    if (!$date->isWeekend()) {
                        $workDays->push($date->copy());
                    }

                    $date->addDay();
                }

                // 月20日程度
                $workDays = $workDays->shuffle()->take(20);

                foreach ($workDays as $day) {

                    $clockIn = $day->copy()->setTime(rand(8,9), rand(0,59));
                    $clockOut = $clockIn->copy()->addHours(rand(8,9));

                    $attendance = Attendance::create([
                        'user_id' => $user->id,
                        'work_date' => $day->format('Y-m-d'),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'remark' => null,
                    ]);

                    // 休憩 0〜3回
                    $breakCount = rand(0,3);

                    for ($i = 0; $i < $breakCount; $i++) {

                        $breakStart = $clockIn->copy()->addHours(rand(2,5));
                        $breakEnd = $breakStart->copy()->addMinutes(rand(15,60));

                        if ($breakEnd > $clockOut) {
                            continue;
                        }

                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => $breakStart,
                            'break_end' => $breakEnd,
                        ]);
                    }
                }

                $current->addMonth();
            }
        }
    }
}
