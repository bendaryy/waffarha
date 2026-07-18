<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use Maat\Waffarha\Resources\Bookings;

/**
 * Guest-facing receipt returned by `POST /waffarha/book_details`
 * ({@see Bookings::bookDetails()}).
 *
 * Shape is a `bookdetails` object with day breakdown + financial summary.
 * Always in EGP. The full decoded `bookdetails` payload is retained in
 * {@see self::$attributes} so fields not promoted here are never lost.
 *
 * @phpstan-type GuestBookDetailsPayload array<string, mixed>
 */
final readonly class GuestBookDetails
{
    /**
     * @param  array<string, mixed>  $attributes  Full decoded `bookdetails` object.
     * @param  list<array<string, mixed>>  $dayBreakdown
     * @param  array<string, mixed>  $financialSummary
     */
    public function __construct(
        public ?string $currency,
        public ?string $uuid,
        public ?string $title,
        public ?string $checkIn,
        public ?string $checkOut,
        public ?int $totalDay,
        public ?float $subtotal,
        public ?float $cleaningFee,
        public ?float $access,
        public ?float $hostTaxRate,
        public ?float $taxFromHost,
        public ?float $total,
        public ?string $guestName,
        public array $dayBreakdown,
        public array $financialSummary,
        public array $attributes,
    ) {}

    /**
     * @param  GuestBookDetailsPayload  $data  Either the full API envelope
     *                                         (`bookdetails` key) or the
     *                                         `bookdetails` object itself.
     */
    public static function fromArray(array $data): self
    {
        $details = isset($data['bookdetails']) && is_array($data['bookdetails'])
            ? $data['bookdetails']
            : $data;

        $str = static fn (string $key): ?string => isset($details[$key]) && is_scalar($details[$key])
            ? (string) $details[$key]
            : null;
        $float = static fn (string $key): ?float => isset($details[$key]) && is_numeric($details[$key])
            ? (float) $details[$key]
            : null;
        $int = static fn (string $key): ?int => isset($details[$key]) && is_scalar($details[$key])
            ? (int) $details[$key]
            : null;

        $dayBreakdown = isset($details['day_breakdown']) && is_array($details['day_breakdown'])
            ? array_values(array_filter($details['day_breakdown'], 'is_array'))
            : [];

        $financialSummary = isset($details['financial_summary']) && is_array($details['financial_summary'])
            ? $details['financial_summary']
            : [];

        return new self(
            currency: $str('currency') ?? 'EGP',
            uuid: $str('uuid'),
            title: $str('title'),
            checkIn: $str('check_in'),
            checkOut: $str('check_out'),
            totalDay: $int('total_day'),
            subtotal: $float('subtotal'),
            cleaningFee: $float('cleaning_fee'),
            access: $float('access'),
            hostTaxRate: $float('host_tax_rate') ?? $float('tax_rate_from_host'),
            taxFromHost: $float('tax_from_host'),
            total: $float('total'),
            guestName: $str('guest_name') ?? $str('customer_name'),
            dayBreakdown: $dayBreakdown,
            financialSummary: $financialSummary,
            attributes: $details,
        );
    }

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
