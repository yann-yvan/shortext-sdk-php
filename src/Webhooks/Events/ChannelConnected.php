<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * A channel (WhatsApp number, mailbox, Shortext endpoint) was connected
 */
class ChannelConnected extends ChannelEvent
{
    public const TYPE = 'channel.connected';
}
