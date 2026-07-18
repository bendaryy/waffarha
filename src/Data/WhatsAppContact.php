<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Maat support WhatsApp contact returned by `GET /waffarha/whatsapp`.
 *
 * Phone comes from Maat `tbl_setting.app_phone_number`. Digits / URLs are
 * normalised for WhatsApp deep links.
 *
 * @phpstan-type WhatsAppContactPayload array<string, mixed>
 */
final readonly class WhatsAppContact
{
    /**
     * @param  array<string, mixed>  $attributes  Full decoded `whatsapp` object.
     */
    public function __construct(
        public ?string $phoneNumber,
        public ?string $phoneDigits,
        public ?string $url,
        public ?string $deepLink,
        public array $attributes,
    ) {}

    /**
     * @param  WhatsAppContactPayload  $data  Full API envelope or the
     *                                        `whatsapp` object itself.
     */
    public static function fromArray(array $data): self
    {
        $payload = isset($data['whatsapp']) && is_array($data['whatsapp'])
            ? $data['whatsapp']
            : $data;

        $str = static fn (string $key): ?string => isset($payload[$key]) && is_scalar($payload[$key])
            ? (string) $payload[$key]
            : null;

        return new self(
            phoneNumber: $str('phone_number'),
            phoneDigits: $str('phone_digits'),
            url: $str('url'),
            deepLink: $str('deep_link'),
            attributes: $payload,
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
