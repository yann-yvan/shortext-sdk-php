<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as Psr7Response;
use NyCorp\Shortext\Shortext;
use NyCorp\Shortext\Tests\TestCase;
use Psr\Http\Message\RequestInterface;

// Only the Laravel integration boots an application
uses(TestCase::class)->in('Laravel');

/**
 * A Shortext envelope as the API answers it (HTTP 200 whatever the outcome)
 */
function envelope(mixed $body = null, bool $success = true, int $code = 1000, string $message = 'Request success.', array $headers = [], int $status = 200): Psr7Response
{
    $payload = ['success' => $success, 'code' => $code, 'message' => $message, 'body' => $body];
    if (! $success) {
        $payload['trace_id'] = 'trace-123';
    }

    return new Psr7Response($status, ['Content-Type' => 'application/json'] + $headers, json_encode($payload));
}

function paginated(array $items, int $page = 1, int $lastPage = 1): array
{
    return [
        'current_page' => $page,
        'data' => $items,
        'last_page' => $lastPage,
        'per_page' => 50,
        'total' => count($items) * $lastPage,
    ];
}

/**
 * A client answering $responses in order; the sent requests are pushed to $GLOBALS['shortext_requests']
 */
function fakeShortext(array $responses, ?string $baseUrl = null): Shortext
{
    $GLOBALS['shortext_requests'] = [];
    $stack = HandlerStack::create(new MockHandler($responses));
    $stack->push(Middleware::history($GLOBALS['shortext_requests']));

    return new Shortext('test-key', $baseUrl, ['handler' => $stack]);
}

function lastRequest(): RequestInterface
{
    $history = $GLOBALS['shortext_requests'] ?? [];

    return end($history)['request'];
}

/** @return list<RequestInterface> */
function sentRequests(): array
{
    return array_map(static fn (array $entry) => $entry['request'], $GLOBALS['shortext_requests'] ?? []);
}

function requestJson(?RequestInterface $request = null): mixed
{
    return json_decode((string) ($request ?? lastRequest())->getBody(), true);
}
