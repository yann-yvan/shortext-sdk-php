<?php

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use NyCorp\Shortext\Http\Response;
use NyCorp\Shortext\Sdk\Builder\DynamicLinkBuilder;
use NyCorp\Shortext\Sdk\Builder\Interactive\MessageButtonReply;
use NyCorp\Shortext\Sdk\Builder\MessageBuilder;
use NyCorp\Shortext\Sdk\ShortextClient;

function legacyClient(array $responses, array &$history): ShortextClient
{
    $stack = HandlerStack::create(new MockHandler($responses));
    $stack->push(Middleware::history($history));

    return new ShortextClient('legacy-key', options: ['handler' => $stack]);
}

it('still sends 1.x builders to POST /messages/send', function () {
    $history = [];
    $response = legacyClient([envelope(['id' => 'msg.1'])], $history)
        ->sendMessage((new MessageBuilder('237699', from: '5328', lastname: 'Awa'))->text('Hello'));

    $request = $history[0]['request'];
    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->isSuccess())->toBeTrue()
        ->and($response->getBody())->toBe(['id' => 'msg.1'])
        ->and((string) $request->getUri())->toBe('https://shortext.ny-corp.io/api/messages/send')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer legacy-key')
        ->and(json_decode((string) $request->getBody(), true))->toBe([
            'contact' => ['identifier' => '237699', 'profile' => ['lastname' => 'Awa']],
            'channel' => 'whatsapp',
            'from' => '5328',
            'message' => ['type' => 'text', 'content' => ['text' => 'Hello']],
        ]);
});

it('never throws, as in 1.x', function () {
    $history = [];
    $client = legacyClient([
        envelope(null, false, 1002, 'Invalid'),
        new ConnectException('Connection refused', new Request('POST', 'x')),
    ], $history);
    $builder = (new MessageBuilder('237699', '5328'))->text('Hello');

    $invalid = $client->sendMessage($builder);
    $unreachable = $client->sendMessage($builder);

    expect($invalid->isSuccess())->toBeFalse()
        ->and($invalid->getCode())->toBe(1002)
        ->and($unreachable->isSuccess())->toBeFalse()
        ->and($unreachable->getCode())->toBe(1006)
        ->and($unreachable->getMessage())->toContain('Connection refused');
});

it('creates dynamic links', function () {
    $history = [];
    legacyClient([envelope(['url' => 'https://s.io/x'])], $history)->createDynamicLink(
        (new DynamicLinkBuilder)->setFallbackUrl('https://a.com')->setDeepLink('app://x')->setTtl(60)
    );

    expect($history[0]['request']->getUri()->getPath())->toBe('/api/deep-link/add')
        ->and(json_decode((string) $history[0]['request']->getBody(), true))->toBe([
            'fallback_url' => 'https://a.com',
            'name' => null,
            'deep_link' => 'app://x',
            'android_package_name' => null,
            'ios_package_name' => null,
            'ttl' => 60,
        ]);
});

it('sends the interactive headers in the API grammar', function () {
    $cta = (new MessageBuilder('1', '2'))->ctaUrl('Body', 'https://a.com', 'Open', header: 'Title')->getPayload();
    $list = (new MessageBuilder('1', '2'))->interactiveList('Body', 'Menu', [], header: 'Title')->getPayload();
    $buttons = (new MessageButtonReply('1', '2'))->setBody('Body')->addTextHeader('Title')->addButton('a', 'A')->getPayload();

    expect($cta['message']['content']['header'])->toBe(['type' => 'text', 'value' => 'Title'])
        ->and($list['message']['content']['header'])->toBe(['type' => 'text', 'value' => 'Title'])
        ->and($buttons['message']['content']['header'])->toBe(['type' => 'text', 'value' => 'Title'])
        ->and($buttons['message']['content']['action'])->toBe([['id' => 'a', 'title' => 'A']]);
});

it('resolves the 1.x "Nycorp" namespace spelling', function () {
    $class = 'Nycorp\Shortext\Sdk\Builder\DynamicLinkBuilder';

    expect(class_exists($class))->toBeTrue()
        ->and(new $class)->toBeInstanceOf(DynamicLinkBuilder::class);
});

it('sends 1.x builders through the new client', function () {
    fakeShortext([envelope([])])->v1()->sendPayload((new MessageBuilder('237699', '5328'))->text('Hi'));

    expect(lastRequest()->getUri()->getPath())->toBe('/api/messages/send')
        ->and(requestJson()['message'])->toBe(['type' => 'text', 'content' => ['text' => 'Hi']]);
});
