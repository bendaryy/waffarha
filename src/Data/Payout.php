<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Typed representation of a single payout request raised by Maat for a
 * specific booking. Mirrors the JSON envelope returned by:
 *
 *   GET  /waffarha/payouts
 *   GET  /waffarha/payouts/{id}
 *   POST /waffarha/payouts/{id}/proof
 *
 * Lifecycle (see Maat-side `App\ProviderPayoutStatus`):
 *   pending → proof_submitted → completed
 *                            └→ rejected (terminal; a fresh payout may be
 *                                         created by Maat afterwards)
 *
 * The commonly-used fields are promoted to typed properties; the full
 * decoded payload is kept in {@see Payout::$attributes} so partners can
 * still reach future server-side additions via {@see Payout::get()}
 * without an SDK release.
 *
 * Monetary amounts are exposed as floats because the Maat envelope already
 * casts them via `(float)` server-side; if you need string-level precision
 * for accounting, read the raw value via `$payout->get('amount')`.
 *
 * @phpstan-type PayoutPayload array<string, mixed>
 */
final readonly class Payout
{
    /**
     * @param  array<string, mixed>  $attributes  Full decoded payload for this payout.
     */
    public function __construct(
        public ?string $uuid,
        public ?string $bookingUuid,
        public ?float $amount,
        public ?string $currency,
        public ?string $status,
        public ?string $statusLabel,
        public ?string $proofUrl,
        public ?string $proofType,
        public ?string $providerNotes,
        public ?string $rejectionReason,
        public ?string $proofSubmittedAt,
        public ?string $reviewedAt,
        public ?string $createdAt,
        public ?string $updatedAt,
        public array $attributes,
    ) {}

    /**
     * @param  PayoutPayload  $data
     */
    public static function fromArray(array $data): self
    {
        // Both shapes are accepted: a flat payout row, or the wrapped
        // single-payout envelope `{ "ResponseCode": "...", "payout": { ... } }`
        // returned by `GET /payouts/{id}` and `POST /payouts/{id}/proof`. The
        // list endpoint already unwraps under `payouts[*]` upstream, so we
        // only have to look for the `payout` key here.
        if (isset($data['payout']) && is_array($data['payout'])) {
            /** @var array<string, mixed> $data */
            $data = $data['payout'];
        }

        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key])
            ? (string) $data[$key]
            : null;
        $float = static fn (string $key): ?float => isset($data[$key]) && is_scalar($data[$key])
            ? (float) $data[$key]
            : null;

        $booking = isset($data['booking']) && is_array($data['booking']) ? $data['booking'] : [];
        $bookingUuid = isset($booking['uuid']) && is_scalar($booking['uuid']) ? (string) $booking['uuid'] : null;

        return new self(
            uuid: $str('uuid'),
            bookingUuid: $bookingUuid,
            amount: $float('amount'),
            currency: $str('currency'),
            status: $str('status'),
            statusLabel: $str('status_label'),
            proofUrl: $str('proof_url'),
            proofType: $str('proof_type'),
            providerNotes: $str('provider_notes'),
            rejectionReason: $str('rejection_reason'),
            proofSubmittedAt: $str('proof_submitted_at'),
            reviewedAt: $str('reviewed_at'),
            createdAt: $str('created_at'),
            updatedAt: $str('updated_at'),
            attributes: $data,
        );
    }

    /**
     * Read a raw attribute by key, with an optional fallback. Useful for
     * fields not promoted to typed properties.
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
