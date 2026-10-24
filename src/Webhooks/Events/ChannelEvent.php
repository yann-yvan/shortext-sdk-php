<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * channel.*: data is a Channel, as in GET /channels/{type}/{id}
 */
abstract class ChannelEvent extends WebhookEvent
{
    /** whatsapp, mail or shortext */
    public function channelType(): ?string
    {
        return $this->data->get('type');
    }

    public function channelId(): ?string
    {
        return $this->data->get('id');
    }

    /** Agent now answering on the channel, null when detached */
    public function agentId(): ?string
    {
        return $this->data->get('agent.id');
    }
}
