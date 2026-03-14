<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Admin;
use App\Models\AttendanceRequest;

class AdminAttendanceRequestTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /** @test */
    public function can_view_pending_requests()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending', // 承認待ち
        ]);

        $response = $this->actingAs($admin, 'web')
            ->get(route('admin.attendance.requests'));

        $response->assertStatus(200);
        $response->assertSee('承認待ち'); // タブ表示など確認
        $response->assertSee($user->name);
    }

    /** @test */
    public function can_view_approved_requests()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved', // 承認済み
        ]);

        $response = $this->actingAs($admin, 'web')
            ->get(route('admin.attendance.requests'));

        $response->assertStatus(200);
        $response->assertSee('承認済み'); // タブ表示など確認
        $response->assertSee($user->name);
    }

    /** @test */
    public function can_view_request_details()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $request = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => '出勤時間修正',
        ]);

        $response = $this->actingAs($admin, 'web')
            ->get(route('admin.attendance.request.show', $request->id));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('出勤時間修正');
    }

    /** @test */
    public function can_approve_request()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $request = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'clock_in' => '08:30:00', // 修正内容
            'clock_out' => '18:30:00',
        ]);

        $response = $this->actingAs($admin, 'web')
            ->post(route('admin.attendance.request.approve', $request->id));

        $response->assertRedirect(route('admin.attendance.requests'));
        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '08:30:00',
            'clock_out' => '18:30:00',
        ]);
    }
}
