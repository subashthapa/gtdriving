<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_find_unverified_users(): void
    {
        $unverified = User::factory()->unverified()->create();
        User::factory()->create();

        $this->actingAs($this->staffUser('Admin'))
            ->get(route('admin.users.unverified'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->where('title', 'Unverified Users')
                ->where('verification', 'unverified')
                ->has('users', 1)
                ->where('users.0.id', $unverified->id)
                ->where('users.0.email_verified_at', null)
            );
    }

    public function test_admin_can_manually_verify_a_user(): void
    {
        Event::fake([Verified::class]);
        $user = User::factory()->unverified()->create();

        $this->actingAs($this->staffUser('Admin'))
            ->patch(route('admin.users.verify', $user))
            ->assertRedirect()
            ->assertSessionHas('success', 'User email verified.');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        Event::assertDispatched(Verified::class, fn (Verified $event) => $event->user->is($user));
    }

    public function test_superadmin_can_manually_verify_a_user(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($this->staffUser('SuperAdmin'))
            ->patch(route('admin.users.verify', $user))
            ->assertRedirect();

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_verifying_an_already_verified_user_is_idempotent(): void
    {
        Event::fake([Verified::class]);
        $user = User::factory()->create();

        $this->actingAs($this->staffUser('Admin'))
            ->patch(route('admin.users.verify', $user))
            ->assertRedirect()
            ->assertSessionHas('success', 'User email is already verified.');

        Event::assertNotDispatched(Verified::class);
    }

    public function test_non_admin_cannot_manually_verify_a_user(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.users.verify', $user))
            ->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    private function staffUser(string $role): User
    {
        Role::findOrCreate($role, 'web');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
