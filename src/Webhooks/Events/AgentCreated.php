<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * An agent was created
 */
class AgentCreated extends AgentEvent
{
    public const TYPE = 'agent.created';
}
