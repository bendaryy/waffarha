<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A single line of a {@see CancellationPolicy}.
 *
 * @phpstan-type PolicyDescriptionPayload array{id?: int|null, description?: string|null}
 */
final readonly class PolicyDescription
{
    public function __construct(
        public ?int $id,
        public ?string $description,
    ) {}

    /**
     * @param  PolicyDescriptionPayload  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) && is_scalar($data['id']) ? (int) $data['id'] : null,
            description: isset($data['description']) && is_scalar($data['description']) ? (string) $data['description'] : null,
        );
    }
}
