<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AdminUserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_admin_can_view_users()
    {
        $admin = User::factory()->create();

        User::factory()->count(5)->create();

        $response = $this->actingAs($admin,'admin')
        ->get(route('admin.staff.list'));

        $response->assertStatus(200);
    }
}
