<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstructorInvitation;
use App\Models\User;
use App\Notifications\InstructorInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class InstructorInvitationController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/InstructorInvitations/Index', [
            'invitations' => InstructorInvitation::query()
                ->with(['inviter:id,name', 'acceptedUser:id,name,email'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $email = Str::lower(trim($validated['email']));
        $this->ensureEligibleEmail($email);

        InstructorInvitation::query()
            ->where('email', $email)
            ->active()
            ->update(['revoked_at' => now()]);

        [$invitation, $token] = $this->createInvitation($email, $request->user()->id);
        $this->sendInvitation($invitation, $token);

        return back()->with('success', 'Instructor invitation sent.');
    }

    public function resend(Request $request, InstructorInvitation $invitation)
    {
        if ($invitation->accepted_at) {
            throw ValidationException::withMessages([
                'invitation' => 'An accepted invitation cannot be resent.',
            ]);
        }

        $this->ensureEligibleEmail($invitation->email);
        $token = Str::random(64);
        $invitation->update([
            'token_hash' => hash('sha256', $token),
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addHours(72),
            'revoked_at' => null,
        ]);

        $this->sendInvitation($invitation->fresh('inviter'), $token);

        return back()->with('success', 'A new invitation link was sent.');
    }

    public function revoke(InstructorInvitation $invitation)
    {
        if (! $invitation->accepted_at) {
            $invitation->update(['revoked_at' => now()]);
        }

        return back()->with('success', 'Instructor invitation revoked.');
    }

    private function createInvitation(string $email, int $inviterId): array
    {
        $token = Str::random(64);
        $invitation = InstructorInvitation::create([
            'email' => $email,
            'token_hash' => hash('sha256', $token),
            'role' => 'Instructor',
            'invited_by' => $inviterId,
            'expires_at' => now()->addHours(72),
        ]);

        return [$invitation->load('inviter'), $token];
    }

    private function sendInvitation(InstructorInvitation $invitation, string $token): void
    {
        Notification::route('mail', $invitation->email)
            ->notify(new InstructorInvitationNotification($invitation, $token));
    }

    private function ensureEligibleEmail(string $email): void
    {
        $user = User::whereRaw('lower(email) = ?', [$email])->first();

        if ($user?->hasAnyRole(['SuperAdmin', 'Admin', 'Instructor'])) {
            throw ValidationException::withMessages([
                'email' => 'This account already has staff access.',
            ]);
        }
    }
}
