<?php

namespace Nycorp\Shortext\Sdk\Builder;

class MessageBuilder implements PayloadBuilder
{
    protected array $contact;
    protected string $channel = 'whatsapp';
    protected string $from;
    protected array $message;

    public function __construct($to, $from, $lastname = '',)
    {
        $this->contact = [
            'phone' => $to,
            'profile' => ['lastname' => $lastname],
        ];
        $this->from = $from;
    }

    public function text($text): static
    {
        $this->message = [
            'type' => 'text',
            'content' => ['text' => trim($text)],
        ];
        return $this;
    }

    public function media(string $mediaUrl, string $mediaType, ?string $name = null, ?string $caption = null): static
    {
        $this->message = [
            'type' => 'media',
            'content' => [
                'media_url' => $mediaUrl,
                'caption' => $caption,
                'name' => $name,
                'media_type' => $mediaType,
            ],
        ];
        return $this;
    }

    public function payment(string $description, float $amount, string $currency, string $order_id = null, string $callback_url = null): static
    {
        return $this->interactive(
            bodyText: $description,
            payment_detail: [
                'amount' => $amount,
                'currency' => $currency,
                'order_id' => $order_id,
                'callback_url' => $callback_url,
            ],
            type: 'payment');
    }

    public function interactive(string $bodyText, ?string $footer = null, array $header = [], array $payment_detail = [], array $cta = [], array $buttons = [], string $type = 'button'): static
    {
        $this->message = [
            'type' => 'interactive',
            'content' => [
                'type' => $type,
                'body' => [
                    'text' => $bodyText,
                ],
                'header' => $header,
                'footer' => $footer,
                'action' => $cta,
                'payment_detail' => $payment_detail,
                'buttons' => $buttons,
            ]
        ];
        return $this;
    }

    public function ctaUrl(string $bodyText, string $url, string $urlCaption, ?string $footer = null, ?string $header = null): static
    {
        return $this->interactive(
            bodyText: $bodyText,
            footer: $footer,
            header: $header ? [
                'text' => $header
            ] : [],
            cta: [
                'display_text' => $urlCaption,
                'url' => $url,
            ],
            type: 'cta_url'
        );
    }

    public function template($templateName, $params = []): static
    {
        $this->message = [
            'type' => 'template',
            'content' => [
                'template_name' => $templateName,
                'params' => $params,
            ],
        ];
        return $this;
    }

    public function getPayload(): array
    {
        return [
            'contact' => $this->contact,
            'channel' => $this->channel,
            'from' => $this->from,
            'message' => $this->message,
        ];
    }
}
