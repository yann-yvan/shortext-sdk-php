<?php

namespace NyCorp\Shortext\Messages;

/**
 * A WhatsApp template. The variables come from GET /templates (body.variables, header.variables).
 */
class TemplateMessage extends Message
{
    /** @var array<string, string> */
    protected array $body = [];

    /** @var array<string, string> */
    protected array $header = [];

    /** @var array<mixed> */
    protected array $buttons = [];

    protected ?string $category = null;

    protected ?string $code = null;

    public function __construct(protected string $name, protected string $language)
    {
        parent::__construct(self::TEMPLATE);
    }

    /**
     * Body variables: ['name' => 'Awa', 'order' => 'A-42'] for "Hello {{name}}, your order {{order}}..."
     *
     * @param  array<string, string|int|float>  $variables
     */
    public function body(array $variables): static
    {
        $this->body = array_map('strval', $variables);

        return $this;
    }

    /**
     * Variables of a TEXT header
     *
     * @param  array<string, string|int|float>  $variables
     */
    public function header(array $variables): static
    {
        $this->header = array_map('strval', $variables);

        return $this;
    }

    /** Media of an IMAGE, VIDEO or DOCUMENT header (the template's own media is sent otherwise) */
    public function headerMedia(string $url): static
    {
        $this->header = ['url' => $url];

        return $this;
    }

    /**
     * Raw Meta parameters of the template buttons; buttonValues() is the short form
     *
     * @param  array<mixed>  $buttons
     */
    public function buttonParameters(array $buttons): static
    {
        $this->buttons = $buttons;

        return $this;
    }

    /**
     * Value of the buttons that need one, by index (GET /templates: buttons[].needs_value):
     * the end of a dynamic link, the coupon of a copy code button, a quick reply payload.
     * [1 => 'A-42', 2 => 'SAVE20']
     *
     * @param  array<int, string|int>  $values
     */
    public function buttonValues(array $values): static
    {
        $this->buttons = array_map('strval', $values);

        return $this;
    }

    /** The code of an authentication template: Shortext puts it in the message and in its code button */
    public function code(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /** UTILITY (default), MARKETING or AUTHENTICATION */
    public function category(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    protected function content(): array
    {
        return array_filter([
            'template_name' => $this->name,
            'language' => $this->language,
            'body' => $this->body,
            'header' => $this->header,
            'buttons' => $this->buttons,
            'category' => $this->category,
            'code' => $this->code,
        ], static fn ($value) => $value !== null && $value !== []);
    }
}
