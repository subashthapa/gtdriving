<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\InstructorInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AcceptInstructorInvitationController extends Controller
{
    public function show(Request $request, string $token)
    {
        $invitation = $this->validInvitation($token);
        $existingUser = User::whereRaw('lower(email) = ?', [$invitation->email])->first();

        if ($existingUser && ! $request->user()) {
            $request->session()->put('url.intended', route('instructor-invitations.show', $token));

            return redirect()->route('login')->with('status', 'Sign in to accept your instructor invitation.');
        }

        if ($existingUser && $request->user()?->isNot($existingUser)) {
            abort(403, 'This invitation belongs to another account.');
        }

        return Inertia::render('Auth/AcceptInstructorInvitation', [
            'email' => $invitation->email,
            'expiresAt' => $invitation->expires_at,
            'token' => $token,
            'existingUser' => (bool) $existingUser,
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invitation = $this->validInvitation($token);
        $existingUser = User::whereRaw('lower(email) = ?', [$invitation->email])->first();
        $validated = [];

        if ($existingUser) {
            abort_unless($request->user()?->is($existingUser), 403, 'Sign in with the invited account.');
            abort_if($existingUser->hasAnyRole(['SuperAdmin', 'Admin']), 422, 'Staff accounts cannot accept instructor invitations.');
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols(), 'confirmed'],
            ]);
        }

        $user = DB::transaction(function () use ($invitation, $existingUser, $validated) {
            $lockedInvitation = InstructorInvitation::query()->lockForUpdate()->findOrFail($invitation->id);
            abort_unless($lockedInvitation->status === 'pending', 410, 'This invitation is no longer valid.');

            $user = $existingUser ?: User::create([
                'name' => $validated['name'],
                'email' => $lockedInvitation->email,
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
            ]);

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $user->syncRoles([$lockedInvitation->role]);
            $lockedInvitation->update([
                'accepted_at' => now(),
                'accepted_user_id' => $user->id,
            ]);

            return $user;
        });

        if (! Auth::check()) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        return redirect()->route('profile.show')
            ->with('success', 'Instructor account activated. Enable two-factor authentication to protect learner information.');
    }

    private function validInvitation(string $token): InstructorInvitation
    {
        $invitation = InstructorInvitation::where('token_hash', hash('sha256', $token))->first();

        abort_unless($invitation && $invitation->status === 'pending', 410, 'This invitation is invalid, expired, or has already been used.');

        return $invitation;
    }
}
