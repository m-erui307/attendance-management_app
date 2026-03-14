<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Admin;

class AdminAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /** @test */
    public function admin_can_see_all_users_attendance_for_today()
    {
        $admin = Admin::factory()->create();

        // ユーザーと勤怠を作成
        $users = User::factory(3)->create();
        foreach ($users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => now()->toDateString(),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]);
        }

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list'));

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee('09:00'); // 出勤時刻
            $response->assertSee('18:00'); // 退勤時刻
        }
    }

    /** @test */
    public function admin_attendance_list_shows_current_date()
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list'));

        $today = now()->format('Y-m-d');
        $response->assertSee($today);
    }

    /** @test */
    public function admin_can_see_previous_day_attendance()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $yesterday = now()->subDay()->toDateString();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $yesterday,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', ['date' => $yesterday]));

        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function admin_can_see_next_day_attendance()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $tomorrow = now()->addDay()->toDateString();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $tomorrow,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.list', ['date' => $tomorrow]));

        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
