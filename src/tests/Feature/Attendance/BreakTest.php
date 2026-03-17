<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakTest extends TestCase
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
    public function break_start_button_is_visible_and_status_changes_to_on_break()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_out' => null,
        ]);

        // 出勤中の画面確認
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('出勤中');
        $response->assertSee('<button class="start-break_btn">休憩入</button>', false);

        // 休憩開始
        $this->actingAs($user)->post(route('break.start'));

        // 休憩中になったか確認
        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('休憩中');
        $response->assertSee('<button class="end-break_btn">休憩戻</button>', false);
    }

    /** @test */
    public function user_can_take_multiple_breaks_and_buttons_display_correctly()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_out' => null,
        ]);

        // 1回目の休憩
        $this->actingAs($user)->post(route('break.start'));
        $this->actingAs($user)->post(route('break.end'));

        // 2回目の休憩
        $this->actingAs($user)->post(route('break.start'));

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('休憩中');
        $response->assertSee('<button class="end-break_btn">休憩戻</button>', false);

        // 休憩戻
        $this->actingAs($user)->post(route('break.end'));

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('出勤中');
        $response->assertSee('<button class="start-break_btn">休憩入</button>', false);
    }

    /** @test */
    public function break_times_are_recorded_and_displayed_in_attendance_list()
    {
        $user = User::factory()->create();

        // 時間固定
        $fixedStart = Carbon::create(2026, 3, 16, 10, 51);
        $fixedEnd   = Carbon::create(2026, 3, 16, 11, 6);

        Carbon::setTestNow($fixedStart);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $fixedStart->toDateString(),
            'clock_out' => null,
        ]);

        // 休憩開始・終了を DB に直接挿入して固定
        $attendance->breaks()->create([
            'break_start' => $fixedStart,
            'break_end'   => $fixedEnd,
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => $fixedStart,
            'break_end' => $fixedEnd,
        ]);

        $breakDuration = $fixedEnd->diffInMinutes($fixedStart);
        $hours = floor($breakDuration / 60);
        $minutes = $breakDuration % 60;
        $displayTime = sprintf('%02d:%02d', $hours, $minutes);

        $response->assertSee($displayTime);
    }

    /** @test */
    public function break_return_button_correctly_changes_status_back_to_clocked_in()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_out' => null,
        ]);

        // 休憩開始
        $this->actingAs($user)->post(route('break.start'));

        // 休憩戻
        $this->actingAs($user)->post(route('break.end'));

        $response = $this->actingAs($user)->get(route('attendance.index'));
        $response->assertSee('出勤中');
        $response->assertSee('<button class="start-break_btn">休憩入</button>', false);
    }
}
