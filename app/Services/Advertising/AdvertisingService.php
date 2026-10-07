<?php

namespace App\Services\Advertising;

use App\Models\AdCampaign;
use App\Models\AdClick;
use App\Models\AdEvent;
use App\Models\AdImpression;
use App\Models\AdNetwork;
use App\Models\AdPlacement;
use App\Models\AdUnit;
use App\Models\Advertisement;
use App\Services\Settings;

class AdvertisingService
{
    public function __construct(private Settings $settings) {}

    public function advertisingEnabled(): bool
    {
        if (! filter_var(config('advertising.enabled'), FILTER_VALIDATE_BOOL)) {
            return false;
        }

        return $this->settings->get('advertising_enabled', '1') === '1';
    }

    public function resolve(string $name): ?AdFill
    {
        $slug = $this->normalize($name);
        $key = 'ad-fill.'.$slug;

        if (request()->attributes->has($key)) {
            return request()->attributes->get($key);
        }

        $this->syncSchedules();
        $fill = $this->find($slug);
        request()->attributes->set($key, $fill);

        if ($fill?->source === 'direct_campaign') {
            request()->attributes->set('ad-beacon', true);
        }

        return $fill;
    }

    public function wantsImpressionBeacon(): bool
    {
        return (bool) request()->attributes->get('ad-beacon', false);
    }

    public function syncSchedules(): void
    {
        if (request()->attributes->get('ad-synced')) {
            return;
        }

        $now = now();

        AdCampaign::query()
            ->whereIn('status', ['active', 'scheduled'])
            ->whereNotNull('end_at')
            ->where('end_at', '<', $now)
            ->update(['status' => 'ended']);

        AdCampaign::query()
            ->where('status', 'active')
            ->whereNotNull('start_at')
            ->where('start_at', '>', $now)
            ->update(['status' => 'scheduled']);

        AdCampaign::query()
            ->where('status', 'scheduled')
            ->where(function ($query) use ($now) {
                $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->update(['status' => 'active']);

        request()->attributes->set('ad-synced', true);
    }

    public function recordImpression(AdCampaign $campaign): void
    {
        $this->syncSchedules();
        $campaign = $campaign->fresh() ?? $campaign;

        if (! $this->campaignIsServable($campaign)) {
            abort(404);
        }

        $campaign->increment('impressions');
        AdEvent::query()->create([
            'ad_campaign_id' => $campaign->id,
            'ad_placement_id' => $campaign->ad_placement_id,
            'type' => 'impression',
        ]);
        $this->recordTraffic(AdImpression::class, $campaign->ad_placement_id, $campaign->id, null);
    }

    public function recordClick(AdCampaign $campaign): string
    {
        $this->syncSchedules();
        $campaign = $campaign->fresh() ?? $campaign;

        if (! $this->campaignIsServable($campaign) || ! $this->safeHttpUrl($campaign->click_url)) {
            abort(404);
        }

        $campaign->increment('clicks');
        AdEvent::query()->create([
            'ad_campaign_id' => $campaign->id,
            'ad_placement_id' => $campaign->ad_placement_id,
            'type' => 'click',
        ]);
        $this->recordTraffic(AdClick::class, $campaign->ad_placement_id, $campaign->id, null);

        return $campaign->click_url;
    }

    public function publisherId(): ?string
    {
        $network = $this->adsenseNetwork();

        foreach ([$network?->publisher_id, config('advertising.adsense.publisher_id')] as $candidate) {
            $id = is_string($candidate) ? trim($candidate) : '';

            if (preg_match('/^ca-pub-\d{8,20}$/', $id) === 1) {
                return $id;
            }
        }

        return null;
    }

    public function adsenseOn(): bool
    {
        if (! $this->advertisingEnabled()) {
            return false;
        }

        $explicit = $this->settings->get('adsense_enabled');

        if ($explicit === '0') {
            return false;
        }

        if ($explicit === '1') {
            return true;
        }

        $network = $this->adsenseNetwork();

        if ($network?->enabled) {
            return true;
        }

        return filter_var(config('advertising.adsense.enabled'), FILTER_VALIDATE_BOOL);
    }

    public function autoAds(): bool
    {
        if (! $this->adsenseOn() || $this->publisherId() === null) {
            return false;
        }

        $configuration = $this->adsenseNetwork()?->configuration ?? [];

        if (($configuration['auto_ads'] ?? false) === true || $this->settings->get('adsense_auto_ads') === '1') {
            return true;
        }

        return filter_var(config('advertising.adsense.auto_ads'), FILTER_VALIDATE_BOOL);
    }

    public function adsenseLoader(): ?string
    {
        $id = $this->publisherId();

        if ($id === null || ! $this->adsenseOn()) {
            return null;
        }

        if (! $this->autoAds() && ! $this->hasManualAdsenseUnit()) {
            return null;
        }

        $src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client='.rawurlencode($id);

        return '<script async src="'.e($src).'" crossorigin="anonymous"></script>';
    }

    public function adsenseMarkup(AdUnit $unit): ?string
    {
        $client = $this->validPublisher($unit->ad_client) ?? $this->publisherId();
        $slot = is_string($unit->ad_slot) ? trim($unit->ad_slot) : '';

        if ($client === null || preg_match('/^\d{6,20}$/', $slot) !== 1) {
            return null;
        }

        $format = in_array($unit->format, AdUnit::FORMATS, true) ? $unit->format : null;
        $html = '<ins class="adsbygoogle" style="display:block" data-ad-client="'.e($client).'" data-ad-slot="'.e($slot).'"';

        if ($format !== null) {
            $html .= ' data-ad-format="'.e($format).'"';
        }

        if ($unit->responsive) {
            $html .= ' data-full-width-responsive="true"';
        }

        $html .= '></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';

        return $html;
    }

    public function safeHttpUrl(?string $url): bool
    {
        if (blank($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return in_array($scheme, ['http', 'https'], true);
    }

    private function find(string $slug): ?AdFill
    {
        if (! $this->advertisingEnabled()) {
            return null;
        }

        $placement = AdPlacement::query()->where('slug', $slug)->first();

        if ($placement && ! $placement->enabled) {
            return null;
        }

        $manager = app(AdManagerService::class);
        $device = $placement->device ?? 'all';

        if ($placement && ! $manager->placementMatchesDevice($placement)) {
            return null;
        }

        $campaign = $placement ? $manager->selectCampaign($placement) : null;

        if ($campaign?->creative && ($campaign->creative->status ?? 'active') === 'active') {
            return $this->accept(new AdFill('direct_campaign', 'Sponsored', $device, campaign: $campaign));
        }

        $legacy = Advertisement::query()->active()->where('placement', $slug)->orderByDesc('id')->first();

        if ($legacy && ($legacy->type === 'image' ? filled($legacy->imageUrl()) : filled($legacy->code))) {
            return $this->accept(new AdFill('legacy', 'Advertisement', $device, legacy: $legacy));
        }

        $thirdParty = $placement
            ? $manager->selectUnit($placement, ['third_party', 'monetag', 'adsterra', 'medianet', 'custom', 'direct'])
            : null;

        if ($thirdParty && filled($thirdParty->markup)) {
            $unitDevice = $this->combinedDevice($device, $thirdParty->device);

            if ($unitDevice !== null) {
                return $this->accept(new AdFill('third_party', 'Advertisement', $unitDevice, unit: $thirdParty));
            }
        }

        $adsense = ($placement && $this->adsenseOn())
            ? $manager->selectUnit($placement, ['adsense'], false)
            : null;

        if ($adsense && $this->adsenseMarkup($adsense) !== null) {
            $unitDevice = $this->combinedDevice($device, $adsense->device);

            if ($unitDevice !== null) {
                return $this->accept(new AdFill('adsense', 'Advertisement', $unitDevice, unit: $adsense));
            }
        }

        $fallback = $placement ? $manager->selectFallback($placement) : null;

        if ($fallback && filled($fallback->fallback_code)) {
            return $this->accept(new AdFill('fallback', 'Advertisement', $device, unit: $fallback));
        }

        return null;
    }

    private function accept(AdFill $fill): ?AdFill
    {
        $max = (int) $this->settings->get('ads_max_per_page', '0');
        $shown = (int) request()->attributes->get('ad-shown', 0);

        if ($max > 0 && $shown >= $max) {
            return null;
        }

        request()->attributes->set('ad-shown', $shown + 1);

        return $fill;
    }

    private function recordTraffic(string $model, ?int $placementId, ?int $campaignId, ?int $unitId): void
    {
        if ($this->settings->get('ads_analytics_enabled', '1') === '0') {
            return;
        }

        $manager = app(AdManagerService::class);

        $model::query()->create([
            'ad_unit_id' => $unitId,
            'ad_placement_id' => $placementId,
            'ad_campaign_id' => $campaignId,
            'page_type' => $manager->currentPage(),
            'device' => $manager->currentDevice(),
            'occurred_at' => now(),
            ...$manager->visitorHashes(),
        ]);
    }

    private function combinedDevice(string $placementDevice, ?string $unitDevice): ?string
    {
        $unitDevice = $unitDevice ?: 'all';

        if ($placementDevice !== 'all' && $unitDevice !== 'all' && $placementDevice !== $unitDevice) {
            return null;
        }

        return $placementDevice !== 'all' ? $placementDevice : $unitDevice;
    }

    private function campaignIsServable(AdCampaign $campaign): bool
    {
        if (! $this->advertisingEnabled() || $campaign->status !== 'active') {
            return false;
        }

        if ($campaign->start_at && $campaign->start_at->isFuture()) {
            return false;
        }

        if ($campaign->end_at && $campaign->end_at->isPast()) {
            return false;
        }

        return true;
    }

    private function hasManualAdsenseUnit(): bool
    {
        if (! $this->adsenseOn()) {
            return false;
        }

        return AdUnit::query()
            ->where('enabled', true)
            ->whereHas('network', fn ($query) => $query->where('type', 'adsense'))
            ->exists();
    }

    private function adsenseNetwork(): ?AdNetwork
    {
        return AdNetwork::query()->where('slug', 'google-adsense')->first()
            ?? AdNetwork::query()->where('type', 'adsense')->orderByDesc('priority')->first();
    }

    private function validPublisher(?string $value): ?string
    {
        $id = is_string($value) ? trim($value) : '';

        return preg_match('/^ca-pub-\d{8,20}$/', $id) === 1 ? $id : null;
    }

    private function normalize(string $name): string
    {
        return str_replace('-', '_', trim($name));
    }
}
