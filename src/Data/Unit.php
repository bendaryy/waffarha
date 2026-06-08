<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Typed representation of a single syndicated unit returned by the Maat API.
 *
 * The commonly-used fields are promoted to typed properties; the full decoded
 * payload is always retained in {@see Unit::$attributes} and reachable via
 * {@see Unit::get()}, so fields not promoted here (e.g. base_price,
 * cleaning_fee, country_id) are never lost.
 *
 * Note: monetary fields arrive from the API as numeric strings (e.g. "1000")
 * and are kept verbatim as strings to avoid precision/rounding surprises.
 *
 * @phpstan-type UnitPayload array<string, mixed>
 */
final readonly class Unit
{
    /**
     * @param  list<string>  $images
     * @param  array<string, mixed>  $attributes  Full decoded payload for this unit.
     */
    public function __construct(
        public ?string $uuid,
        public ?string $title,
        public ?string $city,
        public array $images,
        public ?string $price,
        public ?string $priceCurrency,
        public ?string $latitude,
        public ?string $longitude,
        public array $attributes,
    ) {}

    /**
     * @param  UnitPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key])
            ? (string) $data[$key]
            : null;

        /** @var list<string> $images */
        $images = isset($data['images']) && is_array($data['images'])
            ? array_values(array_map(static fn ($image): string => (string) $image, $data['images']))
            : [];

        $uuid = $data['uuid'] ?? $data['id'] ?? null;

        return new self(
            uuid: $uuid !== null ? (string) $uuid : null,
            title: $str('title'),
            city: $str('city'),
            images: $images,
            price: $str('price'),
            priceCurrency: $str('price_currency'),
            latitude: $str('latitude'),
            longitude: $str('longitude'),
            attributes: $data,
        );
    }

    /**
     * Read a raw attribute by key, with an optional fallback. Useful for fields
     * not promoted to typed properties (e.g. base_price, cleaning_fee).
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
