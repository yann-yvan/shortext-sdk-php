<?php

namespace NyCorp\Shortext\Api;

use NyCorp\Shortext\Messages\Contact;
use NyCorp\Shortext\Messages\Message;
use NyCorp\Shortext\Resource;
use NyCorp\Shortext\Sdk\Builder\PayloadBuilder;

/**
 * API v1.0 routes kept by the SDK: sending a message from a channel, dynamic links.
 * v1.0 does not check the permissions of the API key.
 */
class V1 extends ApiResource
{
    /**
     * POST /messages/send
     *
     * @param  string  $from  your channel: WhatsApp phone id, mailbox...
     * @param  string  $channel  whatsapp, mail...
     */
    public function sendMessage(Contact|string|array $to, string $from, Message|array $message, string $channel = 'whatsapp'): Resource
    {
        return $this->post('/messages/send', [
            'contact' => Contact::from($to)->toArray(),
            'channel' => $channel,
            'from' => $from,
            'message' => $message instanceof Message ? $message->toArray() : $message,
        ]);
    }

    /**
     * POST /deep-link/add
     *
     * @param  PayloadBuilder|array{fallback_url: string, deep_link: string, name?: string, android_package_name?: string, ios_package_name?: string, ttl?: int}  $link
     */
    public function createDynamicLink(PayloadBuilder|array $link): Resource
    {
        return $this->post('/deep-link/add', $link instanceof PayloadBuilder ? $link->getPayload() : $link);
    }

    /** Sends a 1.x builder (MessageBuilder, MessageButtonReply...) */
    public function sendPayload(PayloadBuilder $builder): Resource
    {
        return $this->post('/messages/send', $builder->getPayload());
    }

    private function post(string $path, array $payload): Resource
    {
        return $this->item($this->http->request('POST', $path, json: $payload)->throw());
    }
}
