<?php

$isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
$isSanitized = filter_var(env('MIDTRANS_IS_SANITIZED', true), FILTER_VALIDATE_BOOLEAN);
$is3ds = filter_var(env('MIDTRANS_IS_3DS', true), FILTER_VALIDATE_BOOLEAN);

return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    'is_production' => $isProduction,
    'is_sanitized' => $isSanitized,
    'is_3ds' => $is3ds,

    'snap_js_url' => $isProduction
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
];