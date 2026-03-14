<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /** @test */
    public function email_is_required_for_admin_login()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // メールアドレス未入力でログイン
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // バリデーションエラーが返ることを確認
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    public function password_is_required_for_admin_login()
    {
        $admin = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // パスワード未入力でログイン
        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    public function cannot_login_with_invalid_credentials()
    {
        $admin = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // 誤ったメールアドレスでログイン
        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        // バリデーションメッセージを確認
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
