<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * A message was sent to a contact (AI agent, human agent or API). Also the replies pushed to a Shortext endpoint webhook_url.
 */
class MessageSent extends MessageEvent
{
    public const TYPE = 'message.sent';
}
