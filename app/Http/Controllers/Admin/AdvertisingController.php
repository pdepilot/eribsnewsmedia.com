<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\AdEvent;
use App\Models\AdNetwork;
use App\Models\AdPlacement;
use App\Models\AdUnit;
use App\Models\AdsTxtLine;
use App\Services\Advertising\AdvertisingService;
use App\Services\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvertisingController extends Controller
{
    public function index(AdvertisingService $advertising): View
    {
        $this->guard();
        $advertising->syncSchedules();

        $today = now()->startOfDay();

        return view('admin.advertising.index', [
            'campaigns' => AdCampaign::query()->where('status', 'active')->count(),
            'networks' => AdNetwork::query()->where('enabled', true)->count(),
            'placements' => AdPlacement::query()->count(),
            'impressions' => AdEvent::query()->where('type', 'impression')->where('created_at', '>=', $today)->count(),
            'clicks' => AdEvent::query()->where('type', 'click')->where('created_at', '>=', $today)->count(),
            'expiring' => AdCampaign::query()
                ->where('status', 'active')
                ->whereNotNull('end_at')
                ->whereBetween('end_at', [now(), now()->addDays(7)])
                ->orderBy('end_at')
                ->limit(8)
                ->get(),
            'publisherSaved' => $advertising->publisherId() !== null,
            'adsenseOn' => $advertising->adsenseOn(),
        ]);
    }

    public function settings(AdvertisingService $advertising): View
    {
        $this->guard();
        $network = AdNetwork::query()->where('slug', 'google-adsense')->first();

        return view('admin.advertising.settings', [
            'network' => $network,
            'publisherId' => $network?->publisher_id ?: '',
            'adsenseOn' => $advertising->adsenseOn(),
            'autoAds' => (bool) ($network?->configuration['auto_ads'] ?? false),
            'advertisingOn' => $advertising->advertisingEnabled(),
        ]);
    }

    public function updateSettings(Request $request, Settings $settings): RedirectResponse
    {
        $this->guard();

        $data = $request->validate([
            'publisher_id' => ['nullable', 'string', 'regex:/^ca-pub-\d{8,20}$/'],
        ]);

        $settings->setMany([
            'advertising_enabled' => $request->boolean('advertising_enabled') ? '1' : '0',
            'adsense_enabled' => $request->boolean('adsense_enabled') ? '1' : '0',
            'adsense_auto_ads' => $request->boolean('adsense_auto_ads') ? '1' : '0',
        ]);

        $network = AdNetwork::query()->firstOrCreate(
            ['slug' => 'google-adsense'],
            [
                'name' => 'Google AdSense',
                'type' => 'adsense',
                'enabled' => false,
                'priority' => 10,
            ],
        );

        $configuration = $network->configuration ?? [];
        $configuration['auto_ads'] = $request->boolean('adsense_auto_ads');
        $network->fill([
            'publisher_id' => $data['publisher_id'] ?? null,
            'enabled' => $request->boolean('adsense_enabled'),
            'configuration' => $configuration,
        ])->save();

        return redirect()->route('admin.advertising.settings')->with('status', 'Advertising settings saved. AdSense is not treated as connected until this publisher ID is confirmed in your AdSense account.');
    }

    public function adsTxt(): View
    {
        $this->guard();

        $lines = AdsTxtLine::query()->orderBy('sort_order')->orderBy('id')->pluck('line')->implode("\n");

        return view('admin.advertising.ads-txt', ['lines' => $lines]);
    }

    public function updateAdsTxt(Request $request): RedirectResponse
    {
        $this->guard();

        $raw = (string) $request->validate([
            'lines' => ['nullable', 'string', 'max:20000'],
        ])['lines'];

        $parsed = [];

        foreach (preg_split("/\r\n|\n|\r/", $raw) as $index => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (preg_match('/^[a-z0-9.-]+\s*,\s*[^,\s]+\s*,\s*(DIRECT|RESELLER)(\s*,\s*[a-f0-9]+)?$/i', $line) !== 1) {
                return back()->withInput()->withErrors([
                    'lines' => 'Line '.($index + 1).' is not an ads.txt record. Use domain, publisher id, DIRECT or RESELLER.',
                ]);
            }

            $parsed[] = preg_replace('/\s*,\s*/', ', ', $line);
        }

        AdsTxtLine::query()->delete();

        foreach ($parsed as $order => $line) {
            AdsTxtLine::query()->create([
                'line' => $line,
                'sort_order' => $order,
                'enabled' => true,
            ]);
        }

        return redirect()->route('admin.advertising.ads-txt')->with('status', 'ads.txt saved.');
    }

    private function guard(): void
    {
        abort_unless(auth()->user()->hasPermission('ads.manage'), 403);
    }
}
