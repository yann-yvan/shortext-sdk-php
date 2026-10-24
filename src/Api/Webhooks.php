<?php

namespace NyCorp\Shortext\Api;

use NyCorp\Shortext\Page;
use NyCorp\Shortext\Resource;

/**
 * /v2.0/webhooks: your webhook endpoints (permission manage_integrations).
 * Payloads follow the API version used to create the endpoint.
 */
class Webhooks extends ApiResource
{
    /**
     * Event types an endpoint can subscribe to: [{type, description}]
     *
     * @return list<resource>
     */
    public function events(): array
    {
        return $this->items($this->request('GET', '/webhooks/events'));
    }

    /** @return list<resource> */
    public function list(): array
    {
        return $this->items($this->request('GET', '/webhooks'));
    }

    public function get(string $id): Resource
    {
        return $this->item($this->request('GET', '/webhooks/'.self::id($id)));
    }

    /**
     * The signing `secret` is returned only once, in this response: keep it.
     *
     * @param  list<string>  $events  exact types, prefix wildcards ("message.*") or "*"
     * @param  array<string, string>|null  $filters  e.g. ['app' => 'AGI-XXXXX'], ['channel' => 'whatsapp'], ['channel_id' => '...']
     */
    public function create(string $url, array $events, ?array $filters = null, ?string $description = null): Resource
    {
        return $this->item($this->request('POST', '/webhooks', json: array_filter([
            'url' => $url,
            'events' => $events,
            'filters' => $filters,
            'description' => $description,
        ], static fn ($value) => $value !== null)));
    }

    /**
     * is_enabled = true re-enables an endpoint disabled after repeated failures
     *
     * @param  array{url?: string, events?: list<string>, filters?: array<string, string>|null, description?: string|null, is_enabled?: bool}  $attributes
     */
    public function update(string $id, array $attributes): Resource
    {
        return $this->item($this->request('PUT', '/webhooks/'.self::id($id), json: $attributes));
    }

    public function delete(string $id): void
    {
        $this->request('DELETE', '/webhooks/'.self::id($id));
    }

    /** New signing secret; the previous one stops working immediately */
    public function rotateSecret(string $id): string
    {
        return (string) $this->request('POST', '/webhooks/'.self::id($id).'/rotate-secret', json: [])->get('secret');
    }

    /** Sends a webhook.test event right away and returns the delivery with your endpoint's response */
    public function test(string $id): Resource
    {
        return $this->item($this->request('POST', '/webhooks/'.self::id($id).'/test', json: []));
    }

    /**
     * Delivery history (kept 30 days), most recent first
     *
     * @param  'pending'|'succeeded'|'failed'|null  $status
     * @return Page<resource>
     */
    public function deliveries(string $id, ?string $status = null, int $page = 1): Page
    {
        return $this->page('/webhooks/'.self::id($id).'/deliveries', ['status' => $status], $page);
    }

    /** Redelivers an event (same event id) */
    public function retry(string $id, string $deliveryId): Resource
    {
        return $this->item($this->request('POST', '/webhooks/'.self::id($id).'/deliveries/'.self::id($deliveryId).'/retry', json: []));
    }
}
