<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /** @test */
    public function attendance_detail_displays_user_name()
    {
        $user = User::factory()->create(['name' => 'Taro Yamada']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00'
        ]);

        $response = $this->actingAs($user)
                        ->get(route('attendance.show', [
                            'user' => $user->id,
                            'date' => $attendance->work_date
                        ]));

        $response->assertSee('Taro Yamada');
    }

    /** @test */
    public function attendance_detail_displays_selected_date()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2023-06-01'
        ]);

        $response = $this->actingAs($user)
                        ->get(route('attendance.show', [
                            'user' => $user->id,
                            'date' => '2023-06-01'
                        ]));

        $response->assertSee('2023-06-01');
    }

    /** @test */
    public function attendance_detail_displays_clock_in_and_clock_out()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00'
        ]);

        $response = $this->actingAs($user)
                        ->get(route('attendance.show', [
                            'user' => $user->id,
                            'date' => $attendance->work_date
                        ]));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function attendance_detail_displays_break_times()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00'
        ]);

        // 休憩開始と終了
        $attendance->breaks()->create([
            'break_start' => '12:00:00',
            'break_end' => '12:30:00'
        ]);

        $response = $this->actingAs($user)
                        ->get(route('attendance.show', [
                            'user' => $user->id,
                            'date' => $attendance->work_date
                        ]));

        $response->assertSee('12:00');
        $response->assertSee('12:30');
    }
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
