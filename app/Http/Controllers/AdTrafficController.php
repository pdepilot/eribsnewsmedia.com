<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Services\Advertising\AdvertisingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class AdTrafficController extends Controller
{
    public function impression(AdCampaign $campaign, AdvertisingService $advertising): Response
    {
        $advertising->recordImpression($campaign);

        return response()->noContent();
    }

    public function click(AdCampaign $campaign, AdvertisingService $advertising): RedirectResponse
    {
        return redirect()->away($advertising->recordClick($campaign));
    }
}
