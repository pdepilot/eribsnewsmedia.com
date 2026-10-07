<?php

namespace App\Services\Advertising;

use App\Models\AdCampaign;
use App\Models\AdPlacement;
use App\Models\AdUnit;
use Illuminate\Support\Collection;

class AdManagerService
{
    public const PAGES = ['all', 'homepage', 'article', 'category', 'search', 'tag'];

    public const DEVICES = ['all', 'desktop', 'tablet', 'mobile'];

    public function currentPage(): string
    {
        $forced = request()->attributes->get('ad-page');

        if (is_string($forced) && in_array($forced, self::PAGES, true)) {
            return $forced;
        }

        return match (true) {
            request()->routeIs('home') => 'homepage',
            request()->routeIs('articles.show') => 'article',
            request()->routeIs('categories.show') => 'category',
            request()->routeIs('search') => 'search',
            default => 'all',
        };
    }

    public function currentDevice(): string
    {
        $forced = request()->attributes->get('ad-device');

        if (is_string($forced) && in_array($forced, self::DEVICES, true) && $forced !== 'all') {
            return $forced;
        }

        $hint = (string) request()->header('Sec-CH-UA-Mobile');

        if ($hint === '?1') {
            return 'mobile';
        }

        return 'desktop';
    }

    public function placementMatchesDevice(AdPlacement $placement): bool
    {
        $device = $placement->device ?: 'all';

        return $device === 'all' || $device === $this->currentDevice();
    }

    public function selectCampaign(AdPlacement $placement): ?AdCampaign
    {
        $campaigns = AdCampaign::query()
            ->with('creative')
            ->where('ad_placement_id', $placement->id)
            ->where('status', 'active')
            ->whereHas('advertiser', fn ($query) => $query->where('status', 'active'))
            ->where(function ($query) {
                $query->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')->orWhere('end_at', '>=', now());
            })
            ->get()
            ->filter(fn (AdCampaign $campaign) => $campaign->creative !== null);

        return $this->pick($campaigns);
    }

    public function selectUnit(AdPlacement $placement, array $networkTypes, bool $requireNetwork = true): ?AdUnit
    {
        $page = $this->currentPage();
        $device = $this->currentDevice();

        $units = AdUnit::query()
            ->with('network')
            ->select('ad_units.*')
            ->join('ad_networks', 'ad_networks.id', '=', 'ad_units.ad_network_id')
            ->where('ad_units.ad_placement_id', $placement->id)
            ->where('ad_units.enabled', true)
            ->when($requireNetwork, fn ($query) => $query->where('ad_networks.enabled', true))
            ->whereIn('ad_networks.type', $networkTypes)
            ->where(function ($query) use ($page) {
                $query->where('ad_units.page_target', 'all')->orWhere('ad_units.page_target', $page);
            })
            ->where(function ($query) use ($device) {
                $query->where('ad_units.device', 'all')->orWhere('ad_units.device', $device);
            })
            ->where(function ($query) {
                $query->whereNull('ad_units.starts_at')->orWhere('ad_units.starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ad_units.ends_at')->orWhere('ad_units.ends_at', '>=', now());
            })
            ->get();

        return $this->pick($units);
    }

    public function selectFallback(AdPlacement $placement): ?AdUnit
    {
        $page = $this->currentPage();
        $device = $this->currentDevice();

        return AdUnit::query()
            ->where('ad_placement_id', $placement->id)
            ->where('enabled', true)
            ->whereNotNull('fallback_code')
            ->where('fallback_code', '!=', '')
            ->where(function ($query) use ($page) {
                $query->where('page_target', 'all')->orWhere('page_target', $page);
            })
            ->where(function ($query) use ($device) {
                $query->where('device', 'all')->orWhere('device', $device);
            })
            ->orderByDesc('priority')
            ->orderBy('id')
            ->first();
    }

    public function pick(Collection $items): mixed
    {
        if ($items->isEmpty()) {
            return null;
        }

        $priority = (int) $items->max('priority');
        $group = $items
            ->filter(fn ($item) => (int) $item->priority === $priority)
            ->sortBy('id')
            ->values();

        $total = (int) $group->sum(fn ($item) => max(1, (int) ($item->weight ?? 100)));
        $roll = request()->attributes->get('ad-roll');

        if (! is_numeric($roll) && app()->runningUnitTests() && is_numeric(request()->header('X-Ad-Roll'))) {
            $roll = request()->header('X-Ad-Roll');
        }

        $cursor = is_numeric($roll) ? ((int) $roll % max(1, $total)) : 0;
        $running = 0;

        foreach ($group as $item) {
            $running += max(1, (int) ($item->weight ?? 100));

            if ($cursor < $running) {
                return $item;
            }
        }

        return $group->first();
    }

    public function visitorHashes(): array
    {
        return [
            'session_hash' => hash('sha256', (string) session()->getId()),
            'ip_hash' => hash('sha256', (string) request()->ip().'|'.(string) config('app.key')),
        ];
    }
}
