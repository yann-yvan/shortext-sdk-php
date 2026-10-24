<?php

namespace NyCorp\Shortext\Webhooks\Events;

/**
 * message.*: data is a Message, as in GET /conversations/{id}/messages
 */
abstract class MessageEvent extends WebhookEvent
{
    public function messageId(): ?string
    {
        return $this->data->get('id');
    }

    public function conversationId(): ?string
    {
        return $this->data->get('conversation_id');
    }

    /** whatsapp, mail or shortext */
    public function channel(): ?string
    {
        return $this->data->get('channel');
    }

    public function channelId(): ?string
    {
        return $this->data->get('channel_id');
    }

    /** inbound or outbound */
    public function direction(): ?string
    {
        return $this->data->get('direction');
    }

    public function contactIdentifier(): ?string
    {
        return $this->data->get('contact.identifier');
    }

    /** text, template, interactive, media... */
    public function messageType(): ?string
    {
        return $this->data->get('message.type');
    }

    /** @return array<string, mixed> */
    public function content(): array
    {
        return (array) $this->data->get('message.content', []);
    }

    /** Text of a text message */
    public function text(): ?string
    {
        return $this->data->get('message.content.text');
    }

    public function status(): ?string
    {
        return $this->data->get('status');
    }
}
