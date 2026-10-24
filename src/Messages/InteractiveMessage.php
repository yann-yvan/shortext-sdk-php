<?php

namespace NyCorp\Shortext\Messages;

use LengthException;

/**
 * An interactive message: { type, body, action, header?, footer? }.
 * The header is { type: text|image|video|document, value, filename? }.
 */
class InteractiveMessage extends Message
{
    public const BUTTON = 'button';

    public const LIST = 'list';

    public const CTA_URL = 'cta_url';

    public const PAYMENT = 'payment';

    public const MAX_BUTTONS = 3;

    /** @var array<mixed> */
    protected array $action = [];

    /** @var array{type: string, value: string, filename?: string}|null */
    protected ?array $header = null;

    protected ?string $footer = null;

    public function __construct(protected string $interactiveType, protected string $body)
    {
        parent::__construct(self::INTERACTIVE);
    }

    /** A reply button (button messages, at most 3) */
    public function button(string $id, string $title): static
    {
        if (count($this->action) >= self::MAX_BUTTONS) {
            throw new LengthException('An interactive button message has at most '.self::MAX_BUTTONS.' buttons');
        }

        $this->action[] = ['id' => $id, 'title' => $title];

        return $this;
    }

    /**
     * A section of a list message
     *
     * @param  list<array{id: string, title: string, description?: string}>  $rows  see Message::row()
     */
    public function section(string $title, array $rows): static
    {
        $this->action['sections'][] = ['title' => $title, 'rows' => $rows];

        return $this;
    }

    /**
     * Replaces the action
     *
     * @param  array<mixed>  $action
     */
    public function action(array $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function header(string $text): static
    {
        return $this->setHeader('text', $text);
    }

    public function imageHeader(string $url): static
    {
        return $this->setHeader('image', $url);
    }

    public function videoHeader(string $url): static
    {
        return $this->setHeader('video', $url);
    }

    public function documentHeader(string $url, string $filename = 'document.pdf'): static
    {
        return $this->setHeader('document', $url, $filename);
    }

    public function footer(?string $footer): static
    {
        $this->footer = $footer;

        return $this;
    }

    protected function setHeader(string $type, string $value, ?string $filename = null): static
    {
        $this->header = ['type' => $type, 'value' => $value];

        if ($filename !== null) {
            $this->header['filename'] = $filename;
        }

        return $this;
    }

    protected function content(): array
    {
        return array_filter([
            'type' => $this->interactiveType,
            'body' => $this->body,
            'action' => $this->action,
            'header' => $this->header,
            'footer' => $this->footer,
        ], static fn ($value) => $value !== null);
    }
}
