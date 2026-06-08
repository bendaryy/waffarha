<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Typed representation of the single-unit (detail) response.
 *
 * Shape (top-level keys): `propetydetails` (sic), `house_descriptions`,
 * `house_rules`, `house_safety`, `amenities`, `every_corner_count`,
 * `reviewlist`, `total_review`, `guest_cancellation_policy`,
 * `host_cancellation_policies` (plus the `ResponseCode`/`Result`/`ResponseMsg`
 * envelope, retained in {@see UnitDetail::$attributes}).
 *
 * Fully typed where the payload shape is known. {@see $houseRules} and
 * {@see $reviews} remain raw `list<array<string,mixed>>` because they were empty
 * in every observed sample — they will be promoted to DTOs once their item
 * shapes are confirmed.
 *
 * @phpstan-type UnitDetailPayload array<string, mixed>
 */
final readonly class UnitDetail
{
    /**
     * @param  list<HouseDescription>  $houseDescriptions
     * @param  list<CancellationPolicy>  $hostCancellationPolicies
     * @param  list<Amenity>  $amenities
     * @param  list<SafetyItem>  $houseSafety
     * @param  list<RoomGallery>  $everyCornerCount
     * @param  list<array<string, mixed>>  $houseRules
     * @param  list<array<string, mixed>>  $reviews
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public PropertyDetails $property,
        public array $houseDescriptions,
        public ?CancellationPolicy $guestCancellationPolicy,
        public array $hostCancellationPolicies,
        public ?int $totalReview,
        public array $amenities,
        public array $houseSafety,
        public array $everyCornerCount,
        public array $houseRules,
        public array $reviews,
        public array $attributes,
    ) {}

    /**
     * @param  UnitDetailPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $property = isset($data['propetydetails']) && is_array($data['propetydetails'])
            ? $data['propetydetails']
            : [];
        /** @var array<string, mixed> $property */

        $guest = isset($data['guest_cancellation_policy']) && is_array($data['guest_cancellation_policy'])
            ? CancellationPolicy::fromArray($data['guest_cancellation_policy'])
            : null;

        return new self(
            property: PropertyDetails::fromArray($property),
            houseDescriptions: self::mapList(
                $data['house_descriptions'] ?? null,
                static fn (array $row): HouseDescription => HouseDescription::fromArray($row),
            ),
            guestCancellationPolicy: $guest,
            hostCancellationPolicies: self::mapList(
                $data['host_cancellation_policies'] ?? null,
                static fn (array $row): CancellationPolicy => CancellationPolicy::fromArray($row),
            ),
            totalReview: isset($data['total_review']) && is_scalar($data['total_review']) ? (int) $data['total_review'] : null,
            amenities: self::mapList(
                $data['amenities'] ?? null,
                static fn (array $row): Amenity => Amenity::fromArray($row),
            ),
            houseSafety: self::mapList(
                $data['house_safety'] ?? null,
                static fn (array $row): SafetyItem => SafetyItem::fromArray($row),
            ),
            everyCornerCount: self::mapList(
                $data['every_corner_count'] ?? null,
                static fn (array $row): RoomGallery => RoomGallery::fromArray($row),
            ),
            houseRules: self::rawList($data['house_rules'] ?? null),
            reviews: self::rawList($data['reviewlist'] ?? null),
            attributes: $data,
        );
    }

    /**
     * Read a raw top-level attribute by key, with an optional fallback.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Map a list of array rows through a DTO factory, skipping non-array rows.
     *
     * @template T
     *
     * @param  mixed  $value
     * @param  callable(array<string, mixed>): T  $factory
     * @return list<T>
     */
    private static function mapList(mixed $value, callable $factory): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $out[] = $factory($row);
            }
        }

        return $out;
    }

    /**
     * Normalise a value into a list of array rows (for not-yet-typed sections).
     *
     * @return list<array<string, mixed>>
     */
    private static function rawList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $out[] = $row;
            }
        }

        return $out;
    }
}
