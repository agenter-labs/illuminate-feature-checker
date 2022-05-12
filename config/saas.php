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
    'storage' => env('SAAS_STORAGE', 'file'),
    'token_name' => env('SAAS_TOKEN_NAME', 'sub-tkn'),
    'key' => env('SAAS_KEY', ''),
    'request_restrict' => false,
];