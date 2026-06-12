<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Compact property snapshot returned inside the `property` block of a
 * `POST /waffarha/unit/{uuid}/check` response.
 *
 * Carries just enough metadata for partners to render a confirmation card
 * for the guest without having to round-trip through `units()->show()`.
 * The primary identifier is `$uuid` — Maat's internal numeric `id` is never
 * exposed on the Waffarha surface.
 *
 * @phpstan-type AvailabilityPropertyPayload array{uuid?: string|null, title?: string|null, image?: string|null, address?: string|null, city?: string|null, beds?: int|string|null, bathroom?: int|string|null}
 */
final readonly class AvailabilityProperty
{
    public function __construct(
        public ?string $uuid,
        public ?string $title,
        public ?string $image,
        public ?string $address,
        public ?string $city,
        public ?int $beds,
        public ?int $bathroom,
    ) {}

    /**
     * @param  AvailabilityPropertyPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $beds = $data['beds'] ?? null;
        $bathroom = $data['bathroom'] ?? null;

        return new self(
            uuid: isset($data['uuid']) && is_scalar($data['uuid']) ? (string) $data['uuid'] : null,
            title: isset($data['title']) && is_scalar($data['title']) ? (string) $data['title'] : null,
            image: isset($data['image']) && is_scalar($data['image']) ? (string) $data['image'] : null,
            address: isset($data['address']) && is_scalar($data['address']) ? (string) $data['address'] : null,
            city: isset($data['city']) && is_scalar($data['city']) ? (string) $data['city'] : null,
            beds: is_numeric($beds) ? (int) $beds : null,
            bathroom: is_numeric($bathroom) ? (int) $bathroom : null,
        );
    }
}
