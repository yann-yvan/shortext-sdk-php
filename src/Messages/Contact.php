<?php

namespace NyCorp\Shortext\Messages;

use JsonSerializable;

/**
 * The contact grammar: { identifier, profile? }.
 * identifier: phone in E.164 without "+" (WhatsApp), email (mail) or your own user id (Shortext).
 */
class Contact implements JsonSerializable
{
    /** @var array<string, string> */
    protected array $profile = [];

    final public function __construct(protected string $identifier) {}

    public static function make(string $identifier): static
    {
        return new static($identifier);
    }

    /**
     * @param  Contact|string|array<string, mixed>  $contact  { identifier, profile? }
     */
    public static function from(Contact|string|array $contact): self
    {
        if ($contact instanceof self) {
            return $contact;
        }

        if (is_string($contact)) {
            return new static($contact);
        }

        $instance = new static((string) ($contact['identifier'] ?? ''));
        foreach ((array) ($contact['profile'] ?? []) as $key => $value) {
            $instance->profile[(string) $key] = (string) $value;
        }

        return $instance;
    }

    public function firstname(?string $firstname): static
    {
        return $this->set('firstname', $firstname);
    }

    public function lastname(?string $lastname): static
    {
        return $this->set('lastname', $lastname);
    }

    public function email(?string $email): static
    {
        return $this->set('email', $email);
    }

    /** en, fr, es or de */
    public function preferredLanguage(?string $language): static
    {
        return $this->set('preferred_language', $language);
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    /** @return array{identifier: string, profile?: array<string, string>} */
    public function toArray(): array
    {
        $contact = ['identifier' => $this->identifier];

        if ($this->profile !== []) {
            $contact['profile'] = $this->profile;
        }

        return $contact;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    protected function set(string $key, ?string $value): static
    {
        if ($value === null || $value === '') {
            unset($this->profile[$key]);
        } else {
            $this->profile[$key] = $value;
        }

        return $this;
    }
}
