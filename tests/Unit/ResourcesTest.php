<?php

use NyCorp\Shortext\Messages\Contact;
use NyCorp\Shortext\Messages\Message;
use NyCorp\Shortext\Page;
use NyCorp\Shortext\Resource;
use NyCorp\Shortext\Shortext;

/*
 * Each route: the SDK call, then the expected method, path (under /api), query and JSON body
 */
dataset('routes', [
    'me' => [fn (Shortext $s) => $s->me(), 'GET', '/v2.0/me'],

    'agents list' => [fn (Shortext $s) => $s->agents()->list(['name' => 'Sup'], page: 2), 'GET', '/v2.0/agents', 'page=2&name=Sup'],
    'agents get' => [fn (Shortext $s) => $s->agents()->get('AGI-1'), 'GET', '/v2.0/agents/AGI-1'],
    'agents create' => [fn (Shortext $s) => $s->agents()->create(['name' => 'Support', 'use_ai' => true]), 'POST', '/v2.0/agents', '', ['name' => 'Support', 'use_ai' => true]],
    'agents update' => [fn (Shortext $s) => $s->agents()->update('AGI-1', ['active' => false]), 'PATCH', '/v2.0/agents/AGI-1', '', ['active' => false]],
    'agents delete' => [fn (Shortext $s) => $s->agents()->delete('AGI-1'), 'DELETE', '/v2.0/agents/AGI-1'],
    'agents restore' => [fn (Shortext $s) => $s->agents()->restore('AGI-1'), 'PATCH', '/v2.0/agents/AGI-1/restore'],

    'channels list' => [fn (Shortext $s) => $s->channels()->list(['type' => 'whatsapp', 'agent_id' => 'none']), 'GET', '/v2.0/channels', 'type=whatsapp&agent_id=none'],
    'channels get' => [fn (Shortext $s) => $s->channels()->get('whatsapp', '5328'), 'GET', '/v2.0/channels/whatsapp/5328'],
    'channels attach' => [fn (Shortext $s) => $s->channels()->attachAgent('whatsapp', '5328', 'AGI-2'), 'PUT', '/v2.0/channels/whatsapp/5328/agent', '', ['agent_id' => 'AGI-2']],
    'channels detach' => [fn (Shortext $s) => $s->channels()->detachAgent('mail', 'box'), 'DELETE', '/v2.0/channels/mail/box/agent'],

    'shortext endpoints get' => [fn (Shortext $s) => $s->shortextEndpoints()->get('ste_1'), 'GET', '/v2.0/channels/shortext/ste_1'],
    'shortext endpoints create' => [fn (Shortext $s) => $s->shortextEndpoints()->create(['name' => 'App', 'reply_mode' => 'sync']), 'POST', '/v2.0/channels/shortext', '', ['name' => 'App', 'reply_mode' => 'sync']],
    'shortext endpoints update' => [fn (Shortext $s) => $s->shortextEndpoints()->update('ste_1', ['webhook_url' => null]), 'PATCH', '/v2.0/channels/shortext/ste_1', '', ['webhook_url' => null]],
    'shortext endpoints delete' => [fn (Shortext $s) => $s->shortextEndpoints()->delete('ste_1'), 'DELETE', '/v2.0/channels/shortext/ste_1'],
    'shortext endpoints rotate' => [fn (Shortext $s) => $s->shortextEndpoints()->rotateSecret('ste_1'), 'POST', '/v2.0/channels/shortext/ste_1/rotate-secret', '', []],

    'conversations list' => [fn (Shortext $s) => $s->conversations()->list(['ticket_id' => 'TCK-9', 'assignee' => 'none', 'status' => 'active']), 'GET', '/v2.0/conversations', 'ticket_id=TCK-9&assignee=none&status=active'],
    'conversations list since' => [fn (Shortext $s) => $s->conversations()->list(['updated_since' => new DateTimeImmutable('2026-10-01T00:00:00+00:00')]), 'GET', '/v2.0/conversations', 'updated_since=2026-10-01T00%3A00%3A00%2B00%3A00'],
    'conversations get' => [fn (Shortext $s) => $s->conversations()->get('cnv1'), 'GET', '/v2.0/conversations/cnv1'],
    'conversations messages' => [fn (Shortext $s) => $s->conversations()->messages('cnv1', 3), 'GET', '/v2.0/conversations/cnv1/messages', 'page=3'],
    'conversations start' => [
        fn (Shortext $s) => $s->conversations()->start('whatsapp', '5328', Contact::make('237699')->lastname('Awa'), Message::template('order_update', 'fr')->body(['name' => 'Awa'])),
        'POST', '/v2.0/conversations', '', [
            'channel' => 'whatsapp',
            'channel_id' => '5328',
            'contact' => ['identifier' => '237699', 'profile' => ['lastname' => 'Awa']],
            'message' => ['type' => 'template', 'content' => ['template_name' => 'order_update', 'language' => 'fr', 'body' => ['name' => 'Awa']]],
            'handover' => false,
        ],
    ],
    'conversations reply' => [fn (Shortext $s) => $s->conversations()->reply('cnv1', Message::text('Hi')), 'POST', '/v2.0/conversations/cnv1/messages', '', ['message' => ['type' => 'text', 'content' => ['text' => 'Hi']], 'handover' => true]],
    'conversations reply notification' => [fn (Shortext $s) => $s->conversations()->reply('cnv1', ['type' => 'text', 'content' => ['text' => 'Hi']], handover: false), 'POST', '/v2.0/conversations/cnv1/messages', '', ['message' => ['type' => 'text', 'content' => ['text' => 'Hi']], 'handover' => false]],
    'conversations handover' => [fn (Shortext $s) => $s->conversations()->handover('cnv1'), 'PUT', '/v2.0/conversations/cnv1/handover'],
    'conversations release' => [fn (Shortext $s) => $s->conversations()->release('cnv1'), 'DELETE', '/v2.0/conversations/cnv1/handover'],
    'conversations assign' => [fn (Shortext $s) => $s->conversations()->assign('cnv1', 'USR-1'), 'PUT', '/v2.0/conversations/cnv1/assignee', '', ['user_id' => 'USR-1']],
    'conversations unassign' => [fn (Shortext $s) => $s->conversations()->unassign('cnv1'), 'PUT', '/v2.0/conversations/cnv1/assignee', '', ['user_id' => null]],
    'conversations assignees' => [fn (Shortext $s) => $s->conversations()->assignees(), 'GET', '/v2.0/conversations/assignees'],

    'templates list' => [fn (Shortext $s) => $s->templates()->list(['status' => 'APPROVED', 'channel_id' => '5328']), 'GET', '/v2.0/templates', 'status=APPROVED&channel_id=5328'],
    'templates get' => [fn (Shortext $s) => $s->templates()->get('tpl1'), 'GET', '/v2.0/templates/tpl1'],

    'webhooks events' => [fn (Shortext $s) => $s->webhooks()->events(), 'GET', '/v2.0/webhooks/events'],
    'webhooks list' => [fn (Shortext $s) => $s->webhooks()->list(), 'GET', '/v2.0/webhooks'],
    'webhooks get' => [fn (Shortext $s) => $s->webhooks()->get('wh1'), 'GET', '/v2.0/webhooks/wh1'],
    'webhooks create' => [fn (Shortext $s) => $s->webhooks()->create('https://example.com/hook', ['message.*'], ['app' => 'AGI-1']), 'POST', '/v2.0/webhooks', '', ['url' => 'https://example.com/hook', 'events' => ['message.*'], 'filters' => ['app' => 'AGI-1']]],
    'webhooks update' => [fn (Shortext $s) => $s->webhooks()->update('wh1', ['is_enabled' => true]), 'PUT', '/v2.0/webhooks/wh1', '', ['is_enabled' => true]],
    'webhooks delete' => [fn (Shortext $s) => $s->webhooks()->delete('wh1'), 'DELETE', '/v2.0/webhooks/wh1'],
    'webhooks rotate' => [fn (Shortext $s) => $s->webhooks()->rotateSecret('wh1'), 'POST', '/v2.0/webhooks/wh1/rotate-secret', '', []],
    'webhooks test' => [fn (Shortext $s) => $s->webhooks()->test('wh1'), 'POST', '/v2.0/webhooks/wh1/test', '', []],
    'webhooks deliveries' => [fn (Shortext $s) => $s->webhooks()->deliveries('wh1', 'failed'), 'GET', '/v2.0/webhooks/wh1/deliveries', 'status=failed'],
    'webhooks retry' => [fn (Shortext $s) => $s->webhooks()->retry('wh1', 'dl1'), 'POST', '/v2.0/webhooks/wh1/deliveries/dl1/retry', '', []],

    'hosted sessions purposes' => [fn (Shortext $s) => $s->hostedSessions()->purposes(), 'GET', '/v2.0/hosted-sessions/purposes'],
    'hosted sessions create' => [fn (Shortext $s) => $s->hostedSessions()->create('whatsapp', ['agent_id' => 'AGI-1'], 'https://app.example.com/done', 'fr', 30), 'POST', '/v2.0/hosted-sessions', '', ['purpose' => 'whatsapp', 'context' => ['agent_id' => 'AGI-1'], 'return_url' => 'https://app.example.com/done', 'locale' => 'fr', 'expires_in' => 30]],
    'hosted sessions list' => [fn (Shortext $s) => $s->hostedSessions()->list(), 'GET', '/v2.0/hosted-sessions'],
    'hosted sessions get' => [fn (Shortext $s) => $s->hostedSessions()->get('hs1'), 'GET', '/v2.0/hosted-sessions/hs1'],
    'hosted sessions cancel' => [fn (Shortext $s) => $s->hostedSessions()->cancel('hs1'), 'DELETE', '/v2.0/hosted-sessions/hs1'],

    'inbound' => [
        fn (Shortext $s) => $s->inbound()->send('ste_1', Contact::make('user-42')->firstname('Awa'), 'msg-1', 'Hello', ['page' => '/checkout'], 'sync'),
        'POST', '/v1.0/messages/inbound', '', [
            'channel' => 'shortext',
            'to' => 'ste_1',
            'contact' => ['identifier' => 'user-42', 'profile' => ['firstname' => 'Awa']],
            'message' => ['id' => 'msg-1', 'type' => 'text', 'content' => ['text' => 'Hello'], 'metadata' => ['page' => '/checkout']],
            'reply_mode' => 'sync',
        ],
    ],

    'v1 send message' => [
        fn (Shortext $s) => $s->v1()->sendMessage('237699', '5328', Message::text('Hello')),
        'POST', '/messages/send', '', [
            'contact' => ['identifier' => '237699'],
            'channel' => 'whatsapp',
            'from' => '5328',
            'message' => ['type' => 'text', 'content' => ['text' => 'Hello']],
        ],
    ],
    'v1 dynamic link' => [
        fn (Shortext $s) => $s->v1()->createDynamicLink(['fallback_url' => 'https://a.com', 'deep_link' => 'app://x']),
        'POST', '/deep-link/add', '', ['fallback_url' => 'https://a.com', 'deep_link' => 'app://x'],
    ],
]);

it('sends the right request', function (Closure $call, string $method, string $path, string $query = '', ?array $json = null) {
    $call(fakeShortext([envelope([])]));

    $request = lastRequest();
    expect($request->getMethod())->toBe($method)
        ->and($request->getUri()->getPath())->toBe('/api'.$path)
        ->and($request->getUri()->getQuery())->toBe($query);

    if ($json !== null) {
        expect(requestJson())->toBe($json);
    }
})->with('routes');

it('maps items to resources', function () {
    $agent = fakeShortext([envelope(['id' => 'AGI-1', 'name' => 'Support', 'channels' => [['type' => 'whatsapp', 'id' => '5328']]])])->agents()->get('AGI-1');

    expect($agent)->toBeInstanceOf(Resource::class)
        ->and($agent->id)->toBe('AGI-1')
        ->and($agent['name'])->toBe('Support')
        ->and($agent->get('channels.0.type'))->toBe('whatsapp')
        ->and($agent->has('missing'))->toBeFalse()
        ->and($agent->missing)->toBeNull()
        ->and(json_encode($agent))->toContain('"AGI-1"');
});

it('keeps resources read-only', function () {
    $agent = Resource::from(['id' => 'AGI-1']);

    expect(fn () => $agent['id'] = 'x')->toThrow(LogicException::class);
});

it('maps paginated bodies to pages and loads the next ones', function () {
    $shortext = fakeShortext([
        envelope(paginated([['id' => 'c1'], ['id' => 'c2']], 1, 2)),
        envelope(paginated([['id' => 'c3']], 2, 2)),
    ]);

    $page = $shortext->conversations()->list(['channel' => 'whatsapp']);

    expect($page)->toBeInstanceOf(Page::class)
        ->and($page)->toHaveCount(2)
        ->and($page->currentPage())->toBe(1)
        ->and($page->lastPage())->toBe(2)
        ->and($page->perPage())->toBe(50)
        ->and($page->hasMorePages())->toBeTrue()
        ->and($page->first()->id)->toBe('c1');

    $ids = array_map(fn (Resource $c) => $c->id, iterator_to_array($page->all(), false));

    expect($ids)->toBe(['c1', 'c2', 'c3'])
        ->and(sentRequests()[1]->getUri()->getQuery())->toBe('page=2&channel=whatsapp');
});

it('maps plain lists', function () {
    $channels = fakeShortext([envelope([['type' => 'whatsapp', 'id' => '5328'], ['type' => 'mail', 'id' => 'box']])])->channels()->list();

    expect($channels)->toHaveCount(2)
        ->and($channels[1]->type)->toBe('mail');
});

it('returns the new webhook secret', function () {
    expect(fakeShortext([envelope(['secret' => 'whsec_new'])])->webhooks()->rotateSecret('wh1'))->toBe('whsec_new');
});

it('returns the hosted session purposes', function () {
    expect(fakeShortext([envelope(['whatsapp' => 'Connect WhatsApp', 'mail' => 'Connect a mailbox'])])->hostedSessions()->purposes())
        ->toBe(['whatsapp' => 'Connect WhatsApp', 'mail' => 'Connect a mailbox']);
});

it('returns the inbound outcome and the sync replies', function () {
    $result = fakeShortext([envelope(['status' => 'completed', 'message' => ['id' => 'msg.1'], 'replies' => [['message' => ['content' => ['text' => 'Hi!']]]]])])
        ->inbound()->send('ste_1', 'user-42', 'msg-1', 'Hello');

    expect($result->status)->toBe('completed')
        ->and($result->get('replies.0.message.content.text'))->toBe('Hi!')
        ->and(requestJson())->not->toHaveKey('reply_mode');
});
