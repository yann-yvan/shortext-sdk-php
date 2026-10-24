<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * conversation.*: data is { conversation_id, channel, channel_id, contact: { identifier } }
 */
abstract class ConversationEvent extends WebhookEvent
{
    public function conversationId(): ?string
    {
        return $this->data->get('conversation_id');
    }

    public function channel(): ?string
    {
        return $this->data->get('channel');
    }

    public function channelId(): ?string
    {
        return $this->data->get('channel_id');
    }

    public function contactIdentifier(): ?string
    {
        return $this->data->get('contact.identifier');
    }
}
