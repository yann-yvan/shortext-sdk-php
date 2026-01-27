<?php

namespace NyCorp\Shortext\Sdk\Builder;

use JetBrains\PhpStorm\ArrayShape;

class MessageBuilder implements PayloadBuilder
{
    protected array $contact;

    protected string $channel = 'whatsapp';

    protected string $from;

    protected array $message;

    public function __construct($to, $from, $lastname = '')
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

    public function payment(string $description, float $amount, string $currency, ?string $order_id = null, ?string $callback_url = null): static
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

    /**
     * @return $this
     */
    public function interactive(string $bodyText, ?string $footer = null, array $header = [], array $payment_detail = [], array $cta = [], string $type = 'button'): static
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
            ],
        ];

        return $this;
    }

    public function ctaUrl(string $bodyText, string $url, string $urlCaption, ?string $footer = null, ?string $header = null): static
    {
        return $this->interactive(
            bodyText: $bodyText,
            footer: $footer,
            header: $header ? [
                'text' => $header,
            ] : [],
            cta: [
                'display_text' => $urlCaption,
                'url' => $url,
            ],
            type: 'cta_url'
        );
    }

    /**
     * @return $this
     */
    public function template(string $templateName, string $language, array $body, array $header = [], array $buttons = []): static
    {
        $this->message = [
            'type' => 'template',
            'content' => [
                'template_name' => $templateName,
                'language' => $language,
                'header' => $header,
                'body' => $body,
                'buttons' => $buttons,
            ],
        ];

        return $this;
    }

    #[ArrayShape(['contact' => 'array', 'channel' => 'string', 'from' => 'string', 'message' => 'array'])]
    public function getPayload(): array
    {
        return [
            'contact' => $this->contact,
            'channel' => $this->channel,
            'from' => $this->from,
            'message' => $this->message,
        ];
    }

    #[ArrayShape(['bodyText' => 'string', 'ctaTitle' => 'string', 'choices' => 'section[]', 'header' => 'string', 'footer' => 'string'])]
    public function satisfaction(string $bodyText, string $ctaTitle, array $choices, ?string $header = null, string $footer = 'Powered by https://shortext.ny-corp.io – Smart automation.'): self
    {
        return $this->interactiveList(
            bodyText: $bodyText,
            ctaTitle: $ctaTitle,
            sections: [
                $this->newListSection(
                    title: 'Note',
                    rows: $choices
                ),
            ],
            header: $header,
            footer: $footer
        );
    }

    #[ArrayShape(['bodyText' => 'ctaTitle', 'title' => 'string', 'section' => 'section[]', 'header' => 'string', 'footer' => 'string'])]
    public function interactiveList(string $bodyText, string $ctaTitle, array $sections, ?string $header = null, ?string $footer = null): self
    {
        return $this->interactive(
            bodyText: $bodyText,
            footer: $footer,
            header: $header ? [
                'type' => 'text',
                'text' => $header,
            ] : [],
            cta: [
                'button' => $ctaTitle,
                'sections' => $sections,
            ],
            type: 'list'
        );
    }

    /**
     * @return array{title: string, rows: array}
     */
    #[ArrayShape(['title' => 'string', 'rows' => 'array'])]
    public function newListSection(string $title, array $rows): array
    {
        return [
            'title' => $title,
            'rows' => $rows,
        ];
    }

    /**
     * @return array{id: string, title: string, description: null|string}
     */
    #[ArrayShape(['id' => 'string', 'title' => 'string', 'description' => 'null|string'])]
    public function newListRow(string $id, string $title, ?string $description = null): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'description' => $description,
        ];
    }
}
