<?php

use NyCorp\Shortext\Http\Response;

it('maps the envelope', function () {
    $response = fakeShortext([envelope(['a' => ['b' => 'c']], message: 'Done')])->http()->request('GET', '/v2.0/me');

    expect($response->successful())->toBeTrue()
        ->and($response->failed())->toBeFalse()
        ->and($response->code())->toBe(1000)
        ->and($response->message())->toBe('Done')
        ->and($response->body())->toBe(['a' => ['b' => 'c']])
        ->and($response->get('a.b'))->toBe('c')
        ->and($response->get('a.missing', 'default'))->toBe('default')
        ->and($response->traceId())->toBeNull()
        ->and($response->status())->toBe(200);
});

it('uses the absolute value of negative codes', function () {
    $response = new Response(200, [], ['success' => false, 'code' => -1002, 'message' => 'Invalid', 'body' => null]);

    expect($response->code())->toBe(1002);
});

it('surfaces the version, Deprecation, Sunset and Link headers', function () {
    $response = fakeShortext([envelope([], headers: [
        'X-Shortext-Api-Version' => 'v2.0',
        'Deprecation' => '@1798761600',
        'Sunset' => 'Thu, 01 Jul 2027 00:00:00 GMT',
        'Link' => '<https://docs.example.com/changelog#v2.0>; rel="deprecation"',
    ])])->http()->request('GET', '/v2.0/me');

    expect($response->apiVersion())->toBe('v2.0')
        ->and($response->isDeprecated())->toBeTrue()
        ->and($response->deprecatedAt()->getTimestamp())->toBe(1798761600)
        ->and($response->sunsetAt()->format('Y-m-d'))->toBe('2027-07-01')
        ->and($response->deprecationLink())->toBe('https://docs.example.com/changelog#v2.0');
});

it('is not deprecated without the headers', function () {
    $response = new Response(200, [], ['success' => true, 'code' => 1000, 'body' => null]);

    expect($response->isDeprecated())->toBeFalse()
        ->and($response->deprecatedAt())->toBeNull()
        ->and($response->sunsetAt())->toBeNull()
        ->and($response->deprecationLink())->toBeNull();
});

it('keeps the 1.x DefResponse getters', function () {
    $response = new Response(200, [], ['success' => false, 'code' => 1007, 'message' => 'Not found', 'body' => ['x' => 1]]);

    expect($response->isSuccess())->toBeFalse()
        ->and($response->getCode())->toBe(1007)
        ->and($response->getMessage())->toBe('Not found')
        ->and($response->getBody())->toBe(['x' => 1])
        ->and($response->getData())->toBe(['x' => 1])
        ->and($response->isCodeSame(-1007))->toBeTrue();
});
