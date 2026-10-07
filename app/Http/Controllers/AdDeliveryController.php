<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Services\Advertising\AdvertisingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdDeliveryController extends Controller
{
    public function impression(Request $request, AdCampaign $campaign, AdvertisingService $advertising): Response
    {
        if ($this->isPrefetch($request)) {
            return response('', 204);
        }

        $advertising->recordImpression($campaign);

        return response('', 204);
    }

    public function click(Request $request, AdCampaign $campaign, AdvertisingService $advertising): RedirectResponse|Response
    {
        if ($this->isPrefetch($request)) {
            return response('', 204);
        }

        return redirect()->away($advertising->recordClick($campaign));
    }

    private function isPrefetch(Request $request): bool
    {
        $purpose = strtolower((string) $request->header('Sec-Purpose', $request->header('Purpose', '')));

        return str_contains($purpose, 'prefetch') || str_contains($purpose, 'preview');
    }
}
