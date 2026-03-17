<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class DatetimeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    

    /** @test */
    public function attendance_page_displays_current_datetime()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $week = ['日','月','火','水','木','金','土'];
        $now = now();
        $formatted = $now->format('Y年n月j日').'('.$week[$now->dayOfWeek].")\n".$now->format('H:i');

        $response->assertSee($formatted);
    }
}
