<?php

namespace Tests\Feature;

use App\Models\InstructorInvitation;
use App\Models\User;
use App\Notifications\InstructorInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InstructorInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('SuperAdmin', 'web');
        Role::findOrCreate('Admin', 'web');
        Role::findOrCreate('Instructor', 'web');
        Role::findOrCreate('Learner', 'web');
    }

    public function test_only_superadmin_can_manage_instructor_invitations(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->actingAs($admin)
            ->get(route('admin.instructor-invitations.index'))
            ->assertForbidden();

        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)
            ->get(route('admin.instructor-invitations.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/InstructorInvitations/Index')
                ->has('invitations.data', 0)
            );
    }

    public function test_superadmin_can_send_a_hashed_single_use_invitation(): void
    {
        Notification::fake();
        $superAdmin = $this->superAdmin();
        $capturedToken = null;

        $this->actingAs($superAdmin)
            ->post(route('admin.instructor-invitations.store'), [
                'email' => 'New.Instructor@example.com',
            ])
            ->assertRedirect();

        $invitation = InstructorInvitation::firstOrFail();

        $this->assertSame('new.instructor@example.com', $invitation->email);
        $this->assertSame('pending', $invitation->status);
        $this->assertSame($superAdmin->id, $invitation->invited_by);

        Notification::assertSentOnDemand(
            InstructorInvitationNotification::class,
            function (InstructorInvitationNotification $notification, array $channels, AnonymousNotifiable $notifiable) use (&$capturedToken) {
                $capturedToken = $notification->token;

                return $notifiable->routes['mail'] === 'new.instructor@example.com'
                    && $channels === ['mail'];
            }
        );

        $this->assertNotNull($capturedToken);
        $this->assertNotSame($capturedToken, $invitation->token_hash);
        $this->assertSame(hash('sha256', $capturedToken), $invitation->token_hash);
    }

    public function test_new_user_can_accept_invitation_and_becomes_verified_instructor(): void
    {
        [$invitation, $token] = $this->invitation('candidate@example.com');

        $this->post(route('instructor-invitations.accept', $token), [
            'name' => 'Candidate Instructor',
            'phone' => '0400000000',
            'password' => 'Instructor!234',
            'password_confirmation' => 'Instructor!234',
        ])->assertRedirect(route('profile.show'));

        $user = User::where('email', 'candidate@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue($user->hasRole('Instructor'));
        $this->assertFalse($user->hasRole('Learner'));
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->assertSame($user->id, $invitation->fresh()->accepted_user_id);

        $this->post(route('instructor-invitations.accept', $token), [
            'name' => 'Reuse Attempt',
            'password' => 'Instructor!234',
            'password_confirmation' => 'Instructor!234',
        ])->assertStatus(410);
    }

    public function test_existing_learner_must_sign_in_and_can_accept_own_invitation(): void
    {
        $learner = User::factory()->create(['email' => 'learner@example.com']);
        $learner->assignRole('Learner');
        [$invitation, $token] = $this->invitation($learner->email);

        $this->get(route('instructor-invitations.show', $token))
            ->assertRedirect(route('login'));

        $this->actingAs($learner)
            ->post(route('instructor-invitations.accept', $token))
            ->assertRedirect(route('profile.show'));

        $this->assertTrue($learner->fresh()->hasRole('Instructor'));
        $this->assertFalse($learner->fresh()->hasRole('Learner'));
        $this->assertSame($learner->id, $invitation->fresh()->accepted_user_id);
    }

    public function test_invitation_cannot_be_accepted_by_another_signed_in_user(): void
    {
        $learner = User::factory()->create(['email' => 'invited@example.com']);
        $learner->assignRole('Learner');
        $otherUser = User::factory()->create();
        [, $token] = $this->invitation($learner->email);

        $this->actingAs($otherUser)
            ->post(route('instructor-invitations.accept', $token))
            ->assertForbidden();

        $this->assertTrue($learner->fresh()->hasRole('Learner'));
    }

    public function test_expired_or_revoked_invitation_is_rejected(): void
    {
        $token = 'expired-token';
        InstructorInvitation::create([
            'email' => 'expired@example.com',
            'token_hash' => hash('sha256', $token),
            'role' => 'Instructor',
            'invited_by' => $this->superAdmin()->id,
            'expires_at' => now()->subMinute(),
        ]);

        $this->get(route('instructor-invitations.show', $token))->assertStatus(410);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('SuperAdmin');

        return $user;
    }

    private function invitation(string $email): array
    {
        $token = 'test-token-'.str_replace('@', '-', $email);
        $invitation = InstructorInvitation::create([
            'email' => strtolower($email),
            'token_hash' => hash('sha256', $token),
            'role' => 'Instructor',
            'invited_by' => $this->superAdmin()->id,
            'expires_at' => now()->addHours(72),
        ]);

        return [$invitation, $token];
    }
}
