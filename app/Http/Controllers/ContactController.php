<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse
    {
        ContactMessage::query()->create($request->validated());

        return redirect()
            ->route('pages.contact')
            ->with('status', 'Your message has been received.');
    }
}
