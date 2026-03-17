<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

class AttendanceEditTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    

    /** @test */
    public function clock_in_cannot_be_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->put(route('attendance.update', $attendance->id), [
                'clock_in' => '19:00:00',
                'clock_out' => '18:00:00',
                'remark' => '修正テスト',
            ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function break_start_cannot_be_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->put(route('attendance.update', $attendance->id), [
                'break_start' => '19:00:00',
                'break_end' => '19:30:00',
                'remark' => '休憩開始チェック',
            ]);

        $response->assertSessionHasErrors([
            'break_start' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function break_end_cannot_be_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->put(route('attendance.update', $attendance->id), [
                'break_start' => '17:00:00',
                'break_end' => '19:00:00',
                'remark' => '休憩終了チェック',
            ]);

        $response->assertSessionHasErrors([
            'break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function remarks_is_required()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('attendance.update', $attendance->id), [
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'remark' => '',
            ]);

        $response->assertSessionHasErrors([
            'remark' => '備考を記入してください',
        ]);
    }

    /** @test */
    public function attendance_edit_request_is_created()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->put(route('attendance.update', $attendance->id), [
                'clock_in' => '08:30:00',
                'clock_out' => '18:00:00',
                'remark' => '修正申請テスト',
            ]);

        $this->assertDatabaseHas('attendance_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending', // 承認待ち
        ]);
    }
}
