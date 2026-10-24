<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * A message status changed (delivered, read, failed...)
 */
class MessageStatusUpdated extends MessageEvent
{
    public const TYPE = 'message.status';
}
