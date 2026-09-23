<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_admin_cannot_access_admin_routes(): void
    {
        Role::findOrCreate('Admin', 'web');
        $admin = User::factory()->unverified()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin)
            ->get(route('admin.bookings.index'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_admin_can_access_admin_routes(): void
    {
        Role::findOrCreate('Admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin)
            ->get(route('admin.bookings.index'))
            ->assertOk();
    }

    public function test_security_headers_are_added_to_https_responses(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '172.18.0.2'])
            ->withHeaders([
                'X-Forwarded-Port' => '443',
                'X-Forwarded-Proto' => 'https',
            ])
            ->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_admin_status_is_shared_on_the_authenticated_user(): void
    {
        Role::findOrCreate('SuperAdmin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('SuperAdmin');

        $this->assertTrue($admin->fresh()->is_admin);
        $this->assertTrue($admin->fresh()->is_super_admin);
    }
}
