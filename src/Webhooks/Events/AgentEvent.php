<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * agent.*: data is an Agent (without its channels)
 */
abstract class AgentEvent extends WebhookEvent
{
    public function agentId(): ?string
    {
        return $this->data->get('id');
    }
}
