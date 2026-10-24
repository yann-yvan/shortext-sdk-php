<?php

use NyCorp\Shortext\Messages\Contact;
use NyCorp\Shortext\Messages\Message;

it('builds a text message', function () {
    expect(Message::text('  Hello  ')->toArray())->toBe(['type' => 'text', 'content' => ['text' => 'Hello']]);
});

it('builds a template with body variables and a text header', function () {
    $message = Message::template('order_update', 'fr')
        ->body(['name' => 'Awa', 'order' => 42])
        ->header(['shop' => 'Acme'])
        ->category('UTILITY');

    expect($message->toArray())->toBe(['type' => 'template', 'content' => [
        'template_name' => 'order_update',
        'language' => 'fr',
        'body' => ['name' => 'Awa', 'order' => '42'],
        'header' => ['shop' => 'Acme'],
        'category' => 'UTILITY',
    ]]);
});

it('builds a template with a media header', function () {
    expect(Message::template('receipt', 'en')->headerMedia('https://cdn.example.com/r.pdf')->toArray()['content'])
        ->toBe(['template_name' => 'receipt', 'language' => 'en', 'header' => ['url' => 'https://cdn.example.com/r.pdf']]);
});

it('builds media messages', function () {
    expect(Message::image('https://a.com/i.png', 'Receipt')->toArray())
        ->toBe(['type' => 'media', 'content' => ['media_type' => 'image', 'media_url' => 'https://a.com/i.png', 'caption' => 'Receipt']])
        ->and(Message::document('https://a.com/d.pdf', 'invoice.pdf')->toArray()['content'])
        ->toBe(['media_type' => 'document', 'media_url' => 'https://a.com/d.pdf', 'filename' => 'invoice.pdf'])
        ->and(Message::audio('https://a.com/a.ogg')->toArray()['content'])
        ->toBe(['media_type' => 'audio', 'media_url' => 'https://a.com/a.ogg'])
        ->and(Message::media('video', 'https://a.com/v.mp4', mime: 'video/mp4')->toArray()['content'])
        ->toBe(['media_type' => 'video', 'media_url' => 'https://a.com/v.mp4', 'mime' => 'video/mp4']);
});

it('builds reply buttons with a media header', function () {
    $message = Message::buttons('Confirm the order?')
        ->button('yes', 'Yes')
        ->button('no', 'No')
        ->imageHeader('https://a.com/i.png')
        ->footer('Acme');

    expect($message->toArray())->toBe(['type' => 'interactive', 'content' => [
        'type' => 'button',
        'body' => 'Confirm the order?',
        'action' => [['id' => 'yes', 'title' => 'Yes'], ['id' => 'no', 'title' => 'No']],
        'header' => ['type' => 'image', 'value' => 'https://a.com/i.png'],
        'footer' => 'Acme',
    ]]);
});

it('refuses a fourth button', function () {
    $message = Message::buttons('?')->button('1', 'A')->button('2', 'B')->button('3', 'C');

    expect(fn () => $message->button('4', 'D'))->toThrow(LengthException::class);
});

it('builds a list', function () {
    $message = Message::list('Pick a slot', 'Slots')
        ->section('Morning', [Message::row('9', '9:00'), Message::row('10', '10:00', 'Last one')])
        ->header('Booking');

    expect($message->toArray()['content'])->toBe([
        'type' => 'list',
        'body' => 'Pick a slot',
        'action' => ['button' => 'Slots', 'sections' => [[
            'title' => 'Morning',
            'rows' => [['id' => '9', 'title' => '9:00'], ['id' => '10', 'title' => '10:00', 'description' => 'Last one']],
        ]]],
        'header' => ['type' => 'text', 'value' => 'Booking'],
    ]);
});

it('builds cta_url and payment messages', function () {
    expect(Message::ctaUrl('Track it', 'https://a.com/t', 'Open')->documentHeader('https://a.com/d.pdf', 'd.pdf')->toArray()['content'])
        ->toBe([
            'type' => 'cta_url',
            'body' => 'Track it',
            'action' => ['parameters' => ['display_text' => 'Open', 'url' => 'https://a.com/t']],
            'header' => ['type' => 'document', 'value' => 'https://a.com/d.pdf', 'filename' => 'd.pdf'],
        ])
        ->and(Message::payment('Order A-42', 1500, 'XAF', 'A-42')->toArray()['content'])
        ->toBe([
            'type' => 'payment',
            'body' => 'Order A-42',
            'action' => ['parameters' => ['amount' => 1500.0, 'currency' => 'XAF', 'order_id' => 'A-42', 'callback_url' => null]],
        ]);
});

it('builds raw messages', function () {
    expect(Message::raw('text', ['text' => 'x'])->toArray())->toBe(['type' => 'text', 'content' => ['text' => 'x']])
        ->and(json_encode(Message::interactive('flow', 'Go', ['name' => 'f'])))->toBe('{"type":"interactive","content":{"type":"flow","body":"Go","action":{"name":"f"}}}');
});

it('builds contacts', function () {
    expect(Contact::make('237699')->firstname('Awa')->lastname('Ngo')->email('awa@example.com')->preferredLanguage('fr')->toArray())
        ->toBe(['identifier' => '237699', 'profile' => ['firstname' => 'Awa', 'lastname' => 'Ngo', 'email' => 'awa@example.com', 'preferred_language' => 'fr']])
        ->and(Contact::make('237699')->lastname('')->toArray())->toBe(['identifier' => '237699'])
        ->and(Contact::from('user-42')->toArray())->toBe(['identifier' => 'user-42'])
        ->and(Contact::from(['identifier' => 'u', 'profile' => ['lastname' => 'L']])->toArray())->toBe(['identifier' => 'u', 'profile' => ['lastname' => 'L']]);
});
