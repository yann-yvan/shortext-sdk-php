<?php

namespace NyCorp\Shortext\Api;

use NyCorp\Shortext\Resource;

/**
 * /v2.0/channels/shortext: the Shortext endpoints of your own apps (permission manage_integrations).
 * Read them with channels()->list(['type' => 'shortext']) or get().
 */
class ShortextEndpoints extends ApiResource
{
    public function get(string $id): Resource
    {
        return $this->item($this->request('GET', '/channels/shortext/'.self::id($id)));
    }

    /**
     * @param  array{name: string, agent_id?: string|null, enabled?: bool, reply_mode?: 'async'|'sync', sync_timeout?: int, webhook_url?: string|null}  $attributes
     */
    public function create(array $attributes): Resource
    {
        return $this->item($this->request('POST', '/channels/shortext', json: $attributes));
    }

    /**
     * @param  array{name?: string, agent_id?: string|null, enabled?: bool, reply_mode?: 'async'|'sync', sync_timeout?: int, webhook_url?: string|null}  $attributes
     */
    public function update(string $id, array $attributes): Resource
    {
        return $this->item($this->request('PATCH', '/channels/shortext/'.self::id($id), json: $attributes));
    }

    public function delete(string $id): Resource
    {
        return $this->item($this->request('DELETE', '/channels/shortext/'.self::id($id)));
    }

    /** New secret signing the replies pushed to webhook_url (the previous one stops working): the endpoint with its webhook_secret */
    public function rotateSecret(string $id): Resource
    {
        return $this->item($this->request('POST', '/channels/shortext/'.self::id($id).'/rotate-secret', json: []));
    }
}
