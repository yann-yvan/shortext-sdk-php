# This is my package shortext-php

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nycorp/shortext-php.svg?style=flat-square)](https://packagist.org/packages/nycorp/shortext-php)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/nycorp/shortext-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nycorp/shortext-php/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/nycorp/shortext-php/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/nycorp/shortext-php/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/nycorp/shortext-php.svg?style=flat-square)](https://packagist.org/packages/nycorp/shortext-php)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/shortext-php.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/shortext-php)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require nycorp/shortext-php
```


You can publish the config file with:

```bash
php artisan vendor:publish --tag="shortext-php-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="shortext-php-views"
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
    bodyText: "",
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
