<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\AdCreative;
use App\Models\AdNetwork;
use App\Models\AdPlacement;
use App\Models\AdUnit;
use App\Models\Advertiser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdInventoryController extends Controller
{
    public function networks(): View
    {
        $this->guard();

        return view('admin.advertising.networks.index', [
            'networks' => AdNetwork::query()->orderByDesc('priority')->orderBy('name')->get(),
        ]);
    }

    public function createNetwork(): View
    {
        $this->guard();

        return view('admin.advertising.networks.form', ['network' => new AdNetwork(['type' => 'third_party', 'priority' => 0])]);
    }

    public function storeNetwork(Request $request): RedirectResponse
    {
        $this->guard();
        AdNetwork::query()->create($this->networkPayload($request, new AdNetwork));

        return redirect()->route('admin.advertising.networks.index')->with('status', 'Network saved.');
    }

    public function editNetwork(AdNetwork $network): View
    {
        $this->guard();

        return view('admin.advertising.networks.form', ['network' => $network]);
    }

    public function updateNetwork(Request $request, AdNetwork $network): RedirectResponse
    {
        $this->guard();
        $network->update($this->networkPayload($request, $network));

        return redirect()->route('admin.advertising.networks.index')->with('status', 'Network updated.');
    }

    public function destroyNetwork(AdNetwork $network): RedirectResponse
    {
        $this->guard();
        abort_if(in_array($network->slug, ['google-adsense', 'google-ad-manager'], true), 403);
        $network->delete();

        return redirect()->route('admin.advertising.networks.index')->with('status', 'Network deleted.');
    }

    public function placements(): View
    {
        $this->guard();

        return view('admin.advertising.placements.index', [
            'placements' => AdPlacement::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function createPlacement(): View
    {
        $this->guard();

        return view('admin.advertising.placements.form', ['placement' => new AdPlacement(['device' => 'all', 'enabled' => true, 'sort_order' => 0])]);
    }

    public function storePlacement(Request $request): RedirectResponse
    {
        $this->guard();
        AdPlacement::query()->create($this->placementPayload($request, new AdPlacement));

        return redirect()->route('admin.advertising.placements.index')->with('status', 'Placement saved.');
    }

    public function editPlacement(AdPlacement $placement): View
    {
        $this->guard();

        return view('admin.advertising.placements.form', ['placement' => $placement]);
    }

    public function updatePlacement(Request $request, AdPlacement $placement): RedirectResponse
    {
        $this->guard();
        $placement->update($this->placementPayload($request, $placement));

        return redirect()->route('admin.advertising.placements.index')->with('status', 'Placement updated.');
    }

    public function destroyPlacement(AdPlacement $placement): RedirectResponse
    {
        $this->guard();
        if ($placement->campaigns()->exists() || $placement->units()->exists()) {
            return back()->with('status', 'This placement still has a campaign or an ad unit.');
        }

        $placement->delete();

        return redirect()->route('admin.advertising.placements.index')->with('status', 'Placement deleted.');
    }

    public function units(): View
    {
        $this->guard();

        return view('admin.advertising.units.index', [
            'units' => AdUnit::query()->with(['network', 'placement'])->orderBy('name')->get(),
        ]);
    }

    public function createUnit(): View
    {
        $this->guard();

        return view('admin.advertising.units.form', [
            'unit' => new AdUnit(['responsive' => true, 'device' => 'all', 'priority' => 0]),
            'networks' => AdNetwork::query()->orderBy('name')->get(),
            'placements' => AdPlacement::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function storeUnit(Request $request): RedirectResponse
    {
        $this->guard();
        AdUnit::query()->create($this->unitPayload($request));

        return redirect()->route('admin.advertising.units.index')->with('status', 'Ad unit saved.');
    }

    public function editUnit(AdUnit $unit): View
    {
        $this->guard();

        return view('admin.advertising.units.form', [
            'unit' => $unit,
            'networks' => AdNetwork::query()->orderBy('name')->get(),
            'placements' => AdPlacement::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function updateUnit(Request $request, AdUnit $unit): RedirectResponse
    {
        $this->guard();
        $unit->update($this->unitPayload($request));

        return redirect()->route('admin.advertising.units.index')->with('status', 'Ad unit updated.');
    }

    public function destroyUnit(AdUnit $unit): RedirectResponse
    {
        $this->guard();
        $unit->delete();

        return redirect()->route('admin.advertising.units.index')->with('status', 'Ad unit deleted.');
    }

    public function advertisers(): View
    {
        $this->guard();

        return view('admin.advertising.advertisers.index', [
            'advertisers' => Advertiser::query()->orderBy('name')->get(),
        ]);
    }

    public function createAdvertiser(): View
    {
        $this->guard();

        return view('admin.advertising.advertisers.form', ['advertiser' => new Advertiser(['status' => 'active'])]);
    }

    public function storeAdvertiser(Request $request): RedirectResponse
    {
        $this->guard();
        Advertiser::query()->create($this->advertiserPayload($request));

        return redirect()->route('admin.advertising.advertisers.index')->with('status', 'Advertiser saved.');
    }

    public function editAdvertiser(Advertiser $advertiser): View
    {
        $this->guard();

        return view('admin.advertising.advertisers.form', ['advertiser' => $advertiser]);
    }

    public function updateAdvertiser(Request $request, Advertiser $advertiser): RedirectResponse
    {
        $this->guard();
        $advertiser->update($this->advertiserPayload($request));

        return redirect()->route('admin.advertising.advertisers.index')->with('status', 'Advertiser updated.');
    }

    public function destroyAdvertiser(Advertiser $advertiser): RedirectResponse
    {
        $this->guard();
        if ($advertiser->campaigns()->exists()) {
            return back()->with('status', 'This advertiser still has a campaign.');
        }

        $advertiser->delete();

        return redirect()->route('admin.advertising.advertisers.index')->with('status', 'Advertiser deleted.');
    }

    public function campaigns(): View
    {
        $this->guard();

        return view('admin.advertising.campaigns.index', [
            'campaigns' => AdCampaign::query()->with(['advertiser', 'placement'])->orderByDesc('priority')->orderBy('name')->get(),
        ]);
    }

    public function createCampaign(): View
    {
        $this->guard();

        return view('admin.advertising.campaigns.form', $this->campaignForm(new AdCampaign(['status' => 'draft', 'priority' => 0])));
    }

    public function storeCampaign(Request $request): RedirectResponse
    {
        $this->guard();
        AdCampaign::query()->create($this->campaignPayload($request));

        return redirect()->route('admin.advertising.campaigns.index')->with('status', 'Campaign saved.');
    }

    public function editCampaign(AdCampaign $campaign): View
    {
        $this->guard();

        return view('admin.advertising.campaigns.form', $this->campaignForm($campaign));
    }

    public function updateCampaign(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $this->guard();
        $campaign->update($this->campaignPayload($request));

        return redirect()->route('admin.advertising.campaigns.index')->with('status', 'Campaign updated.');
    }

    public function destroyCampaign(AdCampaign $campaign): RedirectResponse
    {
        $this->guard();
        $campaign->delete();

        return redirect()->route('admin.advertising.campaigns.index')->with('status', 'Campaign deleted.');
    }

    public function creatives(): View
    {
        $this->guard();

        return view('admin.advertising.creatives.index', [
            'creatives' => AdCreative::query()->with('advertiser')->orderBy('name')->get(),
        ]);
    }

    public function createCreative(): View
    {
        $this->guard();

        return view('admin.advertising.creatives.form', [
            'creative' => new AdCreative(['type' => 'image']),
            'advertisers' => Advertiser::query()->orderBy('name')->get(),
        ]);
    }

    public function storeCreative(Request $request): RedirectResponse
    {
        $this->guard();
        AdCreative::query()->create($this->creativePayload($request, new AdCreative));

        return redirect()->route('admin.advertising.creatives.index')->with('status', 'Creative saved.');
    }

    public function editCreative(AdCreative $creative): View
    {
        $this->guard();

        return view('admin.advertising.creatives.form', [
            'creative' => $creative,
            'advertisers' => Advertiser::query()->orderBy('name')->get(),
        ]);
    }

    public function updateCreative(Request $request, AdCreative $creative): RedirectResponse
    {
        $this->guard();
        $creative->update($this->creativePayload($request, $creative));

        return redirect()->route('admin.advertising.creatives.index')->with('status', 'Creative updated.');
    }

    public function destroyCreative(AdCreative $creative): RedirectResponse
    {
        $this->guard();
        if (AdCampaign::query()->where('creative_id', $creative->id)->exists()) {
            return back()->with('status', 'This creative is still used by a campaign.');
        }

        if (filled($creative->image_path) && ! str_starts_with($creative->image_path, 'http')) {
            Storage::disk('public')->delete($creative->image_path);
        }

        $creative->delete();

        return redirect()->route('admin.advertising.creatives.index')->with('status', 'Creative deleted.');
    }

    private function networkPayload(Request $request, AdNetwork $network): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:80', Rule::unique('ad_networks', 'slug')->ignore($network->id)],
            'type' => ['required', Rule::in(AdNetwork::TYPES)],
            'publisher_id' => ['nullable', 'string', 'regex:/^ca-pub-\d{8,20}$/'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'configuration' => ['nullable', 'string', 'max:5000'],
            'credentials' => ['nullable', 'string', 'max:2000'],
        ]);

        $configuration = null;

        if (filled($data['configuration'] ?? null)) {
            $decoded = json_decode($data['configuration'], true);

            if (! is_array($decoded)) {
                throw ValidationException::withMessages([
                    'configuration' => 'Configuration must be a JSON object.',
                ]);
            }

            $configuration = $decoded;
        }

        $payload = [
            'name' => $data['name'],
            'slug' => $this->slug($data['slug'] ?? null, $data['name'], 'ad_networks', $network->id),
            'type' => $data['type'],
            'publisher_id' => $data['publisher_id'] ?? null,
            'enabled' => $request->boolean('enabled'),
            'priority' => (int) ($data['priority'] ?? 0),
            'configuration' => $configuration,
        ];

        if ($request->boolean('clear_credentials')) {
            $payload['credentials'] = null;
        } elseif (filled($data['credentials'] ?? null)) {
            $payload['credentials'] = $data['credentials'];
        }

        return $payload;
    }

    private function placementPayload(Request $request, AdPlacement $placement): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:80', Rule::unique('ad_placements', 'slug')->ignore($placement->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'device' => ['required', Rule::in(AdPlacement::DEVICES)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        return [
            'name' => $data['name'],
            'slug' => $this->slug($data['slug'] ?? null, $data['name'], 'ad_placements', $placement->id),
            'description' => $data['description'] ?? null,
            'device' => $data['device'],
            'enabled' => $request->boolean('enabled'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }

    private function unitPayload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'ad_network_id' => ['required', 'exists:ad_networks,id'],
            'ad_placement_id' => ['nullable', 'exists:ad_placements,id'],
            'ad_client' => ['nullable', 'string', 'regex:/^ca-pub-\d{8,20}$/'],
            'ad_slot' => ['nullable', 'string', 'regex:/^\d{6,20}$/'],
            'format' => ['nullable', Rule::in(AdUnit::FORMATS)],
            'device' => ['required', Rule::in(AdPlacement::DEVICES)],
            'priority' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'markup' => ['nullable', 'string', 'max:20000'],
        ]);

        return [
            ...$data,
            'responsive' => $request->boolean('responsive'),
            'enabled' => $request->boolean('enabled'),
            'priority' => (int) ($data['priority'] ?? 0),
            'ad_placement_id' => $data['ad_placement_id'] ?: null,
            'format' => $data['format'] ?: null,
        ];
    }

    private function advertiserPayload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::in(Advertiser::STATUSES)],
        ]);

        return $data;
    }

    private function campaignPayload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'advertiser_id' => ['required', 'exists:advertisers,id'],
            'ad_placement_id' => ['required', 'exists:ad_placements,id'],
            'creative_id' => ['required', 'exists:advertisement_creatives,id'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'status' => ['required', Rule::in(AdCampaign::STATUSES)],
            'click_url' => ['nullable', 'url', 'max:2048'],
        ]);

        return [
            ...$data,
            'priority' => (int) ($data['priority'] ?? 0),
            'start_at' => $data['start_at'] ?? null,
            'end_at' => $data['end_at'] ?? null,
            'click_url' => $data['click_url'] ?? null,
        ];
    }

    private function creativePayload(Request $request, AdCreative $creative): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'advertiser_id' => ['nullable', 'exists:advertisers,id'],
            'type' => ['required', Rule::in(['image', 'html'])],
            'html' => ['nullable', 'string', 'max:20000'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'image' => [Rule::requiredIf(fn () => $request->input('type') === 'image' && blank($creative->image_path)), 'nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        if (($data['type'] ?? null) === 'html' && blank($data['html'] ?? null)) {
            throw ValidationException::withMessages(['html' => 'An HTML creative needs markup.']);
        }

        $path = $creative->image_path;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $info = @getimagesize($file->getRealPath() ?: '');

            if ($info === false) {
                throw ValidationException::withMessages(['image' => 'The file is not a valid image.']);
            }

            if (filled($path) && ! str_starts_with((string) $path, 'http')) {
                Storage::disk('public')->delete($path);
            }

            $path = $file->store('ads/'.now()->format('Y/m'), 'public');
            $data['width'] = $info[0] ?? null;
            $data['height'] = $info[1] ?? null;
        }

        return [
            'advertiser_id' => $data['advertiser_id'] ?: null,
            'name' => $data['name'],
            'type' => $data['type'],
            'image_path' => $path,
            'html' => $data['type'] === 'html' ? $data['html'] : null,
            'alt_text' => $data['alt_text'] ?? null,
            'width' => $data['width'] ?? $creative->width,
            'height' => $data['height'] ?? $creative->height,
        ];
    }

    private function campaignForm(AdCampaign $campaign): array
    {
        return [
            'campaign' => $campaign,
            'advertisers' => Advertiser::query()->orderBy('name')->get(),
            'placements' => AdPlacement::query()->orderBy('sort_order')->get(),
            'creatives' => AdCreative::query()->orderBy('name')->get(),
        ];
    }

    private function slug(?string $slug, string $name, string $table, ?int $ignore): string
    {
        $base = Str::slug($slug ?: $name) ?: 'item';
        $candidate = $base;
        $suffix = 2;

        while (
            \Illuminate\Support\Facades\DB::table($table)
                ->where('slug', $candidate)
                ->when($ignore, fn ($query) => $query->where('id', '!=', $ignore))
                ->exists()
        ) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function guard(): void
    {
        abort_unless(auth()->user()->hasPermission('ads.manage'), 403);
    }
}
