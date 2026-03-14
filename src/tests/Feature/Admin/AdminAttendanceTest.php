<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_admin_can_view_attendance_list()
    {
        $admin = User::factory()->create();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
        'user_id'=>$user->id
    ]);

        $response = $this->actingAs($admin,'admin')
    ->get(route('admin.attendance.show',[
        'user'=>$attendance->user_id,
        'date'=>$attendance->work_date
    ]));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_attendance_detail()
    {
        $admin = User::factory()->create();

        $attendance = Attendance::factory()->create();

        $response = $this->actingAs($admin,'admin')
    ->get(route('admin.attendance.show',[
        'user'=>$attendance->user_id,
        'date'=>$attendance->work_date
    ]));

        $response->assertStatus(200);
    }
}
