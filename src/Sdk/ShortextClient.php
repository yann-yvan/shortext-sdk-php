<?php

namespace NyCorp\Shortext\Sdk;

use NyCorp\Shortext\Http\Response;
use NyCorp\Shortext\Sdk\Builder\DynamicLinkBuilder;
use NyCorp\Shortext\Sdk\Builder\MessageBuilder;

/**
 * 1.x client, kept for existing projects.
 *
 * @deprecated since 2.0, use NyCorp\Shortext\Shortext: ->v1()->sendMessage(), ->conversations()->start() / reply()
 */
class ShortextClient extends ShortextSdk
{
    public function sendMessage(MessageBuilder $builder): Response
    {
        return $this->request('POST', '/messages/send', $builder->getPayload());
    }

    public function createDynamicLink(DynamicLinkBuilder $builder): Response
    {
        return $this->request('POST', '/deep-link/add', $builder->getPayload());
    }
}
