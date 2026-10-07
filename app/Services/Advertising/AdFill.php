<?php

namespace App\Services\Advertising;

use App\Models\AdCampaign;
use App\Models\AdUnit;
use App\Models\Advertisement;

class AdFill
{
    public function __construct(
        public readonly string $source,
        public readonly string $label,
        public readonly string $device,
        public readonly ?AdCampaign $campaign = null,
        public readonly ?Advertisement $legacy = null,
        public readonly ?AdUnit $unit = null,
    ) {}
}
