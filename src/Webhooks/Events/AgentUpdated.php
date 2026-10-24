<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * An agent was updated (settings or instructions)
 */
class AgentUpdated extends AgentEvent
{
    public const TYPE = 'agent.updated';
}
