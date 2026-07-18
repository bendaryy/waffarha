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
        public ?BookingFinancial $financial,
        public ?Guest $guest,
        public ?string $createdAt,
        public ?string $updatedAt,
        public array $attributes,
    ) {}

    /**
     * @param  BookingPayload  $data  Either the full API envelope (`booking`
     *                                key) or the booking object itself.
     */
    public static function fromArray(array $data): self
    {
        $payload = isset($data['booking']) && is_array($data['booking'])
            ? $data['booking']
            : $data;

        $str = static fn (string $key): ?string => isset($payload[$key]) && is_scalar($payload[$key])
            ? (string) $payload[$key]
            : null;
        $int = static fn (string $key): ?int => isset($payload[$key]) && is_scalar($payload[$key])
            ? (int) $payload[$key]
            : null;

        $uuid = $payload['uuid'] ?? $payload['id'] ?? null;

        $nestedProperty = isset($payload['property']) && is_array($payload['property'])
            ? $payload['property']
            : [];
        $propertyUuid = $payload['property_uuid']
            ?? $payload['property_id']
            ?? ($nestedProperty['uuid'] ?? null);
        $propertyTitle = $payload['property_title']
            ?? ($nestedProperty['title'] ?? null);

        $guest = isset($payload['guest']) && is_array($payload['guest'])
            ? Guest::fromArray($payload['guest'])
            : null;

        $financial = isset($payload['financial']) && is_array($payload['financial'])
            ? BookingFinancial::fromArray($payload['financial'])
            : null;

        return new self(
            uuid: $uuid !== null && is_scalar($uuid) ? (string) $uuid : null,
            providerBookingId: $str('provider_booking_id'),
            provider: $str('provider'),
            propertyUuid: $propertyUuid !== null && is_scalar($propertyUuid) ? (string) $propertyUuid : null,
            propertyTitle: $propertyTitle !== null && is_scalar($propertyTitle) ? (string) $propertyTitle : null,
            checkIn: $str('check_in'),
            checkOut: $str('check_out'),
            guestsCount: $int('guests_count') ?? $int('number_of_guests'),
            totalAmount: $str('total_amount'),
            currency: $str('currency'),
            status: $str('status'),
            cancellationReason: $str('cancellation_reason'),
            notes: $str('notes'),
            financial: $financial,
            guest: $guest,
            createdAt: $str('created_at'),
            updatedAt: $str('updated_at'),
            attributes: $payload,
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
