<?php

declare(strict_types=1);

namespace Maat\Waffarha\Resources;

use InvalidArgumentException;
use Maat\Waffarha\Data\Payout;
use Maat\Waffarha\Data\PayoutCollection;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;

/**
 * The `payouts` API: per-booking settlement queue.
 *
 * Endpoints exposed today:
 *   - {@see Payouts::list()} — `GET /waffarha/payouts`
 *   - {@see Payouts::get()} — `GET /waffarha/payouts/{uuid}`
 *   - {@see Payouts::submitProof()} — `POST /waffarha/payouts/{uuid}/proof`
 *
 * Payouts are addressed by their public `uuid` (a UUID v4 string) — the
 * internal sequential id never leaves Maat. Provider scoping is enforced
 * server-side from the OAuth client behind the access token, so the SDK
 * never sends a provider id explicitly — a token issued for one provider
 * can never read or modify another provider's payouts.
 */
final class Payouts extends Resource
{
    /**
     * List payouts for the authenticated provider.
     *
     * Supported `$query` filters (any combination):
     *   - `status` — `pending`, `proof_submitted`, `completed`, `rejected`,
     *     `open` (= pending + proof_submitted, the default), or `all`.
     *   - `per_page` — 1..200, default 50.
     *   - `booking_id` — narrow to a single Maat booking id.
     *
     * @param  array<string, scalar|null>  $query
     *
     * @throws WaffarhaRequestException
     */
    public function list(array $query = []): PayoutCollection
    {
        return PayoutCollection::fromArray(
            $this->transport->send('GET', 'payouts', query: $query)
        );
    }

    /**
     * Fetch a single payout by its Maat UUID.
     *
     * @throws WaffarhaRequestException
     */
    public function get(string $uuid): Payout
    {
        return Payout::fromArray(
            $this->transport->send('GET', "payouts/{$uuid}")
        );
    }

    /**
     * Attach a transfer-proof file to an open payout. The endpoint accepts
     * a multipart/form-data POST with:
     *   - `proof`: the receipt as a file (jpg/png/gif/webp/pdf, ≤ 10 MB)
     *   - `notes`: optional free-form note shown to the Maat admin reviewer.
     *
     * `$file` may be either:
     *   - an absolute path on the local filesystem (will be opened in
     *     binary-read mode), OR
     *   - an array `['contents' => <string|resource>, 'filename' => 'x.pdf']`
     *     for fully programmatic uploads (e.g. when the bytes are already
     *     in memory).
     *
     * Re-uploads are accepted while the payout is still open (pending or
     * proof_submitted); attempting to re-upload after the admin approved or
     * rejected the payout will surface as a 409 from the API.
     *
     * @param  string|array{contents: mixed, filename?: string, headers?: array<string,string>}  $file
     *
     * @throws WaffarhaRequestException
     */
    public function submitProof(string $uuid, string|array $file, ?string $notes = null): Payout
    {
        if (is_string($file) && trim($file) === '') {
            throw new InvalidArgumentException('Proof file path cannot be empty.');
        }

        $fields = $notes !== null ? ['notes' => $notes] : [];

        return Payout::fromArray(
            $this->transport->sendMultipart(
                endpoint: "payouts/{$uuid}/proof",
                fields: $fields,
                files: ['proof' => $file],
            )
        );
    }
}
