<?php

namespace Nycorp\Shortext\Sdk;

use Nycorp\LiteApi\Response\DefResponse;
use Nycorp\Shortext\Sdk\Builder\DynamicLinkBuilder;
use Nycorp\Shortext\Sdk\Builder\MessageBuilder;

class ShortextClient extends ShortextSdk
{
    public function sendMessage(MessageBuilder $builder): DefResponse
    {
        return $this->request('POST', '/messages/send', $builder->getPayload());
    }

    public function createDynamicLink(DynamicLinkBuilder $builder): DefResponse
    {
        return $this->request('POST', '/deep-link/add', $builder->getPayload());
    }
}
