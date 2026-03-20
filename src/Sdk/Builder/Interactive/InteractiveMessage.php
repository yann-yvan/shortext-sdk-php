<?php

namespace NyCorp\Shortext\Sdk\Builder\Interactive;

use NyCorp\Shortext\Sdk\Builder\MessageBuilder;

abstract class InteractiveMessage extends MessageBuilder
{
    private string $body;

    private ?string $footer = null;

    private array $payment_detail = [];

    private array $header = [];

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getPayload(): array
    {
        $this->message = [
            'type' => 'interactive',
            'content' => [
                'type' => $this->type(),
                'body' => [
                    'text' => $this->body,
                ],
                'header' => $this->header,
                'footer' => $this->footer,
                'items' => $this->items(),
                'meta' => $this->payment_detail,
            ],
        ];

        return parent::getPayload();
    }

    abstract public function type(): string;

    abstract public function items(): array;

    public function setFooter(?string $footer): self
    {
        $this->footer = $footer;

        return $this;
    }

    public function addTextHeader(string $text): self
    {
        return $this->addHeader('text', $text);
    }

    protected function addHeader(string $type, string $value): self
    {
        $this->header = [
            'type' => $type,
            $type === 'text' ? $type : 'url' => $value,
        ];

        return $this;
    }
}
