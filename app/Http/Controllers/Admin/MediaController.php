<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaRequest;
use App\Models\Media;
use App\Services\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('media.manage'), 403);

        return view('admin.media.index', [
            'media' => Media::query()->with('uploader')->latest()->paginate(24),
        ]);
    }

    public function store(MediaRequest $request, MediaLibrary $library): RedirectResponse
    {
        $library->store($request->file('file'), $request->user(), $request->safe()->only(['alt_text', 'caption', 'credit']));

        return back()->with('status', 'Image uploaded.');
    }

    public function destroy(Media $medium, MediaLibrary $library): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('media.manage'), 403);
        $library->delete($medium);

        return back()->with('status', 'Image deleted.');
    }
}
