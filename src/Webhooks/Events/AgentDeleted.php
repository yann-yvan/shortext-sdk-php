<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * An agent was deleted
 */
class AgentDeleted extends AgentEvent
{
    public const TYPE = 'agent.deleted';
}
