<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Typed representation of a provider booking on a Maat unit.
 *
 * The commonly-used fields are promoted to typed properties; the full decoded
 * payload is always retained in {@see Booking::$attributes} and reachable via
 * {@see Booking::get()}, so fields not promoted here are never lost.
 *
 * > **Provisional mapping.** The booking endpoints' real HTTP response shape is
 * > not yet confirmed. These fields are inferred from the documented
 * > create-request payload and the outbound booking webhook (see
 * > docs/webhooks.md), which use slightly different key names — so each field is
 * > read from every observed candidate key (e.g. `uuid`/`id`,
 * > `guests_count`/`number_of_guests`). Promote/refine once a live response is
 * > captured.
 *
 * Note: monetary fields are kept as strings (e.g. "4500.00") to avoid
 * precision/rounding surprises, matching the rest of the SDK's DTOs.
 *
 * @phpstan-type BookingPayload array<string, mixed>
 */
final readonly class Booking
{
    /**
     * @param  array<string, mixed>  $attributes  Full decoded payload for this booking.
     */
    public function __construct(
        public ?string $uuid,
        public ?string $providerBookingId,
        public ?string $provider,
        public ?string $propertyUuid,
        public ?string $propertyTitle,
        public ?string $checkIn,
        public ?string $checkOut,
        public ?int $guestsCount,
        public ?string $totalAmount,
        public ?string $currency,
        public ?string $status,
        public ?string $cancellationReason,
        public ?string $notes,
        public ?Guest $guest,
        public ?string $createdAt,
        public ?string $updatedAt,
        public array $attributes,
    ) {}

    /**
     * @param  BookingPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key])
            ? (string) $data[$key]
            : null;
        $int = static fn (string $key): ?int => isset($data[$key]) && is_scalar($data[$key])
            ? (int) $data[$key]
            : null;

        $uuid = $data['uuid'] ?? $data['id'] ?? null;
        $propertyUuid = $data['property_uuid'] ?? $data['property_id'] ?? null;

        $guest = isset($data['guest']) && is_array($data['guest'])
            ? Guest::fromArray($data['guest'])
            : null;

        return new self(
            uuid: $uuid !== null && is_scalar($uuid) ? (string) $uuid : null,
            providerBookingId: $str('provider_booking_id'),
            provider: $str('provider'),
            propertyUuid: $propertyUuid !== null && is_scalar($propertyUuid) ? (string) $propertyUuid : null,
            propertyTitle: $str('property_title'),
            checkIn: $str('check_in'),
            checkOut: $str('check_out'),
            guestsCount: $int('guests_count') ?? $int('number_of_guests'),
            totalAmount: $str('total_amount'),
            currency: $str('currency'),
            status: $str('status'),
            cancellationReason: $str('cancellation_reason'),
            notes: $str('notes'),
            guest: $guest,
            createdAt: $str('created_at'),
            updatedAt: $str('updated_at'),
            attributes: $data,
        );
    }

    /**
     * Read a raw attribute by key, with an optional fallback. Useful for fields
     * not promoted to typed properties.
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
