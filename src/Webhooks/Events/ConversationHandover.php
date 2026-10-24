<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * A human took over a conversation: the agent stops answering
 */
class ConversationHandover extends ConversationEvent
{
    public const TYPE = 'conversation.handover';
}
