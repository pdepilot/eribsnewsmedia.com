<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('messages.view'), 403);

        $opened = ContactMessage::query()->whereNull('read_at')->pluck('id');
        ContactMessage::query()->whereIn('id', $opened)->update(['read_at' => now()]);

        return view('admin.messages.index', [
            'messages' => ContactMessage::query()->latest()->paginate(30),
            'total' => ContactMessage::query()->count(),
            'today' => ContactMessage::query()->where('created_at', '>=', now()->startOfDay())->count(),
            'opened' => $opened,
        ]);
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('messages.view'), 403);
        $message->delete();

        return back()->with('status', 'Message deleted.');
    }
}
