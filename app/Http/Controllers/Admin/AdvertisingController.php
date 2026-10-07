<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\AdClick;
use App\Models\AdEvent;
use App\Models\AdImpression;
use App\Models\AdNetwork;
use App\Models\AdPlacement;
use App\Models\AdUnit;
use App\Models\AdsTxtEntry;
use App\Models\AdsTxtLine;
use Illuminate\Support\Facades\DB;
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
        $impressions = AdImpression::query()->where('occurred_at', '>=', $today)->count();
        $clicks = AdClick::query()->where('occurred_at', '>=', $today)->count();

        return view('admin.advertising.index', [
            'campaigns' => AdCampaign::query()->where('status', 'active')->count(),
            'networks' => AdNetwork::query()->where('enabled', true)->count(),
            'units' => AdUnit::query()->where('enabled', true)->count(),
            'placements' => AdPlacement::query()->count(),
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 1) : 0,
            'topPlacements' => $this->topColumn(AdImpression::class, 'ad_placement_id', $today),
            'topUnits' => $this->topColumn(AdImpression::class, 'ad_unit_id', $today),
            'recentCampaigns' => AdCampaign::query()->latest('id')->limit(5)->get(),
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

    public function analytics(Request $request): View
    {
        $this->guard();
        [$from, $to, $range] = $this->range($request);

        $impressions = AdImpression::query()->whereBetween('occurred_at', [$from, $to])->count();
        $clicks = AdClick::query()->whereBetween('occurred_at', [$from, $to])->count();

        return view('admin.advertising.analytics', [
            'range' => $range,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 1) : 0,
            'topPlacements' => $this->topColumn(AdImpression::class, 'ad_placement_id', $from, $to),
            'topUnits' => $this->topColumn(AdImpression::class, 'ad_unit_id', $from, $to),
            'activity' => AdImpression::query()->whereBetween('occurred_at', [$from, $to])->latest('occurred_at')->limit(20)->get(),
        ]);
    }

    public function settings(AdvertisingService $advertising): View
    {
        $this->guard();
        $network = AdNetwork::query()->where('slug', 'google-adsense')->first();

        $settings = app(Settings::class);

        return view('admin.advertising.settings', [
            'network' => $network,
            'networks' => AdNetwork::query()->orderBy('name')->get(),
            'publisherId' => $network?->publisher_id ?: '',
            'adsenseOn' => $advertising->adsenseOn(),
            'autoAds' => (bool) ($network?->configuration['auto_ads'] ?? false),
            'advertisingOn' => $advertising->advertisingEnabled(),
            'analyticsOn' => $settings->get('ads_analytics_enabled', '1') !== '0',
            'maxAds' => $settings->get('ads_max_per_page', '0'),
            'defaultNetwork' => $settings->get('ads_default_network', ''),
        ]);
    }

    public function updateSettings(Request $request, Settings $settings): RedirectResponse
    {
        $this->guard();

        $data = $request->validate([
            'publisher_id' => ['nullable', 'string', 'regex:/^ca-pub-\d{8,20}$/'],
        ]);

        $extra = $request->validate([
            'ads_max_per_page' => ['nullable', 'integer', 'min:0', 'max:20'],
            'ads_default_network' => ['nullable', 'exists:ad_networks,id'],
        ]);

        $settings->setMany([
            'advertising_enabled' => $request->boolean('advertising_enabled') ? '1' : '0',
            'adsense_enabled' => $request->boolean('adsense_enabled') ? '1' : '0',
            'adsense_auto_ads' => $request->boolean('adsense_auto_ads') ? '1' : '0',
            'ads_analytics_enabled' => $request->boolean('ads_analytics_enabled') ? '1' : '0',
            'ads_max_per_page' => (string) ($extra['ads_max_per_page'] ?? '0'),
            'ads_default_network' => (string) ($extra['ads_default_network'] ?? ''),
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
        $entries = AdsTxtEntry::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.advertising.ads-txt', [
            'lines' => $lines,
            'entries' => $entries,
            'preview' => app(\App\Http\Controllers\AdsTxtController::class)->show()->getContent(),
        ]);
    }

    public function storeAdsTxtEntry(Request $request): RedirectResponse
    {
        $this->guard();

        $data = $request->validate([
            'advertising_system' => ['required', 'string', 'max:120'],
            'publisher_account_id' => ['required', 'string', 'max:120'],
            'relationship' => ['required', 'in:DIRECT,RESELLER,direct,reseller'],
            'certification_authority_id' => ['nullable', 'string', 'max:120'],
        ]);

        AdsTxtEntry::query()->create([
            ...$data,
            'relationship' => strtoupper($data['relationship']),
            'status' => $request->boolean('status', true),
            'sort_order' => (int) AdsTxtEntry::query()->max('sort_order') + 1,
        ]);

        return redirect()->route('admin.advertising.ads-txt')->with('status', 'ads.txt entry saved.');
    }

    public function destroyAdsTxtEntry(AdsTxtEntry $entry): RedirectResponse
    {
        $this->guard();
        $entry->delete();

        return redirect()->route('admin.advertising.ads-txt')->with('status', 'ads.txt entry removed.');
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

    private function range(Request $request): array
    {
        $range = (string) $request->query('range', 'today');
        $end = now()->endOfDay();

        $start = match ($range) {
            'yesterday' => now()->subDay()->startOfDay(),
            '7' => now()->subDays(6)->startOfDay(),
            '30' => now()->subDays(29)->startOfDay(),
            'custom' => $request->date('from')?->startOfDay() ?? now()->startOfDay(),
            default => now()->startOfDay(),
        };

        if ($range === 'yesterday') {
            $end = now()->subDay()->endOfDay();
        } elseif ($range === 'custom' && $request->date('to')) {
            $end = $request->date('to')->endOfDay();
        }

        return [$start, $end, $range];
    }

    private function topColumn(string $model, string $column, $from, $to = null)
    {
        return $model::query()
            ->select($column, DB::raw('count(*) as total'))
            ->whereNotNull($column)
            ->where('occurred_at', '>=', $from)
            ->when($to, fn ($query) => $query->where('occurred_at', '<=', $to))
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }
}
