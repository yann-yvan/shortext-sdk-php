<?php

namespace NyCorp\Shortext\Api;

use NyCorp\Shortext\Messages\Contact;
use NyCorp\Shortext\Resource;

/**
 * POST /v1.0/messages/inbound: your server posts the messages of your users to a Shortext endpoint.
 *
 * Replies: with reply_mode "sync" the call waits for the agent (max ~25 s) and returns them in `replies`;
 * with "async" (or when sync times out) they are POSTed to the endpoint webhook_url as signed message.sent events.
 */
class Inbound extends ApiResource
{
    public const SYNC = 'sync';

    public const ASYNC = 'async';

    /** Agent replies included */
    public const STATUS_COMPLETED = 'completed';

    /** Sync timed out: replies will arrive via webhook / conversation API */
    public const STATUS_PENDING = 'pending';

    /** Async: replies will arrive via webhook */
    public const STATUS_ACCEPTED = 'accepted';

    /** Stored without automatic reply (human handover, balance, no agent) */
    public const STATUS_NO_AUTO_REPLY = 'no_auto_reply';

    /** Same message id already received: the original message, without a second reply */
    public const STATUS_DUPLICATE = 'duplicate';

    /**
     * @param  string  $endpointId  Shortext endpoint id (ste_...)
     * @param  Contact|string|array  $contact  your own user id, unique within your company
     * @param  string  $messageId  your own message id, used for idempotency
     * @param  array<string, mixed>  $metadata  free context stored with the message (page, cart...)
     * @param  'sync'|'async'|null  $replyMode  defaults to the endpoint setting
     * @return resource { status, message, replies }
     */
    public function send(string $endpointId, Contact|string|array $contact, string $messageId, string $text, array $metadata = [], ?string $replyMode = null): Resource
    {
        $message = ['id' => $messageId, 'type' => 'text', 'content' => ['text' => $text]];
        if ($metadata !== []) {
            $message['metadata'] = $metadata;
        }

        $response = $this->http->request('POST', '/v1.0/messages/inbound', json: array_filter([
            'channel' => 'shortext',
            'to' => $endpointId,
            'contact' => Contact::from($contact)->toArray(),
            'message' => $message,
            'reply_mode' => $replyMode,
        ], static fn ($value) => $value !== null))->throw();

        return $this->item($response);
    }
}
