<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_api_uses_sanctum_stateful_middleware(): void
    {
        $route = collect(Route::getRoutes())->first(
            fn ($route) => $route->uri() === 'api/admin/stats'
        );

        $this->assertNotNull($route);
        $this->assertContains(
            EnsureFrontendRequestsAreStateful::class,
            app('router')->gatherRouteMiddleware($route)
        );
    }

    public function test_authenticated_admin_browser_session_can_access_dashboard_stats_api(): void
    {
        Role::findOrCreate('Admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->getJson('/api/admin/stats')
            ->assertOk()
            ->assertJsonStructure([
                'total_users',
                'total_bookings',
                'total_pages',
                'total_packages',
                'upcoming_bookings',
            ]);
    }

    public function test_guest_cannot_access_dashboard_stats_api(): void
    {
        $this->getJson('/api/admin/stats')->assertUnauthorized();
    }

    public function test_authenticated_non_admin_cannot_access_dashboard_stats_api(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->getJson('/api/admin/stats')->assertForbidden();
    }
}
