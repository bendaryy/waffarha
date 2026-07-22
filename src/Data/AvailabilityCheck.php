<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;
use Traversable;

/**
 * Result of a `POST /waffarha/unit/{uuid}/check` call.
 *
 * A successful (`available === true`) instance comes back from the SDK; an
 * unavailable date range surfaces as a {@see WaffarhaRequestException}
 * (HTTP 409) so consumers can `try { } catch { }` the unhappy path without
 * branching on a status code. The exception's `body` carries the same
 * `reason` / `violated_blocks` keys documented in `docs/check-availability.md`.
 *
 * Iterable + countable over the per-night `$breakdown` so:
 *
 *     foreach ($check as $night) { echo "{$night->date}: {$night->price}\n"; }
 *     $nights = count($check);
 *
 * Date + money fields are grouped into blocks (`$bookingDates`, `$financial`)
 * to mirror the API shape. The most commonly used ones (`$checkIn`,
 * `$checkOut`, `$nights`, `$subtotal`, `$cleaningFee`, `$total`, `$currency`)
 * are also exposed as top-level read-only properties for ergonomics + IDE
 * autocomplete — they delegate to the same underlying blocks at
 * construction time.
 *
 * @implements IteratorAggregate<int, AvailabilityNight>
 *
 * @phpstan-type AvailabilityPayload array{available?: bool|int|string|null, property_uuid?: string|null, check_in?: string|null, check_out?: string|null, nights?: int|string|null, booking_dates?: array<string, mixed>, currency?: string|null, subtotal?: int|float|string|null, cleaning_fee?: int|float|string|null, total?: int|float|string|null, financial?: array<string, mixed>, property?: array<string, mixed>, special_rates_applied?: list<array<string, mixed>>, breakdown?: list<array<string, mixed>>, is_xuru_unit?: bool|int|string|null, xuru_status?: bool|int|string|null, xuru_price_applied?: bool|int|string|null, effective_minimum_stay?: int|string|null}
 */
final readonly class AvailabilityCheck implements Countable, IteratorAggregate
{
    public ?string $checkIn;

    public ?string $checkOut;

    public ?int $nights;

    public ?string $currency;

    public ?float $subtotal;

    public ?float $longStayDiscount;

    public ?bool $longStayApplied;

    public ?float $cleaningFee;

    public ?float $access;

    public ?float $serviceFee;

    public ?float $taxRate;

    public ?float $tax;

    public ?float $hostTaxRate;

    public ?float $taxFromHost;

    public ?float $total;

    public ?float $commissionPercentage;

    public ?float $commissionAmount;

    public ?float $discountPercentage;

    public ?float $discountAmount;

    public ?float $subtotalAfterDiscount;

    /**
     * @param  list<AvailabilityNight>  $breakdown
     * @param  list<SpecialRateApplied>  $specialRatesApplied
     */
    public function __construct(
        public bool $available,
        public ?string $propertyUuid,
        public BookingDates $bookingDates,
        public AvailabilityFinancial $financial,
        public ?AvailabilityProperty $property,
        public array $specialRatesApplied,
        public array $breakdown,
        public ?bool $isXuruUnit = null,
        public ?bool $xuruStatus = null,
        public ?bool $xuruPriceApplied = null,
        public ?int $effectiveMinimumStay = null,
    ) {
        $this->checkIn = $bookingDates->checkIn;
        $this->checkOut = $bookingDates->checkOut;
        $this->nights = $bookingDates->totalDays;
        $this->currency = $financial->currency;
        $this->subtotal = $financial->subtotal;
        $this->longStayDiscount = $financial->longStayDiscount;
        $this->longStayApplied = $financial->longStayApplied;
        $this->cleaningFee = $financial->cleaningFee;
        $this->access = $financial->access;
        $this->serviceFee = $financial->serviceFee;
        $this->taxRate = $financial->taxRate;
        $this->tax = $financial->tax;
        $this->hostTaxRate = $financial->hostTaxRate;
        $this->taxFromHost = $financial->taxFromHost;
        $this->total = $financial->total;
        $this->commissionPercentage = $financial->commissionPercentage;
        $this->commissionAmount = $financial->commissionAmount;
        $this->discountPercentage = $financial->discountPercentage;
        $this->discountAmount = $financial->discountAmount;
        $this->subtotalAfterDiscount = $financial->subtotalAfterDiscount;
    }

    /**
     * @param  AvailabilityPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $rows = isset($data['breakdown']) && is_array($data['breakdown']) ? $data['breakdown'] : [];

        $breakdown = [];
        foreach (array_values($rows) as $row) {
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $breakdown[] = AvailabilityNight::fromArray($row);
            }
        }

        // New shape — `booking_dates` block. Falls back to the legacy
        // top-level `check_in` / `check_out` / `nights` keys so the SDK
        // keeps parsing pre-booking-dates Maat responses without crashing.
        $bookingDates = isset($data['booking_dates']) && is_array($data['booking_dates'])
            ? BookingDates::fromArray($data['booking_dates'])
            : BookingDates::fromLegacyTopLevel([
                'check_in' => $data['check_in'] ?? null,
                'check_out' => $data['check_out'] ?? null,
                'nights' => $data['nights'] ?? null,
            ]);

        // Same dance for the `financial` block.
        $financial = isset($data['financial']) && is_array($data['financial'])
            ? AvailabilityFinancial::fromArray($data['financial'])
            : AvailabilityFinancial::fromLegacyTopLevel([
                'currency' => $data['currency'] ?? null,
                'subtotal' => $data['subtotal'] ?? null,
                'cleaning_fee' => $data['cleaning_fee'] ?? null,
                'total' => $data['total'] ?? null,
            ]);

        $property = isset($data['property']) && is_array($data['property'])
            ? AvailabilityProperty::fromArray($data['property'])
            : null;

        $specialRatesApplied = [];
        if (isset($data['special_rates_applied']) && is_array($data['special_rates_applied'])) {
            foreach (array_values($data['special_rates_applied']) as $row) {
                if (is_array($row)) {
                    /** @var array<string, mixed> $row */
                    $specialRatesApplied[] = SpecialRateApplied::fromArray($row);
                }
            }
        }

        $effectiveMinimumStay = $data['effective_minimum_stay'] ?? null;

        return new self(
            available: isset($data['available']) ? (bool) $data['available'] : true,
            propertyUuid: isset($data['property_uuid']) && is_scalar($data['property_uuid']) ? (string) $data['property_uuid'] : null,
            bookingDates: $bookingDates,
            financial: $financial,
            property: $property,
            specialRatesApplied: $specialRatesApplied,
            breakdown: $breakdown,
            isXuruUnit: array_key_exists('is_xuru_unit', $data) ? (bool) $data['is_xuru_unit'] : null,
            xuruStatus: array_key_exists('xuru_status', $data) && $data['xuru_status'] !== null
                ? (bool) $data['xuru_status']
                : null,
            xuruPriceApplied: array_key_exists('xuru_price_applied', $data) ? (bool) $data['xuru_price_applied'] : null,
            effectiveMinimumStay: is_numeric($effectiveMinimumStay) ? (int) $effectiveMinimumStay : null,
        );
    }

    public function count(): int
    {
        return count($this->breakdown);
    }

    /**
     * @return Traversable<int, AvailabilityNight>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->breakdown);
    }
}
