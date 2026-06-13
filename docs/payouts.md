# `payouts()` — provider settlement queue

Maat raises a payout request every time it owes you money for a specific
booking. Each request is **standalone** (one row per booking, never
aggregated) and follows this lifecycle:

```
pending ─upload proof──▶ proof_submitted ─admin approves──▶ completed
   │                            │
   │                            └─admin rejects────────────▶ rejected
   └─admin rejects (no proof)───────────────────────────────▶ rejected
```

`completed` and `rejected` are terminal. After a rejection the Maat admin can
issue a brand-new payout for the same booking — you'll just see a fresh id
when you next poll.

This resource gives you three endpoints:

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `payouts()->list()` | `GET /waffarha/payouts` | Poll for payouts that need action |
| `payouts()->get()` | `GET /waffarha/payouts/{id}` | Fetch a single payout |
| `payouts()->submitProof()` | `POST /waffarha/payouts/{id}/proof` | Attach the bank-transfer receipt |

Provider scoping is enforced server-side from the OAuth client behind the
access token — your token will never see another provider's payouts, and
cross-tenant ids respond with `404` (not `403`) so existence is never
leaked.

## `list()`

```php
Waffarha::payouts()->list(array $query = []): PayoutCollection
```

Supported filters (any combination):

| Key | Allowed values | Default |
| --- | --- | --- |
| `status` | `open`, `pending`, `proof_submitted`, `completed`, `rejected`, `all` | `open` (= pending + proof_submitted) |
| `per_page` | `1`..`200` | `50` |
| `booking_id` | a Maat booking numeric id | _none_ |

```php
use Maat\Waffarha\Facades\Waffarha;

$pending = Waffarha::payouts()->list(['status' => 'pending']);

foreach ($pending as $payout) {
    echo $payout->id, ' — ', $payout->amount, ' ', $payout->currency, PHP_EOL;
}

echo 'Total pending: ', $pending->meta?->total ?? count($pending);
```

## `get()`

```php
Waffarha::payouts()->get(int $id): Payout
```

```php
$payout = Waffarha::payouts()->get(42);

if ($payout->status === 'proof_submitted') {
    // The admin will review it soon.
}
```

## `submitProof()`

Attach the bank-transfer receipt (image or PDF) to an open payout. The proof
flips the payout to `proof_submitted` and notifies the Maat admin reviewer.

```php
Waffarha::payouts()->submitProof(
    int $id,
    string|array $file,
    ?string $notes = null,
): Payout
```

- `$file` may be either:
  - an absolute path to a file on disk (the SDK opens it in binary-read mode), or
  - an array `['contents' => <string|resource>, 'filename' => 'receipt.pdf']`
    for fully programmatic uploads.
- `$notes` is an optional free-form note shown to the Maat reviewer (≤ 2000 chars).

Constraints (enforced by Maat):

- File type: `jpg`, `jpeg`, `png`, `gif`, `webp`, `pdf`.
- Max size: 10 MB.
- Re-uploads are allowed while the payout is still open. After
  `completed`/`rejected` the server replies `409`.

```php
$payout = Waffarha::payouts()->submitProof(
    id: 42,
    file: '/tmp/transfer-2026-06-13.pdf',
    notes: 'Bank reference WAF-#1278; transfer settled 2026-06-13 14:30 GMT+3',
);

echo $payout->status; // proof_submitted
echo $payout->proofUrl; // public URL of the uploaded proof
```

## Response shape

Single payout envelope returned by every endpoint above:

```json
{
    "ResponseCode": "200",
    "Result": "true",
    "ResponseMsg": "Payout retrieved successfully.",
    "payout": {
        "id": 42,
        "booking": {
            "id": 12345,
            "uuid": "b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d"
        },
        "amount": 4500.00,
        "currency": "EGP",
        "status": "proof_submitted",
        "status_label": "Proof Submitted",
        "proof_url": "https://d2nl84gpjzclyn.cloudfront.net/provider_payout_proofs/abc.pdf",
        "proof_type": "file",
        "provider_notes": "Bank reference WAF-#1278; transfer settled 2026-06-13 14:30 GMT+3",
        "rejection_reason": null,
        "proof_submitted_at": "2026-06-13 17:45:12",
        "reviewed_at": null,
        "created_at": "2026-06-12 09:00:01",
        "updated_at": "2026-06-13 17:45:12"
    }
}
```

The list endpoint wraps an array of these payouts under `payouts`
alongside the standard `pagination` block.

See [`Payout`](data-objects.md#payout) in the data-objects reference for
the typed DTO.
