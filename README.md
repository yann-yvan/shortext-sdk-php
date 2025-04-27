# shortext-php

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nycorp/shortext-php.svg?style=flat-square)](https://packagist.org/packages/nycorp/shortext-php)
[![Total Downloads](https://img.shields.io/packagist/dt/nycorp/shortext-php.svg?style=flat-square)](https://packagist.org/packages/nycorp/shortext-php)


---

**Shortext – Automate your WhatsApp conversations easily 🚀**

Shortext is a smart solution that helps you automate and streamline your customer interactions through **WhatsApp Business**. Whether it's answering clients, managing support tickets, or sending automated notifications, Shortext puts the power of **AI** at the service of your customer experience.

With Shortext, you can:  
✅ Build a **WhatsApp chatbot** in just a few clicks  
✅ Send **automated messages via WhatsApp Business**  
✅ Integrate the **WhatsApp API** seamlessly  
✅ Improve customer satisfaction with fast, personalized responses

Discover how Shortext can transform your WhatsApp management:  
👉 [https://shortext.ny-corp.io](https://shortext.ny-corp.io)

---

> Powered by [Shortext](https://shortext.ny-corp.io) – Smart automation for WhatsApp Business.

---

## Installation

You can install the package via composer:

```bash
composer require nycorp/shortext-php
```

## Usage

Send message:

```php
$sdk =  new ShortextClient(env('SHORTEXT_API_KEY'));
$builder = new MessageBuilder($phone, from: "<PHONE_ID>", lastname: $lastname);
$sdk->sendMessage($builder);
```

You can send text message with:

```php
$builder->text("hello this is text message");
```

You can send media message with:

```php
$builder->media("<YOUR FILE URL>", "document", "document.pdf", "Optional caption");
$builder->media("<YOUR FILE URL>", "audio");
$builder->media("<YOUR FILE URL>", "image", caption: "Optional caption");
$builder->media("<YOUR FILE URL>", "video", caption: "Optional caption");
```

You can send cta_url message with:

```php
$builder->ctaUrl(
    bodyText: "Hello this is body text",
    url: 'https://shortext.ny-corp.io',
    urlCaption: 'Voire les détails'
);
```

You can send payment message with:

```php
$builder->payment(
    desription: "The message to display with 200 characters max length",
    amount: 100,
    currency: "XAF",
    order_id: "<OPTIONAL ORDER ID>",
    callback_url: "<YOUR OPTIONAL CALLBACK URL>"
);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Yvan Ngalle](https://github.com/nycorp)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
