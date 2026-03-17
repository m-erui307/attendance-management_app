<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class ClockInTest extends TestCase
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
    public function clock_in_time_is_visible_in_attendance_list()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('attendance.start'));

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertSee(now()->format('H:i'));
    }

    /** @test */
    public function clock_in_button_works_correctly()
    {
        // 1. ステータスが勤務外のユーザーにログインする
        $user = User::factory()->create();

        // 勤怠画面を開く
        $response = $this->actingAs($user)->get(route('attendance.index'));

        // 画面に「出勤」ボタンと「勤務外」のステータスが表示されていることを確認
        $response->assertSee('出勤');
        $response->assertSee('勤務外');

        // 2. 出勤の処理を行う
        $this->actingAs($user)->post(route('attendance.start'));

        // 画面を再度開く
        $response = $this->actingAs($user)->get(route('attendance.index'));

        // 3. 出勤後のステータスが「出勤中」になっていることを確認
        $response->assertSee('出勤中');

        // 出勤ボタンは表示されなくなることを確認
        $response->assertDontSee('<button class="clock-in_btn">出勤</button>', false);

        // DBに勤怠レコードが作成されていることも確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => today(),
        ]);
    }

    /** @test */
    public function user_can_only_clock_in_once_per_day_and_button_is_hidden_after_clock_out()
    {
        // 退勤済ユーザーを作成
        $user = User::factory()->create();

        // まず1日の出勤を作成し、退勤済にする
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        // ログインして勤怠画面を開く
        $response = $this->actingAs($user)->get(route('attendance.index'));

        // 画面上に「出勤」ボタンは表示されない
        $response->assertDontSee('<button class="clock-in_btn">出勤</button>', false);

        // 勤怠ステータスが「退勤済」で表示される
        $response->assertSee('退勤済');

        // DB上でも2回目の出勤は作れないことを確認
        $this->actingAs($user)->post(route('attendance.start'));
        $this->assertCount(1, Attendance::where('user_id', $user->id)->get());
    }
}
