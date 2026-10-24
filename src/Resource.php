<?php

namespace NyCorp\Shortext;

use ArrayAccess;
use JsonSerializable;
use LogicException;
use NyCorp\Shortext\Support\Data;

/**
 * Read-only object returned by the API (an agent, a conversation, a webhook endpoint...).
 *
 * Fields are reachable as properties ($agent->name), array keys ($agent['name'])
 * or with dot notation ($conversation->get('session.assignee.id')). Nested objects stay arrays.
 * Fields added to the API later are available without upgrading the SDK.
 *
 * @implements ArrayAccess<string, mixed>
 */
class Resource implements ArrayAccess, JsonSerializable
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    final public function __construct(protected array $attributes = []) {}

    /**
     * @param  array<string, mixed>|null  $attributes
     */
    public static function from(?array $attributes): static
    {
        return new static($attributes ?? []);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Data::get($this->attributes, $key, $default);
    }

    public function has(string $key): bool
    {
        return Data::get($this->attributes, $key, $this) !== $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): array
    {
        return $this->attributes;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new LogicException('Shortext resources are read-only');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('Shortext resources are read-only');
    }
}
