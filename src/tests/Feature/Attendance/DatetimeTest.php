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

    use RefreshDatabase;

    /** @test */
    public function attendance_page_displays_current_datetime()
    {
        // テスト用ユーザーを作成
        $user = User::factory()->create();

        // 勤怠ページにログインしてアクセス
        $response = $this->actingAs($user)->get('/attendance');

        // 現在日時をUIで表示される形式に整形（例: Y-m-d H:i）
        $week = ['日','月','火','水','木','金','土'];
$now = now();
$formatted = $now->format('Y年n月j日').'('.$week[$now->dayOfWeek].")\n".$now->format('H:i');

        // レスポンスに現在日時が含まれているか確認
        $response->assertSee($now);
    }
}
