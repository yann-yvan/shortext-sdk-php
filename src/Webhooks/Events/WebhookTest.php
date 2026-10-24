<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * Sent by webhooks()->test() and the dashboard
 */
class WebhookTest extends WebhookEvent
{
    public const TYPE = 'webhook.test';
}
