<?php

return [

    /*
     * Company API key (dashboard > API keys). Its permissions decide which v2.0 routes it can call:
     * send_messages, view_messages, manage_dynamic_links, manage_integrations.
     */
    'api_key' => env('SHORTEXT_API_KEY'),

    'base_url' => env('SHORTEXT_BASE_URL', 'https://shortext.ny-corp.io/api'),

    // Seconds
    'timeout' => (float) env('SHORTEXT_TIMEOUT', 30),

    'connect_timeout' => (float) env('SHORTEXT_CONNECT_TIMEOUT', 10),

    'webhooks' => [
        // Signing secret of your webhook endpoint (or of your Shortext endpoint for its replies)
        'secret' => env('SHORTEXT_WEBHOOK_SECRET'),

        // Seconds a webhook timestamp may differ from now
        'tolerance' => (int) env('SHORTEXT_WEBHOOK_TOLERANCE', 300),
    ],
];
