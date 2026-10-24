<?php

use Illuminate\Http\Request;
use NyCorp\Shortext\Facades\Shortext as ShortextFacade;
use NyCorp\Shortext\Shortext;
use NyCorp\Shortext\Webhooks\Events\MessageReceived;
use NyCorp\Shortext\Webhooks\WebhookVerifier;

it('binds a configured client', function () {
    config()->set('shortext.api_key', 'from-config');
    config()->set('shortext.base_url', 'https://staging.example.com/api');

    $client = app(Shortext::class);

    expect($client)->toBe(app('shortext'))
        ->and($client->http()->baseUrl())->toBe('https://staging.example.com/api')
        ->and(ShortextFacade::getFacadeRoot())->toBe($client);
});

it('merges the default config', function () {
    expect(config('shortext.base_url'))->toBe('https://shortext.ny-corp.io/api')
        ->and(config('shortext.webhooks.tolerance'))->toBe(300);
});

it('verifies a webhook request with the configured secret', function () {
    config()->set('shortext.webhooks.secret', 'whsec_laravel');

    $body = json_encode(['id' => 'evt_1', 'type' => 'message.received', 'data' => ['conversation_id' => 'cnv1']]);
    $timestamp = (string) time();
    $request = Request::create('/webhooks/shortext', 'POST', server: [
        'HTTP_X_SHORTEXT_SIGNATURE' => WebhookVerifier::sign('whsec_laravel', $timestamp, $body),
        'HTTP_X_SHORTEXT_TIMESTAMP' => $timestamp,
        'CONTENT_TYPE' => 'application/json',
    ], content: $body);

    $event = ShortextFacade::webhookEvent($request);

    expect($event)->toBeInstanceOf(MessageReceived::class)
        ->and($event->conversationId())->toBe('cnv1');
});
