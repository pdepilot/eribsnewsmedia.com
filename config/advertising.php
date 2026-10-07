<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Advertising switch
    |--------------------------------------------------------------------------
    |
    | The string "false" must stay off. A blank publisher ID never counts as
    | a connected AdSense account.
    |
    */

    'enabled' => filter_var(env('ADS_ENABLED', true), FILTER_VALIDATE_BOOL),

    'adsense' => [
        'enabled' => filter_var(env('ADSENSE_ENABLED', false), FILTER_VALIDATE_BOOL),
        'publisher_id' => env('ADSENSE_PUBLISHER_ID'),
        'auto_ads' => filter_var(env('ADSENSE_AUTO_ADS', false), FILTER_VALIDATE_BOOL),
    ],

];
