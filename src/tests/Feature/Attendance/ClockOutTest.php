<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\DatabaseMigrations;
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

    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // シーディング済みデータを毎テストで投入
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /** @test */
    public function user_can_see_clock_out_button_and_clock_out()
    {
        // 勤務中のユーザーを作成
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_out' => null, // まだ退勤していない
        ]);

        // 勤怠ページにアクセスして「退勤」ボタンがあるか確認
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('退勤');

        // 退勤処理
        $response = $this->actingAs($user)->post(route('attendance.end'));

        // 処理後は勤怠一覧にリダイレクト
        $response->assertRedirect(route('attendance.index'));

        // DBに退勤時刻が記録されていること
        $this->assertNotNull($attendance->fresh()->clock_out);

        // 退勤後、画面上で「退勤済」と表示されるか確認
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('退勤済');
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

        // 退勤処理
        $this->actingAs($user)->post(route('attendance.end'));

        // 勤怠一覧画面にアクセス
        $response = $this->actingAs($user)->get(route('attendance.list'));

        // 勤怠一覧に退勤時刻が表示されているか確認
        $clockOutTime = $attendance->fresh()->clock_out->format('H:i');
        $response->assertSee($clockOutTime);
    }
}
