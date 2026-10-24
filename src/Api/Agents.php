<?php

namespace NyCorp\Shortext\Api;

use NyCorp\Shortext\Page;
use NyCorp\Shortext\Resource;

/**
 * /v2.0/agents (permission manage_integrations)
 */
class Agents extends ApiResource
{
    /**
     * @param  array{name?: string, active?: bool, use_ai?: bool}  $filters
     * @return Page<resource>
     */
    public function list(array $filters = [], int $page = 1): Page
    {
        return $this->page('/agents', $filters, $page);
    }

    public function get(string $id): Resource
    {
        return $this->item($this->request('GET', '/agents/'.self::id($id)));
    }

    /**
     * @param  array{name: string, active?: bool, use_ai?: bool, inactive_message?: string|null, instructions?: string|null}  $attributes
     */
    public function create(array $attributes): Resource
    {
        return $this->item($this->request('POST', '/agents', json: $attributes));
    }

    /**
     * @param  array{name?: string, active?: bool, use_ai?: bool, inactive_message?: string|null, instructions?: string|null}  $attributes
     */
    public function update(string $id, array $attributes): Resource
    {
        return $this->item($this->request('PATCH', '/agents/'.self::id($id), json: $attributes));
    }

    /** Soft delete: restore() brings it back. Returns the deleted agent. */
    public function delete(string $id): Resource
    {
        return $this->item($this->request('DELETE', '/agents/'.self::id($id)));
    }

    public function restore(string $id): Resource
    {
        return $this->item($this->request('PATCH', '/agents/'.self::id($id).'/restore'));
    }
}
