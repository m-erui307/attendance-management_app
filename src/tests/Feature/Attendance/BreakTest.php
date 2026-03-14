<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class BreakTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_break_start()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
    'user_id'=>$user->id,
    'work_date'=>now()->toDateString(),
    'clock_out'=>null
]);

        $this->actingAs($user)
            ->post(route('break.start'));

        $this->assertDatabaseCount('breaks',1);
    }

    public function user_can_start_break()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('break.start'));

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseCount('breaks', 1);
    }

    /** @test */
    public function user_can_end_break()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post(route('break.start'));

        $response = $this->actingAs($user)->post(route('break.end'));
        $response->assertRedirect(route('attendance.index'));

        // breaksテーブルにbreak_endがセットされているか確認
        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
        ]);
    }

    /** @test */
    public function user_can_take_multiple_breaks()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_out' => null,
        ]);

        // 1回目の休憩
        $this->actingAs($user)->post(route('break.start'));
        $this->actingAs($user)->post(route('break.end'));

        // 2回目の休憩
        $this->actingAs($user)->post(route('break.start'));
        $this->actingAs($user)->post(route('break.end'));

        $this->assertDatabaseCount('breaks', 2);
    }

    /** @test */
    public function break_time_is_shown_in_attendance_list()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post(route('break.start'));
        $this->actingAs($user)->post(route('break.end'));

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $this->assertDatabaseCount('breaks', 1);
        $response->assertSee(now()->format('H:i'));
    }
}
