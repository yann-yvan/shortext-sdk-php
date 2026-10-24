# Shortext PHP SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nycorp/shortext-php.svg?style=flat-square)](https://packagist.org/packages/nycorp/shortext-php)
[![Total Downloads](https://img.shields.io/packagist/dt/nycorp/shortext-php.svg?style=flat-square)](https://packagist.org/packages/nycorp/shortext-php)

PHP client for the [Shortext](https://shortext.ny-corp.io) API: AI agents answering your customers on WhatsApp,
mailboxes and your own apps (Shortext endpoints).

It covers the whole API **v2.0** (agents, channels, conversations, templates, webhooks, hosted sessions),
the Shortext endpoint inbound call, webhook signature verification with typed events, and the v1.0 message sending
used by existing projects. The core works in any PHP project; Laravel gets a service provider, a facade and a config file.

- [Installation](#installation)
- [Configuration](#configuration)
- [Responses and errors](#responses-and-errors)
- [Messages and contacts](#messages-and-contacts)
- [Resources](#resources): [me](#me), [agents](#agents), [channels](#channels), [Shortext endpoints](#shortext-endpoints),
  [conversations](#conversations), [templates](#templates), [webhooks](#webhook-endpoints),
  [hosted sessions](#hosted-sessions), [inbound messages](#inbound-messages-shortext-endpoints), [v1.0](#api-v10)
- [Receiving webhooks](#receiving-webhooks)
- [Testing your integration](#testing-your-integration)
- [Upgrading from 1.x](#upgrading-from-1x)

## Installation

Requires PHP 8.2+.

```bash
composer require nycorp/shortext-php
```

## Configuration

Create a company API key in the Shortext dashboard. On v2.0 its permissions decide what it can call:

| Permission             | Routes                                                                   |
|------------------------|--------------------------------------------------------------------------|
| `view_messages`        | read conversations and their messages                                    |
| `send_messages`        | start, reply, hand over, release, assign conversations; read templates  |
| `manage_integrations`  | agents, channels, Shortext endpoints, webhooks, hosted sessions         |
| `manage_dynamic_links` | dynamic links                                                            |

### Any PHP project

```php
use NyCorp\Shortext\Shortext;

$shortext = new Shortext('your-api-key');

// Options: another base URL (staging...), Guzzle timeouts and headers
$shortext = new Shortext(
    apiKey: 'your-api-key',
    baseUrl: 'https://shortext.ny-corp.io/api',   // default
    options: ['timeout' => 30, 'connect_timeout' => 10],
);
```

You can also pass your own `GuzzleHttp\ClientInterface` as fourth argument.

### Laravel

The service provider and the `Shortext` facade are auto-discovered. Set your `.env`:

```dotenv
SHORTEXT_API_KEY=your-api-key
# Optional
SHORTEXT_BASE_URL=https://shortext.ny-corp.io/api
SHORTEXT_TIMEOUT=30
SHORTEXT_WEBHOOK_SECRET=whsec_...
SHORTEXT_WEBHOOK_TOLERANCE=300
```

To publish `config/shortext.php`:

```bash
php artisan vendor:publish --tag=shortext-config
```

Inject `NyCorp\Shortext\Shortext` or use the facade:

```php
use NyCorp\Shortext\Facades\Shortext;

$conversation = Shortext::conversations()->get($id);
```

## Responses and errors

The Shortext API always answers HTTP 200 with an envelope `{ success, code, message, body }`.
The SDK turns it into results and exceptions:

- items (an agent, a conversation...) are `NyCorp\Shortext\Resource` objects: read-only, reachable as properties
  (`$agent->name`), array keys (`$agent['name']`) or with dot notation (`$conversation->get('session.assignee.id')`),
  `toArray()` for the raw data. Fields added to the API later are available without upgrading the SDK;
- paginated lists (50 items, most recent first) are `NyCorp\Shortext\Page` objects: iterate them, `count()`,
  `currentPage()`, `lastPage()`, `total()`, `hasMorePages()`, `nextPage()`, or `all()` to walk every page lazily;
- short lists (channels, webhook endpoints, assignees, event catalog) are arrays of `Resource`;
- a failure (`success: false`) throws an exception carrying the response:

| Exception                                                    | When                                                        |
|--------------------------------------------------------------|-------------------------------------------------------------|
| `NyCorp\Shortext\Exceptions\ValidationException`             | invalid input (code 1002), `errors()` per field              |
| `NyCorp\Shortext\Exceptions\NotFoundException`               | unknown resource or not yours (1007)                        |
| `NyCorp\Shortext\Exceptions\ConflictException`               | duplicate (1004)                                             |
| `NyCorp\Shortext\Exceptions\NotAuthorizedException`          | the key lacks the permission of the route (1005)            |
| `NyCorp\Shortext\Exceptions\AuthenticationException`         | missing, unknown, revoked or expired key (1 to 5); extends `NotAuthorizedException` |
| `NyCorp\Shortext\Exceptions\ApiVersionSunsetException`       | the API version is no longer served (HTTP 410, code 1200)   |
| `NyCorp\Shortext\Exceptions\ApiException`                    | any other failure; parent of the exceptions above           |
| `NyCorp\Shortext\Exceptions\TransportException`              | network error, timeout, response without a Shortext envelope |

All of them implement `NyCorp\Shortext\Exceptions\ShortextException`.

```php
use NyCorp\Shortext\Exceptions\ApiException;
use NyCorp\Shortext\Exceptions\ValidationException;

try {
    $shortext->agents()->create(['name' => '']);
} catch (ValidationException $e) {
    $e->errors();          // ['name' => ['The name field is required.']]
} catch (ApiException $e) {
    $e->getCode();         // envelope code, see NyCorp\Shortext\ResponseCode
    $e->getMessage();
    $e->traceId();         // give it to the Shortext support
    $e->response();        // NyCorp\Shortext\Http\Response
}
```

### Deprecation of an API version

When Shortext announces the end of a version, its responses carry `Deprecation`, `Sunset` and
`Link: <changelog>; rel="deprecation"` headers; once the sunset date is reached the version answers HTTP 410.
They are available on the raw response:

```php
$response = $shortext->http()->request('GET', '/v2.0/me');

$response->apiVersion();       // "v2.0" (X-Shortext-Api-Version)
$response->isDeprecated();
$response->deprecatedAt();     // ?DateTimeImmutable
$response->sunsetAt();         // ?DateTimeImmutable
$response->deprecationLink();  // changelog URL
```

`http()->request($method, $path, $query, $json)` also reaches routes the SDK does not cover yet; it returns the
response without throwing on `success: false` (call `->throw()` for that).

## Messages and contacts

One message grammar is shared by every endpoint: `{ type, content }`. Build it with `NyCorp\Shortext\Messages\Message`:

```php
use NyCorp\Shortext\Messages\Message;

Message::text('Hello, how can I help?');

// WhatsApp template (GET /templates gives the variables): the only message allowed outside the 24h window
Message::template('order_update', 'fr')
    ->body(['name' => 'Awa', 'order' => 'A-42'])      // "Hello {{name}}, your order {{order}}..."
    ->header(['shop' => 'Acme']);                     // variables of a TEXT header

Message::template('receipt', 'en')->headerMedia('https://cdn.example.com/receipt.pdf'); // IMAGE/VIDEO/DOCUMENT header

// Buttons that need a value, by index (GET /templates: buttons[].needs_value): end of a dynamic link, coupon code
Message::template('autumn_sale', 'fr')->body(['name' => 'Awa'])->buttonValues([1 => 'oct', 2 => 'SAVE20']);

// Authentication template: only the code (put in the message and in the code button)
Message::template('login_code', 'fr')->code('482913');

// Media
Message::image('https://cdn.example.com/a.png', caption: 'Your receipt');
Message::video('https://cdn.example.com/a.mp4');
Message::audio('https://cdn.example.com/a.ogg');
Message::document('https://cdn.example.com/a.pdf', filename: 'invoice.pdf');
Message::media('image', $url, caption: null, filename: null, mime: 'image/png');

// Interactive
Message::buttons('Confirm the order?')
    ->button('yes', 'Yes')
    ->button('no', 'No')                              // at most 3
    ->imageHeader('https://cdn.example.com/a.png')    // or header('text'), videoHeader(), documentHeader()
    ->footer('Acme');

Message::list('Pick a slot', 'Slots')
    ->section('Morning', [Message::row('9', '9:00'), Message::row('10', '10:00', 'Last one')]);

Message::ctaUrl('Track your order', 'https://acme.com/track/A-42', 'Track');
Message::payment('Order A-42', amount: 1500, currency: 'XAF', orderId: 'A-42');
Message::interactive('flow', 'Book now', $action);   // any type with its raw action

// Raw grammar, e.g. a payload stored by your application
Message::raw('text', ['text' => 'Hello']);
```

Every method that takes a `Message` also accepts the raw `['type' => ..., 'content' => [...]]` array.

Contacts use `NyCorp\Shortext\Messages\Contact`. The identifier is a phone in E.164 without `+` (WhatsApp), an email
(mail) or your own user id (Shortext endpoints). A plain string or a `['identifier' => ..., 'profile' => [...]]`
array is accepted too.

```php
use NyCorp\Shortext\Messages\Contact;

Contact::make('237699999999')
    ->firstname('Awa')
    ->lastname('Ngo')
    ->email('awa@example.com')
    ->preferredLanguage('fr');   // en, fr, es, de
```

## Resources

### Me

```php
$me = $shortext->me();

$me->get('company.name');
$me->get('token.abilities');   // ['send_messages', ...]
$me->api_version;              // "v2.0"
```

### Agents

`manage_integrations`

```php
$page = $shortext->agents()->list(['name' => 'Support'], page: 1);

foreach ($page as $agent) {
    echo $agent->id, ' ', $agent->name;
}

$agent = $shortext->agents()->create([
    'name' => 'Support',
    'use_ai' => true,
    'instructions' => 'You are the support agent of Acme...',
]);

$shortext->agents()->get($agent->id);
$shortext->agents()->update($agent->id, ['active' => false, 'inactive_message' => 'Back tomorrow at 8 am']);
$shortext->agents()->delete($agent->id);    // soft delete, returns the deleted agent
$shortext->agents()->restore($agent->id);
```

### Channels

`manage_integrations`. A channel is a WhatsApp number, a mailbox or a Shortext endpoint; `$type` is `whatsapp`,
`mail` or `shortext` and `$id` is the `channel_id` used by the webhooks.

```php
$channels = $shortext->channels()->list(['type' => 'whatsapp']);   // or ['agent_id' => 'none'] for detached ones

$channel = $shortext->channels()->get('whatsapp', '532867649916076');

$shortext->channels()->attachAgent('whatsapp', '532867649916076', 'AGI-XXXXX');  // attach or move
$shortext->channels()->detachAgent('whatsapp', '532867649916076');               // messages stored, nobody answers
```

To connect a new WhatsApp number or mailbox, use a [hosted session](#hosted-sessions).

### Shortext endpoints

`manage_integrations`. A Shortext endpoint plugs an agent into your own app: your server posts the messages of your
users ([inbound](#inbound-messages-shortext-endpoints)) and receives the replies.

```php
$endpoint = $shortext->shortextEndpoints()->create([
    'name' => 'Mobile app',
    'agent_id' => 'AGI-XXXXX',
    'reply_mode' => 'async',                          // async: replies pushed to webhook_url; sync: in the inbound response
    'webhook_url' => 'https://api.acme.com/shortext/replies',
]);

$endpoint->id;   // ste_...

$shortext->shortextEndpoints()->update($endpoint->id, ['reply_mode' => 'sync', 'sync_timeout' => 20]);
$secret = $shortext->shortextEndpoints()->rotateSecret($endpoint->id)->webhook_secret;   // signs the replies pushed to webhook_url (needs one)
$shortext->shortextEndpoints()->delete($endpoint->id);
```

### Conversations

Reading needs `view_messages`; acting needs `send_messages`.

```php
$page = $shortext->conversations()->list([
    'status' => 'active',              // active or archived
    'channel' => 'whatsapp',
    'channel_id' => '532867649916076',
    'agent_id' => 'AGI-XXXXX',         // "none" for detached channels
    'contact' => '237699999999',
    'assignee' => 'none',              // "none" or a member id
    'ticket_id' => 'TCK-1234',         // the conversation of a ticket
    'updated_since' => new DateTimeImmutable('-1 day'),
]);

$conversation = $shortext->conversations()->get($id);
$conversation->get('session.handover');

$messages = $shortext->conversations()->messages($id);   // most recent first
foreach ($messages->all() as $message) {                  // every page
    echo $message->get('message.content.text');
}
```

Start a conversation (on WhatsApp, outside the 24h window opened by the contact, only an approved template can be sent):

```php
$result = $shortext->conversations()->start(
    channel: 'whatsapp',
    channelId: '532867649916076',
    contact: Contact::make('237699999999')->lastname('Awa'),
    message: Message::template('order_update', 'fr')->body(['name' => 'Awa', 'order' => 'A-42']),
    handover: false,                   // true: a human follows, the AI agent does not answer
);

$result->get('conversation.id');
$result->get('message.id');
```

Reply. By default the reply takes the conversation over (the AI agent stops answering); send `handover: false` for a
notification that lets the agent continue:

```php
$shortext->conversations()->reply($id, Message::text('A human is taking over, one moment.'));
$shortext->conversations()->reply($id, Message::text('Your order has shipped'), handover: false);
```

Hand over, release and assign:

```php
$shortext->conversations()->handover($id);          // the AI agent stops answering
$shortext->conversations()->release($id);           // the AI agent answers again

$members = $shortext->conversations()->assignees();
$shortext->conversations()->assign($id, $members[0]->id);
$shortext->conversations()->unassign($id);
```

### Templates

`send_messages`

```php
$templates = $shortext->templates()->list([
    'status' => 'APPROVED',
    'channel_id' => '532867649916076',   // templates this number can send
    'language' => 'fr',
]);

$template = $shortext->templates()->get($id);
$template->get('body.variables');        // ['name', 'order'] → Message::template(...)->body([...])
```

### Webhook endpoints

`manage_integrations`. Payloads follow the API version used to create the endpoint.

```php
$shortext->webhooks()->events();   // catalog: [{type, description}]

$endpoint = $shortext->webhooks()->create(
    url: 'https://api.acme.com/shortext/webhooks',
    events: ['message.*', 'conversation.handover'],   // exact types, prefix wildcards or "*"
    filters: ['app' => 'AGI-XXXXX'],                  // optional: one agent, channel or channel_id
    description: 'CRM sync',
);
$endpoint->secret;   // returned only once: store it

$shortext->webhooks()->list();
$shortext->webhooks()->get($endpoint->id);
$shortext->webhooks()->update($endpoint->id, ['is_enabled' => true]);   // re-enables an endpoint disabled after failures
$newSecret = $shortext->webhooks()->rotateSecret($endpoint->id);        // the previous one stops working at once
$shortext->webhooks()->test($endpoint->id);                             // sends webhook.test now, returns the delivery

$deliveries = $shortext->webhooks()->deliveries($endpoint->id, status: 'failed');
$shortext->webhooks()->retry($endpoint->id, $deliveries->first()->id);  // same event id
$shortext->webhooks()->delete($endpoint->id);
```

### Hosted sessions

`manage_integrations`. A hosted session is a signed, expiring link to a Shortext page (connect a WhatsApp number,
a mailbox...) that you give to your own user.

```php
$shortext->hostedSessions()->purposes();   // ['whatsapp' => 'Connect WhatsApp Business numbers...', 'mail' => ...]

$session = $shortext->hostedSessions()->create(
    purpose: 'whatsapp',
    context: ['agent_id' => 'AGI-XXXXX'],
    returnUrl: 'https://app.acme.com/integrations/done',   // receives ?session_id=...&status=...
    locale: 'fr',
    expiresIn: 60,                                          // minutes, 5 to 1440
);

return redirect($session->url);   // holds a secret: never log or share it

$shortext->hostedSessions()->get($session->id)->status;   // pending, opened, completed, failed, cancelled, expired
$shortext->hostedSessions()->list();
$shortext->hostedSessions()->cancel($session->id);
```

The outcome is also sent as `hosted_session.completed` / `hosted_session.failed` webhooks.

### Inbound messages (Shortext endpoints)

Your server posts the messages of your users to a Shortext endpoint (`POST /v1.0/messages/inbound`):

```php
use NyCorp\Shortext\Api\Inbound;

$result = $shortext->inbound()->send(
    endpointId: 'ste_8k2m4q9x1v7c3n5b6z0p2r4t',
    contact: Contact::make('user-42')->firstname('Awa'),   // your own user id, unique in your company
    messageId: 'client-msg-001',                            // your own id: sending it twice is idempotent
    text: 'Where is my order?',
    metadata: ['page' => '/checkout'],
    replyMode: Inbound::SYNC,                               // defaults to the endpoint setting
);

match ($result->status) {
    Inbound::STATUS_COMPLETED => $result->replies,   // sync: the agent replies
    Inbound::STATUS_PENDING,                         // sync timed out: replies will come to your webhook_url
    Inbound::STATUS_ACCEPTED,                        // async: replies will come to your webhook_url
    Inbound::STATUS_NO_AUTO_REPLY,                   // stored, no automatic reply (handover, no agent...)
    Inbound::STATUS_DUPLICATE => null,               // message id already received
};
```

Replies pushed to `webhook_url` are signed `message.sent` events: verify them as [below](#receiving-webhooks), with the
endpoint secret.

### API v1.0

v1.0 keeps working for existing integrations (it does not check the key permissions):

```php
// POST /messages/send
$shortext->v1()->sendMessage(
    to: Contact::make('237699999999')->lastname('Awa'),
    from: '532867649916076',          // your channel: WhatsApp phone id, mailbox...
    message: Message::text('Hello'),
    channel: 'whatsapp',
);

// POST /deep-link/add
$shortext->v1()->createDynamicLink([
    'fallback_url' => 'https://acme.com',
    'deep_link' => 'acme://orders/42',
    'ttl' => 3600,
]);

// A 1.x builder (MessageBuilder, MessageButtonReply...)
$shortext->v1()->sendPayload($builder);
```

## Receiving webhooks

Shortext signs every webhook (webhook endpoints and Shortext endpoint replies):

```
X-Shortext-Signature: sha256=hex(hmac_sha256(secret, "{X-Shortext-Timestamp}.{raw body}"))
```

`WebhookVerifier` checks the signature in constant time and rejects timestamps more than 5 minutes away
(replay protection), then decodes the body into a typed event. Always verify the **raw** body.

```php
use NyCorp\Shortext\Exceptions\InvalidSignatureException;
use NyCorp\Shortext\Webhooks\WebhookVerifier;
use NyCorp\Shortext\Webhooks\Events\MessageReceived;

$verifier = new WebhookVerifier(secret: 'whsec_...', tolerance: 300);

try {
    $event = $verifier->event(file_get_contents('php://input'), getallheaders());
} catch (InvalidSignatureException $e) {
    http_response_code(400);
    exit;
}

if ($event instanceof MessageReceived) {
    $event->conversationId();
    $event->contactIdentifier();
    $event->text();
}

http_response_code(200);   // within 10 s: process heavy work asynchronously
```

`$verifier->verify($rawBody, $signature, $timestamp)` (throws) and `isValid()` (bool) check without decoding.

In Laravel, the facade uses `config('shortext.webhooks.secret')`. Exclude the route from CSRF protection:

```php
use Illuminate\Http\Request;
use NyCorp\Shortext\Exceptions\InvalidSignatureException;
use NyCorp\Shortext\Facades\Shortext;
use NyCorp\Shortext\Webhooks\Events;

Route::post('/shortext/webhooks', function (Request $request) {
    try {
        $event = Shortext::webhookEvent($request);
    } catch (InvalidSignatureException) {
        abort(400);
    }

    match (true) {
        $event instanceof Events\MessageReceived => ProcessMessage::dispatch($event->toArray()),
        $event instanceof Events\ConversationHandover => /* ... */ null,
        default => null,
    };

    return response()->noContent();
});
```

Delivery order is not guaranteed and an event may be delivered twice: dedupe on `$event->id()`.

| Type                       | Class                    | Accessors                                                         |
|----------------------------|--------------------------|-------------------------------------------------------------------|
| `message.received`         | `MessageReceived`        | `messageId()`, `conversationId()`, `channel()`, `channelId()`, `direction()`, `contactIdentifier()`, `messageType()`, `content()`, `text()`, `status()` |
| `message.sent`             | `MessageSent`            | same                                                              |
| `message.status`           | `MessageStatusUpdated`   | same                                                              |
| `conversation.handover`    | `ConversationHandover`   | `conversationId()`, `channel()`, `channelId()`, `contactIdentifier()` |
| `conversation.released`    | `ConversationReleased`   | same                                                              |
| `channel.connected`        | `ChannelConnected`       | `channelType()`, `channelId()`, `agentId()`                       |
| `channel.agent_changed`    | `ChannelAgentChanged`    | same, `previousAgentId()`                                         |
| `agent.created`            | `AgentCreated`           | `agentId()`                                                       |
| `agent.updated`            | `AgentUpdated`           | `agentId()`                                                       |
| `agent.deleted`            | `AgentDeleted`           | `agentId()`                                                       |
| `hosted_session.completed` | `HostedSessionCompleted` | `sessionId()`, `purpose()`, `context()`, `result()`               |
| `hosted_session.failed`    | `HostedSessionFailed`    | `sessionId()`, `purpose()`, `context()`, `error()`                |
| `webhook.test`             | `WebhookTest`            |                                                                   |
| any other                  | `UnknownEvent`           |                                                                   |

Classes are in `NyCorp\Shortext\Webhooks\Events`. Every event has `id()`, `type()`, `apiVersion()`, `createdAt()`,
`data()` (a `Resource`) and `toArray()`.

## Testing your integration

Pass a Guzzle handler to answer without calling Shortext:

```php
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

$mock = new MockHandler([
    new Response(200, [], json_encode(['success' => true, 'code' => 1000, 'message' => 'Request success.', 'body' => ['id' => 'AGI-1']])),
]);

$shortext = new Shortext('test-key', options: ['handler' => $mock]);
```

In Laravel, bind that instance: `$this->app->instance(\NyCorp\Shortext\Shortext::class, $shortext);`.

To test your webhook route, sign the body with `WebhookVerifier::sign($secret, $timestamp, $body)`.

## Upgrading from 1.x

2.0 is a major version: PHP 8.2+, `nycorp/lite-api` is no longer required, and the 1.x classes are deprecated
but still work.

1. `composer require nycorp/shortext-php:^2.0`
2. **Return type.** `ShortextClient::sendMessage()` and `createDynamicLink()` now return `NyCorp\Shortext\Http\Response`
   instead of `Nycorp\LiteApi\Response\DefResponse`. It keeps `isSuccess()`, `getCode()`, `getMessage()`,
   `getBody()`, `getData()` and `isCodeSame()`, and still never throws. Only code that type-hints `DefResponse` or
   calls `getResponse()` must change.
3. **Namespace.** The classes are declared under `NyCorp\Shortext\...`. The 1.x spelling `Nycorp\Shortext\...` still
   resolves; prefer `NyCorp`.
4. **Fixed payloads.** `MessageBuilder::ctaUrl()` and `interactiveList()` now send their `header` as
   `{type: "text", value}`; in 1.x the header text was dropped by the API.
5. **Removed.** The empty `NyCorp\Shortext\Shortext` class is now the v2 client; the placeholder `shortext-php`
   command, migration and `config/shortext-php.php` are gone (the config is `config/shortext.php`).
6. **FlowCrypto** needs `composer require phpseclib/phpseclib:^4.0` (it was an undeclared dependency).

Then move to the new client at your own pace:

```php
// 1.x
$sdk = new ShortextClient(env('SHORTEXT_API_KEY'));
$builder = new MessageBuilder($phone, from: '<PHONE_ID>', lastname: $lastname);
$response = $sdk->sendMessage($builder->text('Hello'));
if (! $response->isSuccess()) { /* ... */ }

// 2.0, same v1.0 route
$shortext = new Shortext(env('SHORTEXT_API_KEY'));
$shortext->v1()->sendMessage(Contact::make($phone)->lastname($lastname), '<PHONE_ID>', Message::text('Hello')); // throws on failure

// 2.0, API v2.0: the conversation, its handover and its webhooks
$shortext->conversations()->start('whatsapp', '<PHONE_ID>', Contact::make($phone), Message::template('welcome', 'fr'));
```

| 1.x `MessageBuilder`                         | 2.0 `Message`                                         |
|----------------------------------------------|-------------------------------------------------------|
| `->text($text)`                              | `Message::text($text)`                                |
| `->media($url, 'image', caption: $c)`        | `Message::image($url, $c)` / `Message::media(...)`    |
| `->template($name, $lang, $body, $header)`   | `Message::template($name, $lang)->body([...])->header([...])` |
| `->ctaUrl($body, $url, $caption)`            | `Message::ctaUrl($body, $url, $caption)`              |
| `->payment($description, $amount, $currency)`| `Message::payment($description, $amount, $currency)`  |
| `->interactiveList($body, $cta, $sections)`  | `Message::list($body, $cta)->section(...)`            |
| `new MessageButtonReply(...)->addButton()`   | `Message::buttons($body)->button($id, $title)`        |

## Development

```bash
composer test       # Pest, HTTP faked: no call to any server
composer analyse    # PHPStan
composer format     # Pint
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Yvan Ngalle](https://github.com/nycorp)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
