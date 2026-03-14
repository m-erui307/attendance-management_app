<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_login_email_required()
    {
        $response = $this->post('/login',[
            'email'=>'',
            'password'=>'password'
        ]);

        $response->assertSessionHasErrors([
        'email' => 'メールアドレスを入力してください',
    ]);
    }

    public function test_login_success()
    {
        $user = User::factory()->create([
            'password'=>bcrypt('password')
        ]);

        $response = $this->post('/login',[
            'email'=>$user->email,
            'password'=>'password'
        ]);

        $response->assertRedirect('/attendance');
    }

    public function test_password_required()
{
    $user = User::factory()->create([
        'password' => bcrypt('password123')
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => '',
    ]);

    $response->assertSessionHasErrors([
        'password' => 'パスワードを入力してください',
    ]);
}

public function test_invalid_login()
{
    $user = User::factory()->create([
        'password' => bcrypt('password123')
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'ログイン情報が登録されていません',
    ]);
}
}
