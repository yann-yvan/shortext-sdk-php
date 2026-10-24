<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * hosted_session.*: data is a HostedSession
 */
abstract class HostedSessionEvent extends WebhookEvent
{
    public function sessionId(): ?string
    {
        return $this->data->get('id');
    }

    public function purpose(): ?string
    {
        return $this->data->get('purpose');
    }

    /** @return array<string, mixed> */
    public function context(): array
    {
        return (array) $this->data->get('context', []);
    }
}
