<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * Typed representation of the OAuth token endpoint response.
 *
 * @phpstan-type TokenPayload array{
 *     token_type?: string,
 *     expires_in?: int,
 *     access_token: string,
 *     refresh_token?: string|null
 * }
 */
final readonly class TokenResponse
{
    public function __construct(
        public string $accessToken,
        public int $expiresIn,
        public string $tokenType = 'Bearer',
        public ?string $refreshToken = null,
    ) {}

    /**
     * @param  TokenPayload  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            accessToken: (string) ($data['access_token'] ?? ''),
            expiresIn: (int) ($data['expires_in'] ?? 0),
            tokenType: (string) ($data['token_type'] ?? 'Bearer'),
            refreshToken: isset($data['refresh_token']) ? (string) $data['refresh_token'] : null,
        );
    }
}
