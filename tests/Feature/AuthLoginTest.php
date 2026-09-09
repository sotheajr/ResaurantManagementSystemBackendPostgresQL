<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_token(): void
    {
        $role = Role::create([
            'role_name' => 'admin',
        ]);

        User::create([
            'full_name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'phone' => '0999999999',
            'password' => bcrypt('admin123'),
            'role_id' => $role->role_id,
            'status' => 'Active',
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'user',
                         'token',
                     ],
                 ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_admin_can_fetch_dashboard_stats(): void
    {
        $role = Role::create([
            'role_name' => 'staff',
        ]);

        $user = User::create([
            'full_name' => 'Admin User',
            'username' => 'dashboard-admin',
            'email' => 'dashboard@example.com',
            'phone' => '0999999999',
            'password' => bcrypt('secret123'),
            'role_id' => $role->role_id,
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/dashboard/stats?range=month');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'range',
                    'summary',
                    'chart',
                    'paymentMethods',
                    'topItems',
                    'tableStatus',
                    'recentOrders',
                ],
            ]);
    }
}
