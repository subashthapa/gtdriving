<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return $this->renderUsers('All Users');
    }

    public function instructors()
    {
        return $this->renderUsers('Instructors', 'Instructor');
    }

    public function learners()
    {
        return $this->renderUsers('Learners', 'Learner');
    }

    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()],
            'phone' => 'nullable|string|max:20',
            'role' => ['nullable', Rule::in($this->assignableRoles())],
        ]);

        $role = $validated['role'] ?? null;
        unset($validated['role']);
        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);

        if ($role) {
            $user->syncRoles([$role]);
        }

        event(new Registered($user));

        return redirect()->route('admin.users.index')->with('success', 'User created');
    }

    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->load('roles:id,name'),
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'role' => ['nullable', Rule::in($this->assignableRoles())],
        ]);

        $role = $validated['role'] ?? null;
        $emailChanged = $validated['email'] !== $user->email;
        unset($validated['role']);

        $user->update($validated);

        if ($emailChanged) {
            $user->forceFill(['email_verified_at' => null])->save();
        }

        if ($role) {
            $user->syncRoles([$role]);
        }

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted');
    }

    private function renderUsers(string $title, ?string $role = null)
    {
        $query = User::query()
            ->with('roles:id,name')
            ->withCount(['learnerBookings', 'instructorBookings'])
            ->orderBy('name');

        if ($role) {
            $query->role($role);
        }

        return Inertia::render('Admin/Users/Index', [
            'users' => $query->get(),
            'title' => $title,
            'role' => $role,
        ]);
    }

    private function assignableRoles(): array
    {
        return auth()->user()->hasRole('SuperAdmin')
            ? ['SuperAdmin', 'Admin', 'Instructor', 'Learner']
            : ['Instructor', 'Learner'];
    }
}
