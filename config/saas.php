<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Manage IAM's cache configurations. It uses the driver defined in the
    | config/cache.php file.
    |
    */
    'storage' => [
        'cache' => env('SAAS_STORAGE_CACHE', 'file'),
        'subscription' => env('SAAS_MODEL_SUBSCRIPTION', ''),
        'feature' => env('SAAS_MODEL_FEATURE', '')
    ],
    'token_name' => env('SAAS_TOKEN_NAME', 'sub-tkn'),
    'key' => env('SAAS_KEY', ''),
];