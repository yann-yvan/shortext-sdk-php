<?php

namespace NyCorp\Shortext;

use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * One page of a list (50 items, most recent first).
 *
 * @template T of Resource
 *
 * @implements IteratorAggregate<int, T>
 */
class Page implements Countable, IteratorAggregate
{
    /**
     * @param  list<T>  $items
     * @param  (Closure(int): Page<T>)|null  $fetch  loads another page
     */
    public function __construct(
        protected array $items,
        protected int $currentPage = 1,
        protected int $lastPage = 1,
        protected ?int $perPage = null,
        protected ?int $total = null,
        protected ?Closure $fetch = null,
    ) {}

    /**
     * Builds a page from a Laravel paginator body (current_page, data, last_page, per_page, total)
     *
     * @template R of Resource
     *
     * @param  class-string<R>  $class
     * @param  (Closure(int): Page<R>)|null  $fetch
     * @return Page<R>
     */
    public static function fromBody(mixed $body, string $class = Resource::class, ?Closure $fetch = null): self
    {
        $body = is_array($body) ? $body : [];

        // A plain list (not paginated)
        $rows = array_is_list($body) ? $body : ($body['data'] ?? []);

        $items = array_map(static fn ($row) => $class::from(is_array($row) ? $row : []), array_values((array) $rows));

        return new self(
            $items,
            (int) ($body['current_page'] ?? 1),
            (int) ($body['last_page'] ?? 1),
            isset($body['per_page']) ? (int) $body['per_page'] : null,
            isset($body['total']) ? (int) $body['total'] : (array_is_list($body) ? count($items) : null),
            $fetch,
        );
    }

    /** @return list<T> */
    public function items(): array
    {
        return $this->items;
    }

    public function first(): ?Resource
    {
        return $this->items[0] ?? null;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function lastPage(): int
    {
        return $this->lastPage;
    }

    public function perPage(): ?int
    {
        return $this->perPage;
    }

    /** Items on every page */
    public function total(): ?int
    {
        return $this->total;
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    /** @return Page<T>|null */
    public function nextPage(): ?self
    {
        if (! $this->hasMorePages() || $this->fetch === null) {
            return null;
        }

        return ($this->fetch)($this->currentPage + 1);
    }

    /**
     * Every item of this page and the following ones, loaded lazily
     *
     * @return \Generator<int, T>
     */
    public function all(): \Generator
    {
        $page = $this;

        while ($page !== null) {
            foreach ($page->items() as $item) {
                yield $item;
            }
            $page = $page->nextPage();
        }
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
