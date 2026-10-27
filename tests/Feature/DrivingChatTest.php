<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DrivingChatTest extends TestCase
{
    use RefreshDatabase;

    private function say(string $message)
    {
        return $this->postJson('/chat/messages', ['message' => $message])->assertOk();
    }

    private function prepareBooking(): User
    {
        Role::findOrCreate('Instructor', 'web');
        $instructor = User::factory()->create();
        $instructor->assignRole('Instructor');
        DB::table('timeslots')->insert(['start_time' => '09:00:00', 'end_time' => '10:00:00', 'is_visible' => true]);
        $this->say('Book a lesson');
        $this->say('60 minutes');
        $this->say(now()->addDay()->toDateString());
        $this->say('Any available instructor')->assertJsonPath('step', 'slot');
        $this->say('09:00');
        $this->contact();
        $this->say('123 Test Street');
        $this->say('First lesson, nervous learner')->assertJsonPath('step', 'confirm');

        return $instructor;
    }

    private function contact(): void
    {
        $this->say('Chat Learner');
        $this->say('chat@example.com');
        $this->say('0400000000');
    }

    public function test_booking_is_only_created_after_confirmation_and_cannot_be_submitted_twice(): void
    {
        $instructor = $this->prepareBooking();
        $this->assertDatabaseCount('bookings', 0);
        $this->say('Confirm and submit')->assertJsonPath('step', 'menu');
        $this->assertDatabaseHas('bookings', ['instructor' => $instructor->id, 'payment_status' => 'pending']);
        $this->assertStringContainsString('Pickup: 123 Test Street', Booking::first()->instructions);
        $this->say('Confirm and submit');
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_conflicting_booking_is_rechecked_at_confirmation(): void
    {
        $instructor = $this->prepareBooking();
        Booking::create(['user_id' => User::factory()->create()->id, 'instructor' => $instructor->id,
            'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00', 'end_time' => '10:00']);
        $this->say('Confirm and submit')->assertJsonPath('step', 'date');
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseMissing('users', ['email' => 'chat@example.com']);
    }

    public function test_instructor_question_is_saved_in_existing_inbox(): void
    {
        $this->say('Ask an instructor');
        $this->say('Can we work on parking?');
        $this->contact();
        $this->assertDatabaseCount('messages', 0);
        $this->say('Confirm and submit');
        $this->assertDatabaseHas('messages', ['email' => 'chat@example.com', 'message' => 'Can we work on parking?', 'session_type' => 'Chat instructor enquiry']);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_invalid_email_does_not_advance_and_reset_clears_details(): void
    {
        $this->say('Ask an instructor');
        $this->say('Need help');
        $this->say('Learner');
        $this->postJson('/chat/messages', ['message' => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->getJson('/chat')->assertJsonPath('step', 'email');
        $this->deleteJson('/chat')->assertOk()->assertJsonPath('step', 'menu')->assertJsonCount(1, 'messages');
        $this->getJson('/chat')->assertJsonPath('data', []);
    }

    public function test_prices_use_server_configuration_and_unknown_questions_have_handoff(): void
    {
        config(['services.instructor.hourly_rate' => 75]);
        $response = $this->say('Lesson prices');
        $this->assertStringContainsString('75.00', $response->json('messages.2.text'));
        $this->say('What is your refund policy?')->assertJsonFragment(['Ask an instructor']);
    }

    public function test_empty_instructor_list_and_past_dates_are_handled(): void
    {
        $this->say('Book a lesson');
        $this->say('60 minutes');
        $this->postJson('/chat/messages', ['message' => now()->subDay()->toDateString()])->assertUnprocessable();
        $this->say(now()->addDay()->toDateString())->assertJsonPath('step', 'menu');
        $this->assertDatabaseCount('bookings', 0);
    }
}
