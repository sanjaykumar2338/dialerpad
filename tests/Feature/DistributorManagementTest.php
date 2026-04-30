<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_distributor(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.distributors.store'), [
            'name' => 'West Region Distributor',
            'email' => 'west@example.com',
            'phone' => '+15551234567',
            'company_name' => 'West Distribution LLC',
            'status' => User::STATUS_ACTIVE,
            'role' => User::ROLE_DISTRIBUTOR,
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertRedirect(route('admin.distributors.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'West Region Distributor',
            'email' => 'west@example.com',
            'phone' => '+15551234567',
            'company_name' => 'West Distribution LLC',
            'status' => User::STATUS_ACTIVE,
            'role' => User::ROLE_DISTRIBUTOR,
            'is_admin' => false,
        ]);

        $this->assertNotNull(User::where('email', 'west@example.com')->firstOrFail()->email_verified_at);
    }

    public function test_distributor_can_login_and_redirects_to_dashboard(): void
    {
        $distributor = User::factory()->create([
            'email' => 'distributor@example.com',
            'status' => User::STATUS_ACTIVE,
            'role' => User::ROLE_DISTRIBUTOR,
        ]);

        $response = $this->post(route('distributor.login.store'), [
            'email' => $distributor->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($distributor);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_inactive_distributor_cannot_login(): void
    {
        $distributor = User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'role' => User::ROLE_DISTRIBUTOR,
        ]);

        $this->post(route('distributor.login.store'), [
            'email' => $distributor->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_distributor_cannot_access_admin_pages(): void
    {
        $distributor = User::factory()->create([
            'status' => User::STATUS_ACTIVE,
            'role' => User::ROLE_DISTRIBUTOR,
        ]);

        $this->actingAs($distributor)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_landing_page_has_distributor_login_button(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Distributor Login')
            ->assertSee(route('distributor.login', absolute: false));
    }
}
