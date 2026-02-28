<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_to_login(): void
    {
        $this->withoutVite();

        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_user_can_log_in_from_admin_login_page(): void
    {
        $this->withoutVite();

        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_guest_cannot_access_admin_api(): void
    {
        $response = $this->getJson('/api/admin/meta');

        $response->assertUnauthorized();
    }
}
