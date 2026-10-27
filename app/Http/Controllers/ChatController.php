<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    private const MENU = ['Book a lesson', 'Lesson prices', 'Driving advice', 'Ask an instructor'];

    public function show(Request $request)
    {
        return response()->json($request->session()->get('driving_chat', $this->initial()));
    }

    public function reset(Request $request)
    {
        $request->session()->forget('driving_chat');

        return response()->json($this->initial());
    }

    public function send(Request $request)
    {
        $input = $request->validate(['message' => 'required|string|max:2000']);
        $text = trim($input['message']);
        $state = $request->session()->get('driving_chat', $this->initial());
        $state['messages'][] = ['role' => 'user', 'text' => $text];
        $reply = $this->advance($state, $text, $request);
        $state['messages'][] = ['role' => 'assistant', 'text' => $reply];
        $state['messages'] = array_slice($state['messages'], -60);
        $request->session()->put('driving_chat', $state);

        return response()->json($state);
    }

    private function initial(): array
    {
        return [
            'step' => 'menu', 'data' => [], 'choices' => self::MENU, 'inputType' => 'text',
            'messages' => [['role' => 'assistant', 'text' => 'Hi! I’m the GT Driving automated assistant. I can book a lesson, explain lesson pricing, share general learning tips, or send your question to our team. How can I help?']],
        ];
    }

    private function prompt(array &$state, string $step, string $text, array $choices = [], string $type = 'text'): string
    {
        $state['step'] = $step;
        $state['choices'] = $choices;
        $state['inputType'] = $type;

        return $text;
    }

    private function menu(array &$state, string $text): string
    {
        $state['data'] = [];

        return $this->prompt($state, 'menu', $text, self::MENU);
    }

    private function advance(array &$s, string $text, Request $request): string
    {
        $d = &$s['data'];
        $bookings = app(BookingController::class);
        switch ($s['step']) {
            case 'menu':
                if (preg_match('/book|lesson time|availability/i', $text)) {
                    if ($request->user()?->hasAnyRole(['Admin', 'SuperAdmin'])) {
                        return $this->menu($s, 'Please use the admin booking page to select the learner you are booking for. I can still help with information or instructor questions.');
                    }
                    $d = ['purpose' => 'booking'];
                    return $this->prompt($s, 'duration', 'Let’s book your lesson. How long would you like?', ['60 minutes', '90 minutes', '120 minutes']);
                }
                if (preg_match('/price|cost|pay|package/i', $text)) {
                    $rate = number_format((float) config('services.instructor.hourly_rate', 60), 2);
                    $currency = config('services.payments.currency', 'AUD');
                    return $this->menu($s, "Individual lessons are {$currency} {$rate} per hour, with the total calculated from your lesson length. Pay your instructor in cash; payment is recorded as pending when you book. For package inclusions or a custom quote, choose Ask an instructor.");
                }
                if (preg_match('/advice|tip|nervous|park|test|first lesson/i', $text)) {
                    return $this->prompt($s, 'advice', 'What would you like help preparing for? These are general learning tips from the automated assistant, not a personal assessment from an instructor.', ['First lesson', 'Feeling nervous', 'Parking practice', 'Test preparation']);
                }
                if (preg_match('/instructor|question|contact|human|help/i', $text)) {
                    $d = ['purpose' => 'question'];
                    return $this->prompt($s, 'question', 'What would you like to ask? Include your learning goals or the lesson information you need. Our team will receive your question for follow-up by email or phone; this is not live chat.');
                }
                return $this->menu($s, 'I can help with bookings, lesson prices and general learning tips. For other questions, choose Ask an instructor and I’ll collect your details for our team.');
            case 'advice':
                $tips = [
                    'First lesson' => 'Tell your instructor about your experience and goals beforehand. Bring your learner permit and any glasses you use for driving. Ask them to explain the controls and agree on a comfortable pace before setting off.',
                    'Feeling nervous' => 'Tell your instructor what makes you nervous. Ask to start in a quiet practice area, work on one skill at a time, and agree on a way to request a break. Your instructor can tailor the lesson to your confidence level.',
                    'Parking practice' => 'Ask your instructor to demonstrate the manoeuvre, then practise slowly in a suitable area under supervision. Focus on observation, vehicle control and checking your surroundings rather than rushing.',
                    'Test preparation' => 'Ask for a mock assessment and feedback on the skills that need practice. Review your observations, positioning and control with your instructor. Confirm current test requirements with your licensing authority before test day.',
                ];
                return isset($tips[$text]) ? $this->menu($s, $tips[$text].' For personalised guidance, choose Ask an instructor.') : 'Please choose one of the advice topics below.';
            case 'duration':
                if (! in_array($text, ['60 minutes', '90 minutes', '120 minutes'], true)) {
                    return 'Please choose a lesson duration below.';
                }
                $d['duration'] = (int) $text;
                return $this->prompt($s, 'date', 'Which date would you like? Enter a future date. Lesson times use the school’s timezone: '.config('app.timezone').'.', [], 'date');
            case 'date':
                Validator::make(['date' => $text], ['date' => 'required|date_format:Y-m-d|after_or_equal:today'])->validate();
                $d['date'] = $text;
                $instructors = $bookings->fetchInstructors()->getData(true);
                if (! $instructors) {
                    return $this->menu($s, 'There are no instructors available to book online yet. Choose Ask an instructor to request a lesson.');
                }
                $d['instructors'] = collect($instructors)->mapWithKeys(fn ($i) => [$i['name'].' (#'.$i['id'].')' => $i['id']])->all();
                return $this->prompt($s, 'instructor', 'Choose your instructor, or let us find someone available.', ['Any available instructor', ...array_keys($d['instructors'])]);
            case 'instructor':
                if ($text !== 'Any available instructor' && ! isset($d['instructors'][$text])) {
                    return 'Please choose an instructor below.';
                }
                $d['instructor'] = $d['instructors'][$text] ?? null;
                $d['instructor_name'] = $text;
                return $this->slots($s);
            case 'slot':
                if (! isset($d['slots'][$text])) {
                    return 'Please choose one of the available lesson times below.';
                }
                $slot = $d['slots'][$text];
                $d['start_time'] = substr($slot['start_time'], 0, 5);
                $d['end_time'] = Carbon::parse($d['start_time'])->addMinutes($d['duration'])->format('H:i');
                $d['instructor'] = $slot['available_instructor_id'] ?? $d['instructor'];
                $d['instructor_name'] = $slot['available_instructor_name'] ?? $d['instructor_name'];
                return $this->prompt($s, 'name', 'What is your full name? Your contact and lesson details will be saved with your booking for our team.');
            case 'question':
                $d['question'] = $text;
                return $this->prompt($s, 'name', 'What is your full name? We’ll save your question and contact details in the team’s inbox when you confirm.');
            case 'name':
                Validator::make(['name' => $text], ['name' => 'required|string|max:255'])->validate();
                $d['name'] = $text;
                return $this->prompt($s, 'email', 'What email address should we use to contact you?', [], 'email');
            case 'email':
                Validator::make(['email' => $text], ['email' => 'required|email|max:255'])->validate();
                $d['email'] = $text;
                return $this->prompt($s, 'phone', 'What is your phone number?', [], 'tel');
            case 'phone':
                Validator::make(['phone' => $text], ['phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9 ()-]{6,20}$/']])->validate();
                $d['phone'] = $text;
                if ($d['purpose'] === 'question') {
                    return $this->review($s);
                }
                return $this->prompt($s, 'pickup', 'Where should your instructor pick you up? Enter your address or meeting point.');
            case 'pickup':
                $d['pickup'] = $text;
                return $this->prompt($s, 'experience', 'Tell us your experience level and what you want to work on (for example, first lesson, parking or test preparation).');
            case 'experience':
                $d['experience'] = $text;
                return $this->review($s);
            case 'confirm':
                if ($text !== 'Confirm and submit') {
                    return 'Choose Confirm and submit to save these details, or Start over to change them.';
                }
                if ($d['purpose'] === 'question') {
                    $message = Message::create([
                        'name' => $d['name'], 'email' => $d['email'], 'phone' => $d['phone'],
                        'session_type' => 'Chat instructor enquiry', 'message' => $d['question'],
                    ]);
                    return $this->menu($s, "Your question has been saved to our team’s inbox (reference #{$message->id}). The team can follow up using the contact details you provided. No lesson has been booked.");
                }
                if (Carbon::parse($d['date'].' '.$d['start_time'])->isPast()) {
                    return $this->prompt($s, 'date', 'That lesson time has passed. Please choose a new date.', [], 'date');
                }
                try {
                    $result = DB::transaction(fn () => $bookings->store(new Request([
                        'name' => $d['name'], 'email' => $d['email'], 'phone' => $d['phone'],
                        'date' => $d['date'], 'start_time' => $d['start_time'], 'end_time' => $d['end_time'],
                        'instructor' => $d['instructor'], 'payment_method' => 'cash',
                        'instructions' => "Chat contact: {$d['name']} / {$d['email']} / {$d['phone']}\nPickup: {$d['pickup']}\nExperience and goals: {$d['experience']}",
                    ])));
                } catch (ValidationException $e) {
                    if (isset($e->errors()['start_time'])) {
                        return $this->slots($s, 'That time is no longer available. ');
                    }
                    throw $e;
                }
                $amount = number_format($result->getData(true)['payment']['amount'], 2);
                return $this->menu($s, "Your lesson is booked for {$d['date']}, {$d['start_time']}–{$d['end_time']} with {$d['instructor_name']}. Total: ".config('services.payments.currency', 'AUD')." {$amount}, payable in cash to your instructor. Pickup: {$d['pickup']}. Your learning goals have been saved with the booking.");
        }

        return $this->menu($s, 'How can I help?');
    }

    private function slots(array &$s, string $prefix = ''): string
    {
        $d = &$s['data'];
        $slots = app(BookingController::class)->getAvailableSlots(new Request([
            'date' => $d['date'], 'duration' => $d['duration'], 'instructor' => $d['instructor'],
        ]))->getData(true);
        $d['slots'] = collect($slots)->filter(function ($slot) use ($d) {
            $start = Carbon::parse($d['date'].' '.$slot['start_time']);
            return $start->isFuture() && $start->copy()->addMinutes($d['duration'])->isSameDay($start);
        })->mapWithKeys(fn ($slot) => [substr($slot['start_time'], 0, 5) => $slot])->all();
        if (! $d['slots']) {
            return $this->prompt($s, 'date', $prefix.'No times are available for that selection. Try another date, or Start over to send a question to our team.', [], 'date');
        }

        return $this->prompt($s, 'slot', $prefix.'Choose an available start time. Availability is checked again when you confirm.', array_keys($d['slots']));
    }

    private function review(array &$s): string
    {
        $d = $s['data'];
        $summary = "Please review before submitting:\nName: {$d['name']}\nEmail: {$d['email']}\nPhone: {$d['phone']}\n";
        if ($d['purpose'] === 'question') {
            $summary .= "Question: {$d['question']}\nThis will be saved in our team’s inbox for follow-up.";
        } else {
            $amount = number_format($d['duration'] / 60 * (float) config('services.instructor.hourly_rate', 60), 2);
            $summary .= "Lesson: {$d['date']}, {$d['start_time']}–{$d['end_time']}\nInstructor: {$d['instructor_name']}\nPickup: {$d['pickup']}\nGoals: {$d['experience']}\nTotal: ".config('services.payments.currency', 'AUD')." {$amount} (cash, payable to instructor).";
        }

        return $this->prompt($s, 'confirm', $summary, ['Confirm and submit']);
    }
}
