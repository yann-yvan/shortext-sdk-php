<?php

namespace NyCorp\Shortext\Webhooks\Events;

use DateTimeImmutable;
use NyCorp\Shortext\Exceptions\InvalidSignatureException;
use NyCorp\Shortext\Resource;

/**
 * A webhook body: { id, type, api_version, created_at, data }.
 *
 * Delivery order is not guaranteed and an event may be delivered twice: dedupe on id().
 * Unknown types (added to the API later) are decoded as UnknownEvent.
 */
abstract class WebhookEvent
{
    /** @var array<string, class-string<WebhookEvent>> */
    public const TYPES = [
        MessageReceived::TYPE => MessageReceived::class,
        MessageSent::TYPE => MessageSent::class,
        MessageStatusUpdated::TYPE => MessageStatusUpdated::class,
        ConversationHandover::TYPE => ConversationHandover::class,
        ConversationReleased::TYPE => ConversationReleased::class,
        ChannelConnected::TYPE => ChannelConnected::class,
        ChannelAgentChanged::TYPE => ChannelAgentChanged::class,
        AgentCreated::TYPE => AgentCreated::class,
        AgentUpdated::TYPE => AgentUpdated::class,
        AgentDeleted::TYPE => AgentDeleted::class,
        HostedSessionCompleted::TYPE => HostedSessionCompleted::class,
        HostedSessionFailed::TYPE => HostedSessionFailed::class,
        WebhookTest::TYPE => WebhookTest::class,
    ];

    protected Resource $data;

    /**
     * @param  array<string, mixed>  $payload
     */
    final public function __construct(protected array $payload)
    {
        $this->data = Resource::from(is_array($payload['data'] ?? null) ? $payload['data'] : []);
    }

    /**
     * Decodes a body already verified (WebhookVerifier::event() verifies and decodes)
     *
     * @param  string|array<string, mixed>  $payload
     */
    public static function fromPayload(string|array $payload): self
    {
        if (is_string($payload)) {
            $payload = json_decode($payload, true);

            if (! is_array($payload)) {
                throw new InvalidSignatureException('The webhook body is not a JSON object');
            }
        }

        $class = self::TYPES[$payload['type'] ?? ''] ?? UnknownEvent::class;

        return new $class($payload);
    }

    /** Event id (evt_...), identical on redeliveries */
    public function id(): string
    {
        return (string) ($this->payload['id'] ?? '');
    }

    public function type(): string
    {
        return (string) ($this->payload['type'] ?? '');
    }

    /** Version of the payload: the one used to create the endpoint */
    public function apiVersion(): ?string
    {
        return $this->payload['api_version'] ?? null;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        $date = $this->payload['created_at'] ?? null;

        return is_string($date) ? new DateTimeImmutable($date) : null;
    }

    public function data(): Resource
    {
        return $this->data;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->payload;
    }
}
