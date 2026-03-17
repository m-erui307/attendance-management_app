<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminLoginTest extends TestCase
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
    public function email_is_required_for_admin_login()
    {
        $admin = Admin::create([
            'email' => 'admin' . rand(1000, 9999) . '@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    public function test_password_is_required_for_admin_login()
    {
        $admin = Admin::create([
            'email' => 'admin' . rand(1000, 9999) . '@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    public function test_cannot_login_with_invalid_credentials()
    {
        $admin = Admin::create([
            'email' => 'admin' . rand(1000, 9999) . '@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong' . Str::random(5) . '@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
