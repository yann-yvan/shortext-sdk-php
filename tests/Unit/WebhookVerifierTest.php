<?php

use NyCorp\Shortext\Exceptions\InvalidSignatureException;
use NyCorp\Shortext\Webhooks\Events\AgentCreated;
use NyCorp\Shortext\Webhooks\Events\AgentDeleted;
use NyCorp\Shortext\Webhooks\Events\AgentUpdated;
use NyCorp\Shortext\Webhooks\Events\ChannelAgentChanged;
use NyCorp\Shortext\Webhooks\Events\ChannelConnected;
use NyCorp\Shortext\Webhooks\Events\ConversationHandover;
use NyCorp\Shortext\Webhooks\Events\ConversationReleased;
use NyCorp\Shortext\Webhooks\Events\HostedSessionCompleted;
use NyCorp\Shortext\Webhooks\Events\HostedSessionFailed;
use NyCorp\Shortext\Webhooks\Events\MessageReceived;
use NyCorp\Shortext\Webhooks\Events\MessageSent;
use NyCorp\Shortext\Webhooks\Events\MessageStatusUpdated;
use NyCorp\Shortext\Webhooks\Events\UnknownEvent;
use NyCorp\Shortext\Webhooks\Events\WebhookEvent;
use NyCorp\Shortext\Webhooks\Events\WebhookTest;
use NyCorp\Shortext\Webhooks\WebhookVerifier;

const SECRET = 'whsec_test';
const NOW = 1_790_000_000;

function verifier(int $tolerance = 300): WebhookVerifier
{
    return new WebhookVerifier(SECRET, $tolerance, fn () => NOW);
}

function webhookBody(string $type, array $data = []): string
{
    return json_encode(['id' => 'evt_1', 'type' => $type, 'api_version' => 'v2.0', 'created_at' => '2026-10-24T10:00:00+00:00', 'data' => $data]);
}

it('computes the documented signature', function () {
    // sha256=hex(hmac_sha256(secret, "{timestamp}.{raw body}"))
    expect(WebhookVerifier::sign('secret', '1700000000', '{"a":1}'))
        ->toBe('sha256='.hash_hmac('sha256', '1700000000.{"a":1}', 'secret'));
});

it('accepts a valid signature', function () {
    $body = webhookBody('message.received');

    expect(verifier()->isValid($body, WebhookVerifier::sign(SECRET, NOW, $body), (string) NOW))->toBeTrue();
});

it('accepts a timestamp inside the tolerance window', function (int $offset) {
    $body = webhookBody('message.received');
    $timestamp = NOW + $offset;

    expect(verifier()->isValid($body, WebhookVerifier::sign(SECRET, $timestamp, $body), $timestamp))->toBeTrue();
})->with([-300, 300]);

it('rejects invalid webhooks', function (Closure $tamper, string $message) {
    $body = webhookBody('message.received');
    [$payload, $signature, $timestamp] = $tamper($body, WebhookVerifier::sign(SECRET, NOW, $body), (string) NOW);

    expect(fn () => verifier()->verify($payload, $signature, $timestamp))->toThrow(InvalidSignatureException::class, $message);
})->with([
    'tampered body' => [fn ($b, $s, $t) => [$b.' ', $s, $t], 'Invalid webhook signature'],
    'wrong secret' => [fn ($b, $s, $t) => [$b, WebhookVerifier::sign('other', $t, $b), $t], 'Invalid webhook signature'],
    'timestamp not signed' => [fn ($b, $s, $t) => [$b, $s, (string) ($t + 1)], 'Invalid webhook signature'],
    'too old' => [fn ($b, $s, $t) => [$b, WebhookVerifier::sign(SECRET, NOW - 301, $b), (string) (NOW - 301)], 'tolerance'],
    'in the future' => [fn ($b, $s, $t) => [$b, WebhookVerifier::sign(SECRET, NOW + 301, $b), (string) (NOW + 301)], 'tolerance'],
    'no signature' => [fn ($b, $s, $t) => [$b, null, $t], 'Missing'],
    'no timestamp' => [fn ($b, $s, $t) => [$b, $s, null], 'Missing'],
    'non numeric timestamp' => [fn ($b, $s, $t) => [$b, $s, 'yesterday'], 'Invalid X-Shortext-Timestamp'],
]);

it('refuses to verify without a secret', function () {
    expect(fn () => (new WebhookVerifier(''))->verify('{}', 'sha256=x', (string) time()))->toThrow(InvalidSignatureException::class, 'secret');
});

it('can disable the tolerance check', function () {
    $body = webhookBody('message.received');

    expect(verifier(0)->isValid($body, WebhookVerifier::sign(SECRET, 1, $body), 1))->toBeTrue();
});

it('reads the headers in any case and format', function (array $headers) {
    $body = webhookBody('message.received', ['conversation_id' => 'cnv1']);
    $signature = WebhookVerifier::sign(SECRET, NOW, $body);
    $headers = array_map(fn ($value) => str_replace(['{sig}', '{ts}'], [$signature, (string) NOW], $value), $headers);

    expect(verifier()->event($body, $headers))->toBeInstanceOf(MessageReceived::class);
})->with([
    'canonical' => [['X-Shortext-Signature' => '{sig}', 'X-Shortext-Timestamp' => '{ts}']],
    'lower case' => [['x-shortext-signature' => '{sig}', 'x-shortext-timestamp' => '{ts}']],
    '$_SERVER' => [['HTTP_X_SHORTEXT_SIGNATURE' => '{sig}', 'HTTP_X_SHORTEXT_TIMESTAMP' => '{ts}']],
]);

it('reads Symfony style header arrays', function () {
    $body = webhookBody('webhook.test');
    $headers = ['x-shortext-signature' => [WebhookVerifier::sign(SECRET, NOW, $body)], 'x-shortext-timestamp' => [(string) NOW]];

    expect(verifier()->event($body, $headers))->toBeInstanceOf(WebhookTest::class);
});

it('does not decode an unverified webhook', function () {
    expect(fn () => verifier()->event(webhookBody('message.received'), ['X-Shortext-Signature' => 'sha256=bad', 'X-Shortext-Timestamp' => (string) NOW]))
        ->toThrow(InvalidSignatureException::class);
});

it('decodes message events', function () {
    $event = WebhookEvent::fromPayload(webhookBody('message.received', [
        'id' => 'msg.1',
        'conversation_id' => 'cnv1',
        'direction' => 'inbound',
        'channel' => 'whatsapp',
        'channel_id' => '5328',
        'contact' => ['identifier' => '237699'],
        'message' => ['id' => 'wamid.1', 'type' => 'text', 'content' => ['text' => 'Hello']],
        'status' => 'received',
    ]));

    expect($event)->toBeInstanceOf(MessageReceived::class)
        ->and($event->id())->toBe('evt_1')
        ->and($event->type())->toBe('message.received')
        ->and($event->apiVersion())->toBe('v2.0')
        ->and($event->createdAt()->format('Y-m-d'))->toBe('2026-10-24')
        ->and($event->messageId())->toBe('msg.1')
        ->and($event->conversationId())->toBe('cnv1')
        ->and($event->channel())->toBe('whatsapp')
        ->and($event->channelId())->toBe('5328')
        ->and($event->direction())->toBe('inbound')
        ->and($event->contactIdentifier())->toBe('237699')
        ->and($event->messageType())->toBe('text')
        ->and($event->text())->toBe('Hello')
        ->and($event->content())->toBe(['text' => 'Hello'])
        ->and($event->status())->toBe('received')
        ->and($event->data()->get('message.id'))->toBe('wamid.1');
});

it('maps every documented event type', function (string $type, string $class) {
    expect(WebhookEvent::fromPayload(webhookBody($type)))->toBeInstanceOf($class);
})->with([
    ['message.received', MessageReceived::class],
    ['message.sent', MessageSent::class],
    ['message.status', MessageStatusUpdated::class],
    ['conversation.handover', ConversationHandover::class],
    ['conversation.released', ConversationReleased::class],
    ['channel.connected', ChannelConnected::class],
    ['channel.agent_changed', ChannelAgentChanged::class],
    ['agent.created', AgentCreated::class],
    ['agent.updated', AgentUpdated::class],
    ['agent.deleted', AgentDeleted::class],
    ['hosted_session.completed', HostedSessionCompleted::class],
    ['hosted_session.failed', HostedSessionFailed::class],
    ['webhook.test', WebhookTest::class],
    ['something.new', UnknownEvent::class],
]);

it('exposes the typed accessors of the other events', function () {
    $handover = WebhookEvent::fromPayload(webhookBody('conversation.handover', ['conversation_id' => 'cnv1', 'channel' => 'mail', 'channel_id' => 'box', 'contact' => ['identifier' => 'a@b.c']]));
    $channel = WebhookEvent::fromPayload(webhookBody('channel.agent_changed', ['type' => 'whatsapp', 'id' => '5328', 'agent' => ['id' => 'AGI-2'], 'previous_agent' => ['id' => 'AGI-1']]));
    $agent = WebhookEvent::fromPayload(webhookBody('agent.created', ['id' => 'AGI-3', 'name' => 'Sales']));
    $completed = WebhookEvent::fromPayload(webhookBody('hosted_session.completed', ['id' => 'hs1', 'purpose' => 'whatsapp', 'context' => ['agent_id' => 'AGI-1'], 'result' => ['channels' => ['5328']]]));
    $failed = WebhookEvent::fromPayload(webhookBody('hosted_session.failed', ['id' => 'hs2', 'error' => 'Cancelled by the user']));

    expect($handover->conversationId())->toBe('cnv1')
        ->and($handover->channel())->toBe('mail')
        ->and($handover->channelId())->toBe('box')
        ->and($handover->contactIdentifier())->toBe('a@b.c')
        ->and($channel->channelType())->toBe('whatsapp')
        ->and($channel->channelId())->toBe('5328')
        ->and($channel->agentId())->toBe('AGI-2')
        ->and($channel->previousAgentId())->toBe('AGI-1')
        ->and($agent->agentId())->toBe('AGI-3')
        ->and($agent->data()->name)->toBe('Sales')
        ->and($completed->sessionId())->toBe('hs1')
        ->and($completed->purpose())->toBe('whatsapp')
        ->and($completed->context())->toBe(['agent_id' => 'AGI-1'])
        ->and($completed->result())->toBe(['channels' => ['5328']])
        ->and($failed->error())->toBe('Cancelled by the user');
});

it('rejects a body that is not JSON', function () {
    expect(fn () => WebhookEvent::fromPayload('not json'))->toThrow(InvalidSignatureException::class);
});
