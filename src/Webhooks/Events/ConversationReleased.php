<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * A conversation was handed back to the agent
 */
class ConversationReleased extends ConversationEvent
{
    public const TYPE = 'conversation.released';
}
