<?php

use NyCorp\Shortext\Shortext;

it('authenticates with the API key as a Bearer token', function () {
    fakeShortext([envelope(['company' => ['id' => 'CMP-1']])])->me();

    $request = lastRequest();
    expect($request->getHeaderLine('Authorization'))->toBe('Bearer test-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/json')
        ->and($request->getHeaderLine('User-Agent'))->toStartWith('shortext-php/'.Shortext::VERSION);
});

it('calls the v2.0 routes under the default base URL', function () {
    fakeShortext([envelope([])])->me();

    expect((string) lastRequest()->getUri())->toBe('https://shortext.ny-corp.io/api/v2.0/me')
        ->and(lastRequest()->getMethod())->toBe('GET');
});

it('accepts a custom base URL with or without trailing slash', function (string $baseUrl) {
    fakeShortext([envelope([])], $baseUrl)->me();

    expect((string) lastRequest()->getUri())->toBe('https://staging.example.com/api/v2.0/me');
})->with(['https://staging.example.com/api', 'https://staging.example.com/api/']);

it('leaves null query values out and sends booleans as 1/0', function () {
    fakeShortext([envelope(paginated([]))])->agents()->list(['name' => null, 'active' => true, 'use_ai' => false]);

    expect(lastRequest()->getUri()->getQuery())->toBe('active=1&use_ai=0');
});

it('sends JSON bodies', function () {
    fakeShortext([envelope(['id' => 'AGI-1'])])->agents()->create(['name' => 'Support']);

    expect(lastRequest()->getHeaderLine('Content-Type'))->toBe('application/json')
        ->and(requestJson())->toBe(['name' => 'Support']);
});

it('sends an empty JSON object for actions without input', function () {
    fakeShortext([envelope(['secret' => 's'])])->webhooks()->rotateSecret('wh-1');

    expect((string) lastRequest()->getBody())->toBe('{}');
});

it('sends no body on bodiless actions', function () {
    fakeShortext([envelope(['id' => 'c1'])])->conversations()->handover('c1');

    expect((string) lastRequest()->getBody())->toBe('')
        ->and(lastRequest()->hasHeader('Content-Type'))->toBeFalse();
});

it('encodes the path parameters', function () {
    fakeShortext([envelope([])])->channels()->get('mail', 'support@acme.com/x');

    expect(lastRequest()->getUri()->getPath())->toBe('/api/v2.0/channels/mail/support%40acme.com%2Fx');
});

it('gives raw access to routes the SDK does not cover', function () {
    $response = fakeShortext([envelope(['balance' => 10])])->http()->request('GET', '/business-manager/balance');

    expect($response->get('balance'))->toBe(10)
        ->and((string) lastRequest()->getUri())->toBe('https://shortext.ny-corp.io/api/business-manager/balance');
});
