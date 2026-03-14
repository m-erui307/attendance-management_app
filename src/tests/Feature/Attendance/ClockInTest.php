<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ClockInTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_clock_in()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('attendance.start'));

        $this->assertDatabaseHas('attendances',[
            'user_id'=>$user->id
        ]);
    }

    public function user_can_clock_in_once_per_day()
    {
        $user = User::factory()->create();

        // 1回目の出勤
        $response = $this->actingAs($user)
            ->post(route('attendance.start'));
        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);

        // 2回目の出勤はできない
        $response2 = $this->actingAs($user)
            ->post(route('attendance.start'));
        $response2->assertSessionHas('error'); // エラーメッセージ
    }

    /** @test */
    public function clock_in_time_is_shown_in_attendance_list()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('attendance.start'));

        $response = $this->actingAs($user)->get(route('attendance.list'));

        // 出勤時刻の H:i 表示を確認
        $response->assertSee(now()->format('H:i'));
    }
}
