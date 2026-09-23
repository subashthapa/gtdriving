<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppRoutesAndFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_route_is_accessible(): void
    {
        $this->get('/')
            ->assertOk();
    }

    public function test_contact_message_submission_persists_message(): void
    {
        $response = $this->from('/')->post(route('messages.store'), [
            'name' => 'Test Person',
            'phone' => '1234567890',
            'email' => 'person@example.com',
            'session_type' => 'Manual',
            'message' => 'I need a lesson next week.',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('messages', [
            'name' => 'Test Person',
            'email' => 'person@example.com',
            'session_type' => 'Manual',
        ]);
    }

    public function test_available_slots_api_requires_date(): void
    {
        $this->getJson('/api/available-slots')
            ->assertStatus(400)
            ->assertJson([
                'error' => 'Date is required',
            ]);
    }

    public function test_available_slots_api_returns_timeslots(): void
    {
        DB::table('timeslots')->insert([
            [
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->getJson('/api/available-slots?date=2026-02-21')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment([
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
            ]);
    }

    public function test_available_slots_api_excludes_booked_slot_for_instructor(): void
    {
        Role::findOrCreate('Instructor', 'web');
        $instructor = User::factory()->create();
        $instructor->assignRole('Instructor');

        DB::table('timeslots')->insert([
            [
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'is_visible' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Booking::create([
            'user_id' => User::factory()->create()->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'start_date' => '2026-02-21',
            'end_date' => '2026-02-21',
            'instructor' => $instructor->id,
        ]);

        $this->getJson("/api/available-slots?date=2026-02-21&instructor={$instructor->id}")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
            ]);
    }

    public function test_get_time_slots_api_returns_timeslots(): void
    {
        DB::table('timeslots')->insert([
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'is_visible' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/get-time-slots')
            ->assertOk()
            ->assertJsonFragment([
                'start_time' => '08:00:00',
                'end_time' => '09:00:00',
            ]);
    }

    public function test_booked_dates_api_returns_bookings(): void
    {
        DB::table('bookings')->insert([
            'user_id' => null,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'start_date' => '2026-02-21',
            'end_date' => '2026-02-21',
            'name' => 'Walk-in',
            'email' => 'walkin@example.com',
            'phone' => '1231231234',
            'instructor' => 3,
            'approved_by' => null,
            'instructions' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson('/api/booked-dates')
            ->assertOk()
            ->assertJsonFragment([
                'start_date' => '2026-02-21',
            ]);
    }

    public function test_admin_pages_index_uses_pages_controller_component(): void
    {
        DB::table('pages')->insert([
            'title' => 'About Us',
            'subtitle' => 'Intro',
            'description' => 'Description',
            'image' => null,
            'thumbnail' => null,
            'added_by' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->createAdminUser())
            ->get(route('admin.pages.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Pages/Index')
            ->has('pages', 1)
        );
    }

    public function test_admin_can_create_page(): void
    {
        $response = $this->actingAs($this->createAdminUser())
            ->post(route('admin.pages.store'), [
                'title' => 'Training Programs',
                'subtitle' => 'Programs',
                'description' => 'Page copy',
            ]);

        $response->assertRedirect(route('admin.pages.index'));

        $this->assertDatabaseHas('pages', [
            'title' => 'Training Programs',
            'subtitle' => 'Programs',
        ]);
    }

    public function test_admin_can_create_package_without_debug_interruptions(): void
    {
        $response = $this->actingAs($this->createAdminUser())
            ->post(route('admin.packages.store'), [
                'package_name' => 'Starter',
                'subtitle' => 'Basic package',
                'price' => '199.99',
                'status' => true,
            ]);

        $response->assertRedirect(route('admin.packages.index'));

        $this->assertDatabaseHas('packages', [
            'package_name' => 'Starter',
        ]);
    }

    public function test_admin_can_access_users_dashboard_route(): void
    {
        $response = $this->actingAs($this->createAdminUser())
            ->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')
        );
    }

    public function test_booking_update_is_forbidden_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $booking = Booking::create([
            'user_id' => $owner->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'start_date' => '2026-02-21',
            'end_date' => '2026-02-21',
            'instructor' => 3,
        ]);

        $this->actingAs($otherUser)
            ->put(route('bookings.update', $booking), [
                'start_date' => '2026-02-22',
                'end_date' => '2026-02-22',
                'start_time' => '11:00',
                'end_time' => '12:00',
                'instructions' => 'Rescheduled',
            ])
            ->assertForbidden();
    }

    public function test_booking_owner_can_update_booking(): void
    {
        $owner = User::factory()->create();
        $originalDate = now()->addDays(2)->toDateString();
        $rescheduledDate = now()->addDays(3)->toDateString();

        $booking = Booking::create([
            'user_id' => $owner->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'start_date' => $originalDate,
            'end_date' => $originalDate,
            'instructor' => 3,
        ]);

        $this->actingAs($owner)
            ->from('/dashboard')
            ->put(route('bookings.update', $booking), [
                'start_date' => $rescheduledDate,
                'end_date' => $rescheduledDate,
                'start_time' => '11:00',
                'end_time' => '12:00',
                'instructions' => 'Rescheduled',
            ])
            ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'start_date' => $rescheduledDate,
            'start_time' => '11:00',
            'instructions' => 'Rescheduled',
        ]);
    }

    public function test_booking_owner_can_delete_booking(): void
    {
        $owner = User::factory()->create();

        $booking = Booking::create([
            'user_id' => $owner->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'instructor' => 3,
        ]);

        $this->actingAs($owner)
            ->from('/dashboard')
            ->delete(route('bookings.destroy', $booking))
            ->assertRedirect('/dashboard');

        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id,
        ]);
    }

    public function test_booking_owner_cannot_cancel_past_booking(): void
    {
        $owner = User::factory()->create();

        $booking = Booking::create([
            'user_id' => $owner->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'instructor' => 3,
        ]);

        $this->actingAs($owner)
            ->deleteJson(route('bookings.destroy', $booking))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('booking');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    public function test_booking_requires_a_valid_instructor(): void
    {
        $learner = User::factory()->create();

        $this->actingAs($learner)
            ->postJson(route('saveBooking'), [
                'name' => $learner->name,
                'email' => $learner->email,
                'phone' => '1234567890',
                'date' => now()->addDay()->toDateString(),
                'start_time' => '09:00',
                'end_time' => '10:00',
                'instructor' => 999999,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('instructor');
    }

    public function test_guest_can_create_booking_and_new_learner_account_is_created(): void
    {
        Role::findOrCreate('Learner', 'web');
        Role::findOrCreate('Instructor', 'web');

        $instructor = User::factory()->create();
        $instructor->assignRole('Instructor');
        $bookingDate = now()->addDay()->toDateString();

        $response = $this->postJson(route('saveBooking'), [
            'name' => 'Guest Learner',
            'email' => 'guest.learner@example.com',
            'phone' => '1234567890',
            'date' => $bookingDate,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'instructor' => $instructor->id,
            'instructions' => 'First lesson',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Booking created successfully!',
            ]);

        $learner = User::where('email', 'guest.learner@example.com')->first();

        $this->assertNotNull($learner);
        $this->assertTrue($learner->hasRole('Learner'));
        $this->assertDatabaseHas('bookings', [
            'user_id' => $learner->id,
            'instructor' => $instructor->id,
            'start_date' => $bookingDate,
        ]);
    }

    public function test_existing_learner_can_book_without_duplicate_account_creation(): void
    {
        Role::findOrCreate('Learner', 'web');
        Role::findOrCreate('Instructor', 'web');

        $learner = User::factory()->create([
            'name' => 'Existing Learner',
            'email' => 'existing.learner@example.com',
            'phone' => '0001112222',
        ]);
        $learner->assignRole('Learner');

        $instructor = User::factory()->create();
        $instructor->assignRole('Instructor');
        $bookingDate = now()->addDays(2)->toDateString();

        $response = $this->postJson(route('saveBooking'), [
            'name' => 'Existing Learner Updated',
            'email' => 'existing.learner@example.com',
            'phone' => '9998887777',
            'date' => $bookingDate,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'instructor' => $instructor->id,
        ]);

        $response->assertOk();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', [
            'id' => $learner->id,
            'name' => 'Existing Learner',
            'phone' => '0001112222',
        ]);
        $this->assertDatabaseHas('bookings', [
            'user_id' => $learner->id,
            'instructor' => $instructor->id,
            'start_date' => $bookingDate,
        ]);
    }

    public function test_cash_booking_stores_server_calculated_pending_payment(): void
    {
        Role::findOrCreate('Instructor', 'web');
        config(['services.instructor.hourly_rate' => 60]);

        $learner = User::factory()->create(['phone' => '0400000000']);
        $instructor = User::factory()->create();
        $instructor->assignRole('Instructor');

        $this->actingAs($learner)->postJson(route('saveBooking'), [
            'name' => $learner->name,
            'email' => $learner->email,
            'phone' => $learner->phone,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:30',
            'instructor' => $instructor->id,
            'payment_method' => 'cash',
            'amount' => 1,
        ])->assertOk()->assertJsonPath('payment.amount', 90);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $learner->id,
            'amount' => 90,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
        ]);
    }

    public function test_assigned_instructor_can_mark_cash_booking_as_paid(): void
    {
        Role::findOrCreate('Instructor', 'web');
        $instructor = User::factory()->create();
        $instructor->assignRole('Instructor');
        $booking = Booking::create([
            'user_id' => User::factory()->create()->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'instructor' => $instructor->id,
            'amount' => 60,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
        ]);

        $this->actingAs($instructor)
            ->patch(route('bookings.payment.update', $booking), ['payment_status' => 'paid'])
            ->assertRedirect();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'payment_status' => 'paid']);
        $this->assertNotNull($booking->fresh()->paid_at);
        $this->assertEquals('60.00', $instructor->fresh()->income);
    }

    public function test_unassigned_instructor_cannot_change_payment_status(): void
    {
        Role::findOrCreate('Instructor', 'web');
        $assignedInstructor = User::factory()->create();
        $assignedInstructor->assignRole('Instructor');
        $otherInstructor = User::factory()->create();
        $otherInstructor->assignRole('Instructor');
        $booking = Booking::create([
            'user_id' => User::factory()->create()->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'instructor' => $assignedInstructor->id,
            'amount' => 60,
        ]);

        $this->actingAs($otherInstructor)
            ->patch(route('bookings.payment.update', $booking), ['payment_status' => 'paid'])
            ->assertForbidden();
    }

    public function test_paid_booking_cannot_be_cancelled_before_refund(): void
    {
        $learner = User::factory()->create();
        $booking = Booking::create([
            'user_id' => $learner->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'instructor' => User::factory()->create()->id,
            'amount' => 60,
            'payment_status' => 'paid',
        ]);

        $this->actingAs($learner)
            ->deleteJson(route('bookings.destroy', $booking))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('booking');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    public function test_instructor_can_view_bookings_page(): void
    {
        Role::findOrCreate('Instructor', 'web');

        $instructor = User::factory()->create();
        $instructor->assignRole('Instructor');

        $otherLearner = User::factory()->create();
        $otherInstructor = User::factory()->create();

        Booking::create([
            'user_id' => $otherLearner->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'start_date' => '2026-02-26',
            'end_date' => '2026-02-26',
            'instructor' => $instructor->id,
            'instructions' => 'Owned booking',
        ]);

        Booking::create([
            'user_id' => $otherLearner->id,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'start_date' => '2026-02-26',
            'end_date' => '2026-02-26',
            'instructor' => $otherInstructor->id,
            'instructions' => 'Other booking',
        ]);

        $response = $this->actingAs($instructor)->get(route('bookings.index'));

        $response->assertOk();
        $response->assertViewIs('booking');
        $response->assertViewHas('confirmed', function ($bookings) use ($instructor) {
            return $bookings->count() === 1 && (int) $bookings->first()->instructor === (int) $instructor->id;
        });
    }

    public function test_instructor_dashboard_includes_learners_history_earnings_and_future_calendar_data(): void
    {
        Role::findOrCreate('Instructor', 'web');
        Role::findOrCreate('Learner', 'web');

        config(['services.instructor.hourly_rate' => 60]);

        $instructor = User::factory()->create();
        $instructor->assignRole('Instructor');

        $learnerA = User::factory()->create();
        $learnerA->assignRole('Learner');
        $learnerB = User::factory()->create();
        $learnerB->assignRole('Learner');

        Booking::create([
            'user_id' => $learnerA->id,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'instructor' => $instructor->id,
            'instructions' => 'Future lesson',
            'amount' => 120,
            'payment_status' => 'pending',
        ]);

        Booking::create([
            'user_id' => $learnerB->id,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->subDays(2)->toDateString(),
            'instructor' => $instructor->id,
            'instructions' => 'Past lesson',
            'amount' => 60,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($instructor)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('isInstructor', true)
            ->has('instructorLearners', 2)
            ->has('instructorFutureBookings', 1)
            ->has('instructorPastBookings', 1)
            ->where('instructorStats.total_learners', 2)
            ->where('instructorStats.total_lessons', 2)
            ->where('instructorStats.upcoming_lessons', 1)
            ->where('instructorStats.past_lessons', 1)
            ->where('instructorStats.total_earnings', 60)
            ->where('instructorStats.outstanding_payments', 120)
        );
    }

    public function test_admin_has_separate_instructor_and_learner_sections(): void
    {
        Role::findOrCreate('Instructor', 'web');
        Role::findOrCreate('Learner', 'web');

        $instructor = User::factory()->create();
        $instructor->assignRole('Instructor');
        $learner = User::factory()->create();
        $learner->assignRole('Learner');
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->get(route('admin.instructors.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->where('title', 'Instructors')
                ->where('role', 'Instructor')
                ->has('users', 1)
                ->where('users.0.id', $instructor->id)
            );

        $this->actingAs($admin)
            ->get(route('admin.learners.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->where('title', 'Learners')
                ->where('role', 'Learner')
                ->has('users', 1)
                ->where('users.0.id', $learner->id)
            );
    }

    public function test_admin_can_update_a_booked_session(): void
    {
        Role::findOrCreate('Instructor', 'web');
        Role::findOrCreate('Learner', 'web');

        $admin = $this->createAdminUser();
        $learner = User::factory()->create();
        $learner->assignRole('Learner');
        $instructor = User::factory()->create();
        $instructor->assignRole('Instructor');
        $newDate = now()->addDays(5)->toDateString();

        $booking = Booking::create([
            'user_id' => $learner->id,
            'instructor' => $instructor->id,
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'amount' => 60,
            'payment_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.bookings.update', $booking), [
                'user_id' => $learner->id,
                'instructor' => $instructor->id,
                'start_date' => $newDate,
                'end_date' => $newDate,
                'start_time' => '10:00',
                'end_time' => '11:30',
                'instructions' => 'Updated by admin',
                'payment_status' => 'paid',
                'payment_reference' => 'CASH-001',
            ])
            ->assertRedirect(route('admin.bookings.index'));

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'start_date' => $newDate,
            'start_time' => '10:00',
            'end_time' => '11:30',
            'instructions' => 'Updated by admin',
            'amount' => 90,
            'payment_status' => 'paid',
            'payment_reference' => 'CASH-001',
        ]);
        $this->assertEquals('90.00', $instructor->fresh()->income);
    }

    private function createAdminUser(): User
    {
        Role::findOrCreate('Admin', 'web');

        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }
}
