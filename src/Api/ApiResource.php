<?php

namespace NyCorp\Shortext\Api;

use NyCorp\Shortext\Exceptions\ApiException;
use NyCorp\Shortext\Exceptions\TransportException;
use NyCorp\Shortext\Http\HttpClient;
use NyCorp\Shortext\Http\Response;
use NyCorp\Shortext\Page;
use NyCorp\Shortext\Resource;

/**
 * Base of the API groups: the requests throw on failure.
 */
abstract class ApiResource
{
    public const V2 = '/v2.0';

    public function __construct(protected HttpClient $http) {}

    /**
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>|null  $json
     *
     * @throws ApiException|TransportException
     */
    protected function request(string $method, string $path, array $query = [], ?array $json = null): Response
    {
        return $this->http->request($method, static::V2.$path, $query, $json)->throw();
    }

    protected function item(Response $response): Resource
    {
        $body = $response->body();

        return Resource::from(is_array($body) ? $body : []);
    }

    /** @return list<resource> */
    protected function items(Response $response): array
    {
        return Page::fromBody($response->body())->items();
    }

    /**
     * @param  array<string, mixed>  $query
     * @return Page<resource>
     */
    protected function page(string $path, array $query, int $page): Page
    {
        $response = $this->request('GET', $path, ['page' => $page > 1 ? $page : null] + $query);

        return Page::fromBody($response->body(), Resource::class, fn (int $next) => $this->page($path, $query, $next));
    }

    protected static function id(string|int $id): string
    {
        return rawurlencode((string) $id);
    }
}
