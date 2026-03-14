<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Admin;

class AdminUserInfoTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /** @test */
    public function admin_can_view_all_users_info()
    {
        $admin = Admin::factory()->create();
        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index')); // スタッフ一覧ページ

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /** @test */
    public function admin_can_view_selected_user_attendance_list()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        // 勤怠データを作成
        $attendances = Attendance::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', $user->id));

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->work_date);
        }
    }

    /** @test */
    public function admin_can_navigate_previous_and_next_month_in_user_attendance()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        // 前月・翌月の勤怠データ
        $prevMonth = now()->subMonth()->format('Y-m-d');
        $nextMonth = now()->addMonth()->format('Y-m-d');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $prevMonth,
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextMonth,
        ]);

        // 前月ボタン押下
        $responsePrev = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', $user->id) . '?month=' . now()->subMonth()->format('Y-m'));
        $responsePrev->assertSee($prevMonth);

        // 翌月ボタン押下
        $responseNext = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', $user->id) . '?month=' . now()->addMonth()->format('Y-m'));
        $responseNext->assertSee($nextMonth);
    }

    /** @test */
    public function admin_can_navigate_to_attendance_detail_from_list()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->work_date);
    }
}
