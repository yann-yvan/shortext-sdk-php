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
     * Parameters of the template buttons (e.g. the variable suffix of a URL button)
     *
     * @param  array<mixed>  $buttons
     */
    public function buttonParameters(array $buttons): static
    {
        $this->buttons = $buttons;

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
        ], static fn ($value) => $value !== null && $value !== []);
    }
}
