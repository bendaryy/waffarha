<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * A paginated collection of {@see Payout} objects.
 *
 * Iterable and countable so callers can write:
 *
 *     $payouts = Waffarha::payouts()->list(['status' => 'proof_submitted']);
 *     foreach ($payouts as $payout) {
 *         echo $payout->id;
 *     }
 *     $total = $payouts->meta?->total;
 *
 * The list envelope mirrors the other Waffarha list endpoints — rows under
 * `payouts` and the standard `pagination` block. Rows are still resolved
 * defensively (`payouts` → `data` → bare list) to tolerate future variants.
 *
 * @implements IteratorAggregate<int, Payout>
 *
 * @phpstan-type ListPayload array{payouts?: list<array<string, mixed>>, data?: list<array<string, mixed>>, pagination?: array<string, mixed>}
 */
final readonly class PayoutCollection implements Countable, IteratorAggregate
{
    /**
     * @param  list<Payout>  $items
     */
    public function __construct(
        public array $items,
        public ?PaginationMeta $meta = null,
    ) {}

    /**
     * Build a collection from a decoded payouts response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rows = match (true) {
            isset($data['payouts']) && is_array($data['payouts']) => $data['payouts'],
            isset($data['data']) && is_array($data['data']) => $data['data'],
            $data !== [] && array_is_list($data) => $data,
            default => [],
        };

        $items = [];
        foreach (array_values($rows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $items[] = Payout::fromArray($row);
            }
        }

        $meta = isset($data['pagination']) && is_array($data['pagination'])
            ? PaginationMeta::fromArray($data['pagination'])
            : null;

        return new self($items, $meta);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, Payout>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(static fn (Payout $payout): array => $payout->toArray(), $this->items);
    }
}
