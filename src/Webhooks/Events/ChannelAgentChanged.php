<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * A channel was attached to an agent, moved to another one or detached
 */
class ChannelAgentChanged extends ChannelEvent
{
    public const TYPE = 'channel.agent_changed';

    /** Agent the channel was attached to before, null if it was detached */
    public function previousAgentId(): ?string
    {
        return $this->data->get('previous_agent.id');
    }
}
