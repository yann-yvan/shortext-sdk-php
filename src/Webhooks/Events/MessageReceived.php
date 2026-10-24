<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * A contact sent a message to one of your agents
 */
class MessageReceived extends MessageEvent
{
    public const TYPE = 'message.received';
}
