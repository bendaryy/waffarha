<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Guest details attached to a {@see Booking}.
 *
 * The promoted keys are identical across the documented create-request payload
 * and the outbound booking webhook, so they are the best-evidenced fields. The
 * full decoded object is always retained in {@see Guest::$attributes}.
 *
 * @phpstan-type GuestPayload array<string, mixed>
 */
final readonly class Guest
{
    /**
     * @param  array<string, mixed>  $attributes  Full decoded payload for this guest.
     */
    public function __construct(
        public ?string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $nationality,
        public ?string $passportNumber,
        public ?string $dateOfBirth,
        public array $attributes,
    ) {}

    /**
     * @param  GuestPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key])
            ? (string) $data[$key]
            : null;

        return new self(
            name: $str('name'),
            email: $str('email'),
            phone: $str('phone'),
            nationality: $str('nationality'),
            passportNumber: $str('passport_number'),
            dateOfBirth: $str('date_of_birth'),
            attributes: $data,
        );
    }

    /**
     * Read a raw attribute by key, with an optional fallback.
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
