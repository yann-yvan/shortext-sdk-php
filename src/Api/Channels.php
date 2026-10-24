<?php

namespace NyCorp\Shortext\Api;

use NyCorp\Shortext\Resource;

/**
 * /v2.0/channels (permission manage_integrations).
 * $type is whatsapp, mail or shortext; $id is the channel_id of the webhooks.
 */
class Channels extends ApiResource
{
    public const WHATSAPP = 'whatsapp';

    public const MAIL = 'mail';

    public const SHORTEXT = 'shortext';

    /**
     * @param  array{type?: string, agent_id?: string}  $filters  agent_id "none" lists the detached channels
     * @return list<resource>
     */
    public function list(array $filters = []): array
    {
        return $this->items($this->request('GET', '/channels', $filters));
    }

    public function get(string $type, string $id): Resource
    {
        return $this->item($this->request('GET', self::path($type, $id)));
    }

    /** Attaches the channel to an agent, or moves it to another one */
    public function attachAgent(string $type, string $id, string $agentId): Resource
    {
        return $this->item($this->request('PUT', self::path($type, $id).'/agent', json: ['agent_id' => $agentId]));
    }

    /** Messages are still stored, nobody answers automatically */
    public function detachAgent(string $type, string $id): Resource
    {
        return $this->item($this->request('DELETE', self::path($type, $id).'/agent'));
    }

    private static function path(string $type, string $id): string
    {
        return '/channels/'.self::id($type).'/'.self::id($id);
    }
}
