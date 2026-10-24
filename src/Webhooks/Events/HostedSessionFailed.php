<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * A hosted session failed: error() says why
 */
class HostedSessionFailed extends HostedSessionEvent
{
    public const TYPE = 'hosted_session.failed';

    public function error(): ?string
    {
        return $this->data->get('error');
    }
}
