<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Messages/Index', [
            'messages' => Message::latest()->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Messages/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'required|email|max:255',
            'session_type' => 'required|string|max:100',
            'message'      => 'required|string',
        ]);

        Message::create($validated);

        return redirect()->back()->with('success', 'Your message has been sent.');
    }

    public function show(Message $message)
    {
        return Inertia::render('Admin/Messages/Show', ['message' => $message]);
    }

    public function edit(Message $message)
    {
        return Inertia::render('Admin/Messages/Edit', ['message' => $message]);
    }

    public function update(Request $request, Message $message)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'nullable',
            'session_type' => 'nullable|string',
            'message' => 'required',
        ]);

        $message->update($validated);

        return redirect()->route('admin.messages.index')->with('success', 'Message updated.');
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message deleted.');
    }
}
