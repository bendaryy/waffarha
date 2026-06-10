<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Pagination metadata, matching the Maat units endpoint `pagination` block.
 * Every field is nullable so the DTO tolerates non-paginated or differently
 * shaped responses without throwing.
 *
 * @phpstan-type MetaPayload array{
 *     current_page?: int|null,
 *     last_page?: int|null,
 *     per_page?: int|null,
 *     total?: int|null,
 *     next_page_url?: string|null,
 *     prev_page_url?: string|null
 * }
 */
final readonly class PaginationMeta
{
    public function __construct(
        public ?int $currentPage = null,
        public ?int $lastPage = null,
        public ?int $perPage = null,
        public ?int $total = null,
        public ?string $nextPageUrl = null,
        public ?string $prevPageUrl = null,
    ) {}

    /**
     * @param  MetaPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $int = static fn (string $key): ?int => isset($data[$key]) && is_scalar($data[$key]) ? (int) $data[$key] : null;
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key]) ? (string) $data[$key] : null;

        return new self(
            currentPage: $int('current_page'),
            lastPage: $int('last_page'),
            perPage: $int('per_page'),
            total: $int('total'),
            nextPageUrl: $str('next_page_url'),
            prevPageUrl: $str('prev_page_url'),
        );
    }
}
