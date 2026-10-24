<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * A hosted session was completed: result() holds what it produced
 */
class HostedSessionCompleted extends HostedSessionEvent
{
    public const TYPE = 'hosted_session.completed';

    /** @return array<string, mixed> e.g. the connected channels */
    public function result(): array
    {
        return (array) $this->data->get('result', []);
    }
}
