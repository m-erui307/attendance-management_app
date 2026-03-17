<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceStatusTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // シーディング済みデータを毎テストで投入
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_status_display()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);
    }

    /** @test */
    public function shows_status_as_off_work_when_user_has_not_clocked_in()
    {
        $user = User::factory()->create();

        // 勤怠データなし＝勤務外
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('勤務外');
    }

    /** @test */
    public function shows_status_as_clocked_in_when_user_is_working()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');
    }

    /** @test */
    public function shows_status_as_on_break_when_user_is_on_break()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        // breaks テーブルに休憩中レコードを作成
        $attendance->breaks()->create([
            'break_start' => now(),
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩中');
    }

    /** @test */
    public function shows_status_as_clocked_out_when_user_has_clocked_out()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤済');
    }
}
