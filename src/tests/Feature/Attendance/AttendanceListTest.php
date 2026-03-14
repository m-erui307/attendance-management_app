<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_attendance_list_display()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        for($i=1;$i<=3;$i++){
    Attendance::factory()->create([
        'user_id'=>$user->id,
        'work_date'=>now()->subDays($i)->toDateString()
    ]);
}

for($i=4;$i<=6;$i++){
    Attendance::factory()->create([
        'user_id'=>$other->id,
        'work_date'=>now()->subDays($i)->toDateString()
    ]);
}

        $response = $this->actingAs($user)
    ->get(route('attendance.list'));

        $response->assertStatus(200);
    }

    public function test_only_my_attendance_display()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Attendance::factory()->count(3)->create([
            'user_id'=>$user->id
        ]);

        Attendance::factory()->count(3)->create([
            'user_id'=>$other->id
        ]);

        $response = $this->actingAs($user)
    ->get(route('attendance.list'));

        $response->assertStatus(200);
    }

    public function user_sees_all_own_attendance()
    {
        $user = User::factory()->create();

        // 自分の勤怠を3件作成
        $attendances = Attendance::factory()->count(3)->create([
            'user_id' => $user->id
        ]);

        // 他ユーザーの勤怠を作成
        Attendance::factory()->count(2)->create();

        $response = $this->actingAs($user)
                        ->get(route('attendance.list'));

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->work_date);
        }
    }

    /** @test */
    public function current_month_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $currentMonth = now()->format('Y年n月');
        $response->assertSee($currentMonth);
    }

    /** @test */
    public function previous_month_button_shows_previous_month_attendance()
    {
        $user = User::factory()->create();

        $prevMonthDate = now()->subMonth();
        $attendancePrev = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $prevMonthDate->toDateString(),
        ]);

        $response = $this->actingAs($user)
                        ->get(route('attendance.list', ['month' => $prevMonthDate->format('Y-m')]));

        $response->assertSee($attendancePrev->work_date);
    }

    /** @test */
    public function next_month_button_shows_next_month_attendance()
    {
        $user = User::factory()->create();

        $nextMonthDate = now()->addMonth();
        $attendanceNext = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextMonthDate->toDateString(),
        ]);

        $response = $this->actingAs($user)
                        ->get(route('attendance.list', ['month' => $nextMonthDate->format('Y-m')]));

        $response->assertSee($attendanceNext->work_date);
    }

    /** @test */
    public function clicking_detail_redirects_to_attendance_detail()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
                        ->get(route('attendance.show', [
                            'user' => $attendance->user_id,
                            'date' => $attendance->work_date
                        ]));

        $response->assertStatus(200);
        $response->assertSee('勤務外'); // 初期状態では勤務外のステータスが表示される
    }
}
