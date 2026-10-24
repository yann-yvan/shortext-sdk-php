<?php

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as Psr7Response;
use NyCorp\Shortext\Exceptions\ApiException;
use NyCorp\Shortext\Exceptions\ApiVersionSunsetException;
use NyCorp\Shortext\Exceptions\AuthenticationException;
use NyCorp\Shortext\Exceptions\ConflictException;
use NyCorp\Shortext\Exceptions\NotAuthorizedException;
use NyCorp\Shortext\Exceptions\NotFoundException;
use NyCorp\Shortext\Exceptions\ShortextException;
use NyCorp\Shortext\Exceptions\TransportException;
use NyCorp\Shortext\Exceptions\ValidationException;

it('throws a typed exception per envelope code', function (int $code, string $class) {
    $shortext = fakeShortext([envelope(null, false, $code, 'Failure')]);

    expect(fn () => $shortext->agents()->get('AGI-1'))->toThrow($class, 'Failure');
})->with([
    'validation' => [1002, ValidationException::class],
    'not found' => [1007, NotFoundException::class],
    'duplicate' => [1004, ConflictException::class],
    'permission' => [1005, NotAuthorizedException::class],
    'unknown key' => [4, AuthenticationException::class],
    'no company key' => [5, AuthenticationException::class],
    'other' => [1001, ApiException::class],
]);

it('exposes the validation errors per field', function () {
    $shortext = fakeShortext([envelope(['name' => ['The name field is required.']], false, 1002, 'The name field is required.')]);

    try {
        $shortext->agents()->create([]);
        $this->fail('No exception');
    } catch (ValidationException $e) {
        expect($e->errors())->toBe(['name' => ['The name field is required.']])
            ->and($e->getCode())->toBe(1002)
            ->and($e->traceId())->toBe('trace-123')
            ->and($e->response()->failed())->toBeTrue();
    }
});

it('treats an authentication failure as not authorized too', function () {
    $shortext = fakeShortext([envelope(null, false, 4, 'Unauthenticated.')]);

    expect(fn () => $shortext->me())->toThrow(NotAuthorizedException::class);
});

it('throws ApiVersionSunsetException on HTTP 410', function () {
    $shortext = fakeShortext([envelope(['sunset_at' => '2027-07-01', 'latest' => 'v3.0'], false, 1200, 'API version v2.0 is no longer available', status: 410)]);

    try {
        $shortext->me();
        $this->fail('No exception');
    } catch (ApiVersionSunsetException $e) {
        expect($e->latestVersion())->toBe('v3.0')
            ->and($e->sunsetAt()->format('Y-m-d'))->toBe('2027-07-01')
            ->and($e->response()->status())->toBe(410);
    }
});

it('throws TransportException when the API is unreachable', function () {
    $shortext = fakeShortext([new ConnectException('Connection refused', new Request('GET', 'x'))]);

    expect(fn () => $shortext->me())->toThrow(TransportException::class, 'Connection refused');
});

it('throws TransportException without a Shortext envelope', function (Psr7Response $response) {
    $shortext = fakeShortext([$response]);

    try {
        $shortext->me();
        $this->fail('No exception');
    } catch (TransportException $e) {
        expect($e->status())->toBe($response->getStatusCode());
    }
})->with([
    'html error page' => fn () => new Psr7Response(502, ['Content-Type' => 'text/html'], '<html>Bad gateway</html>'),
    'other json' => fn () => new Psr7Response(500, [], '{"error":"boom"}'),
]);

it('marks every exception as a ShortextException', function () {
    $shortext = fakeShortext([envelope(null, false, 1007)]);

    try {
        $shortext->me();
        $this->fail('No exception');
    } catch (Throwable $e) {
        expect($e)->toBeInstanceOf(ShortextException::class);
    }
});
