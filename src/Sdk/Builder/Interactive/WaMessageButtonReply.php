<?php

namespace NyCorp\Shortext\Sdk\Builder\Interactive;

class WaMessageButtonReply extends WaInteractiveMessage
{
    use MediaHeader;

    private array $buttons = [];

    public function type(): string
    {
        return 'button';
    }

    public function cta_actions(): array
    {
        return collect($this->buttons)->take(3)->all();
    }

    public function addButton(string $id, string $title): self
    {
        $this->buttons[] = ['id' => $id, 'title' => $title];

        return $this;
    }

    public function actionKey(): string
    {
        return 'buttons';
    }
}
