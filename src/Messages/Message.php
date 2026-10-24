<?php

namespace NyCorp\Shortext\Messages;

use JsonSerializable;

/**
 * A message in the grammar shared by every endpoint and version: { type, content }.
 *
 *   Message::text('Hello')
 *   Message::template('order_update', 'fr')->body(['name' => 'Awa', 'order' => 'A-42'])
 *   Message::image('https://example.com/a.png', caption: 'Your receipt')
 *   Message::buttons('Confirm the order?')->button('yes', 'Yes')->button('no', 'No')
 */
class Message implements JsonSerializable
{
    public const TEXT = 'text';

    public const TEMPLATE = 'template';

    public const INTERACTIVE = 'interactive';

    public const MEDIA = 'media';

    /**
     * @param  array<string, mixed>  $content
     */
    public function __construct(protected string $type, protected array $content = []) {}

    public static function text(string $text): self
    {
        return new self(self::TEXT, ['text' => trim($text)]);
    }

    /** A WhatsApp template (GET /templates): the only message allowed outside the 24h window */
    public static function template(string $name, string $language): TemplateMessage
    {
        return new TemplateMessage($name, $language);
    }

    /**
     * @param  string  $mediaType  image, video, document or audio
     */
    public static function media(string $mediaType, string $url, ?string $caption = null, ?string $filename = null, ?string $mime = null): self
    {
        return new self(self::MEDIA, array_filter([
            'media_type' => $mediaType,
            'media_url' => $url,
            'caption' => $caption,
            'filename' => $filename,
            'mime' => $mime,
        ], static fn ($value) => $value !== null));
    }

    public static function image(string $url, ?string $caption = null): self
    {
        return self::media('image', $url, $caption);
    }

    public static function video(string $url, ?string $caption = null): self
    {
        return self::media('video', $url, $caption);
    }

    public static function audio(string $url): self
    {
        return self::media('audio', $url);
    }

    public static function document(string $url, ?string $filename = null, ?string $caption = null): self
    {
        return self::media('document', $url, $caption, $filename);
    }

    /** Up to 3 reply buttons: ->button($id, $title) */
    public static function buttons(string $body): InteractiveMessage
    {
        return new InteractiveMessage(InteractiveMessage::BUTTON, $body);
    }

    /** A menu opened by $buttonText: ->section($title, [Message::row(...), ...]) */
    public static function list(string $body, string $buttonText): InteractiveMessage
    {
        return (new InteractiveMessage(InteractiveMessage::LIST, $body))->action(['button' => $buttonText, 'sections' => []]);
    }

    /** A row of a list section */
    public static function row(string $id, string $title, ?string $description = null): array
    {
        return array_filter(['id' => $id, 'title' => $title, 'description' => $description], static fn ($value) => $value !== null);
    }

    /** A button opening a URL */
    public static function ctaUrl(string $body, string $url, string $displayText): InteractiveMessage
    {
        return (new InteractiveMessage(InteractiveMessage::CTA_URL, $body))
            ->action(['parameters' => ['display_text' => $displayText, 'url' => $url]]);
    }

    /** A payment request (WhatsApp) */
    public static function payment(string $description, float $amount, string $currency, ?string $orderId = null, ?string $callbackUrl = null): InteractiveMessage
    {
        return (new InteractiveMessage(InteractiveMessage::PAYMENT, $description))
            ->action(['parameters' => [
                'amount' => $amount,
                'currency' => $currency,
                'order_id' => $orderId,
                'callback_url' => $callbackUrl,
            ]]);
    }

    /**
     * Any interactive message (button, list, cta_url, payment, quick_reply, carousel, flow) with its raw action
     *
     * @param  array<mixed>  $action
     */
    public static function interactive(string $type, string $body, array $action = []): InteractiveMessage
    {
        return (new InteractiveMessage($type, $body))->action($action);
    }

    /**
     * A message from its raw grammar, e.g. a payload stored by your application
     *
     * @param  array<string, mixed>  $content
     */
    public static function raw(string $type, array $content): self
    {
        return new self($type, $content);
    }

    public function type(): string
    {
        return $this->type;
    }

    /** @return array{type: string, content: array<string, mixed>} */
    public function toArray(): array
    {
        return ['type' => $this->type, 'content' => $this->content()];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /** @return array<string, mixed> */
    protected function content(): array
    {
        return $this->content;
    }
}
