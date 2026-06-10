<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * A cancellation policy. Used for both `guest_cancellation_policy` (single) and
 * each entry of `host_cancellation_policies` (list) in the single-unit response.
 *
 * The two share `id`/`name`/`display_name`/`short_description`/`descriptions`;
 * the host variant adds the compensation/host fields, which are null for the
 * guest policy.
 *
 * @phpstan-type PolicyPayload array<string, mixed>
 */
final readonly class CancellationPolicy
{
    /**
     * @param  list<PolicyDescription>  $descriptions
     */
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $displayName,
        public ?string $shortDescription,
        public array $descriptions,
        public ?bool $hostCancellationEnabled = null,
        public ?string $hostCancellationNotes = null,
        public ?string $customCompensation30Days = null,
        public ?string $customCompensation14To29Days = null,
        public ?string $customCompensation7To13Days = null,
    ) {}

    /**
     * @param  PolicyPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key]) ? (string) $data[$key] : null;

        $descriptions = [];
        if (isset($data['descriptions']) && is_array($data['descriptions'])) {
            foreach ($data['descriptions'] as $entry) {
                if (is_array($entry)) {
                    /** @var array<string, mixed> $entry */
                    $descriptions[] = PolicyDescription::fromArray($entry);
                }
            }
        }

        return new self(
            id: isset($data['id']) && is_scalar($data['id']) ? (int) $data['id'] : null,
            name: $str('name'),
            displayName: $str('display_name'),
            shortDescription: $str('short_description'),
            descriptions: $descriptions,
            hostCancellationEnabled: array_key_exists('host_cancellation_enabled', $data) ? (bool) $data['host_cancellation_enabled'] : null,
            hostCancellationNotes: $str('host_cancellation_notes'),
            customCompensation30Days: $str('custom_compensation_30_days'),
            customCompensation14To29Days: $str('custom_compensation_14_29_days'),
            customCompensation7To13Days: $str('custom_compensation_7_13_days'),
        );
    }
}
