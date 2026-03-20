<?php

namespace NyCorp\Shortext\Sdk\Builder\Interactive;

class MessageButtonReply extends InteractiveMessage
{
    use MediaHeader;

    private array $buttons = [];

    public function type(): string
    {
        return 'button';
    }

    public function items(): array
    {
        return collect($this->buttons)->take(3)->all();
    }

    public function addButton(string $id, string $title): self
    {
        $this->buttons[] = ['id' => $id, 'title' => $title];

        return $this;
    }
}
