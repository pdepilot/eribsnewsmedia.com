<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdvertisementRequest;
use App\Models\Advertisement;
use App\Services\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('ads.manage'), 403);

        return view('admin.ads.index', [
            'ads' => Advertisement::query()->orderBy('placement')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->hasPermission('ads.manage'), 403);

        return view('admin.ads.form', ['ad' => new Advertisement(['type' => 'html', 'placement' => 'sidebar'])]);
    }

    public function store(AdvertisementRequest $request, MediaLibrary $library): RedirectResponse
    {
        $data = $this->payload($request, $library);
        Advertisement::query()->create($data);

        return redirect()->route('admin.ads.index')->with('status', 'Advertisement saved.');
    }

    public function edit(Advertisement $advertisement): View
    {
        abort_unless(auth()->user()->hasPermission('ads.manage'), 403);

        return view('admin.ads.form', ['ad' => $advertisement]);
    }

    public function update(AdvertisementRequest $request, Advertisement $advertisement, MediaLibrary $library): RedirectResponse
    {
        $advertisement->update($this->payload($request, $library));

        return redirect()->route('admin.ads.index')->with('status', 'Advertisement updated.');
    }

    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('ads.manage'), 403);
        $advertisement->delete();

        return redirect()->route('admin.ads.index')->with('status', 'Advertisement deleted.');
    }

    private function payload(AdvertisementRequest $request, MediaLibrary $library): array
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->file('image')) {
            $media = $library->store($request->file('image'), $request->user(), [
                'alt_text' => $data['alt_text'] ?? null,
            ]);
            $data['image_path'] = $media->path;
        }

        unset($data['image']);

        return $data;
    }
}
