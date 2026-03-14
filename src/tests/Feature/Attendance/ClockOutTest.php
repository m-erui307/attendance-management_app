<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class ClockOutTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_clock_out()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
    'user_id'=>$user->id,
    'work_date'=>now()->toDateString(),
    'clock_out'=>null
]);

        $this->actingAs($user)
            ->post(route('attendance.end'));

        $this->assertDatabaseMissing('attendances',[
            'id'=>$attendance->id,
            'clock_out'=>null
        ]);
    }

    public function user_can_clock_out()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('attendance.end'));

        $response->assertRedirect(route('attendance.index'));

        // 退勤が記録されていることを確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
        ]);

        $this->assertNotNull($attendance->fresh()->clock_out);
    }

    /** @test */
    public function clock_out_time_is_shown_in_attendance_list()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post(route('attendance.end'));

        $response = $this->actingAs($user)->get(route('attendance.list'));

        // 勤怠一覧で退勤時刻が表示されるか確認
        $response->assertSee($attendance->fresh()->clock_out->format('H:i'));
    }
}
