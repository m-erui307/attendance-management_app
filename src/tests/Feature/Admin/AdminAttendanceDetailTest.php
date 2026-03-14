<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Admin;

class AdminAttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_selected_attendance_detail()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'note' => '勤務詳細メモ',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('勤務詳細メモ');
    }

    /** @test */
    public function admin_cannot_save_attendance_with_invalid_clock_times()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '18:00:00',
            'clock_out' => '09:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'note' => '不正な時間',
            ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function admin_cannot_save_break_start_after_clock_out()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.attendance.update', $attendance->id), [
                'break_start' => '19:00',
                'break_end' => '19:30',
                'note' => '休憩不正',
            ]);

        $response->assertSessionHasErrors([
            'break_start' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function admin_cannot_save_break_end_after_clock_out()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.attendance.update', $attendance->id), [
                'break_start' => '10:00',
                'break_end' => '19:00',
                'note' => '休憩不正',
            ]);

        $response->assertSessionHasErrors([
            'break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function admin_cannot_save_without_note()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'note' => '既存メモ',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '',
            ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
