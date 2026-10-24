<?php

namespace NyCorp\Shortext\Api;

use NyCorp\Shortext\Page;
use NyCorp\Shortext\Resource;

/**
 * /v2.0/templates: WhatsApp templates the company can send (permission send_messages)
 */
class Templates extends ApiResource
{
    /**
     * @param  array{channel_id?: string, status?: 'APPROVED'|'PENDING'|'REJECTED', name?: string, language?: string}  $filters
     * @return Page<resource>
     */
    public function list(array $filters = [], int $page = 1): Page
    {
        return $this->page('/templates', $filters, $page);
    }

    public function get(string $id): Resource
    {
        return $this->item($this->request('GET', '/templates/'.self::id($id)));
    }
}
