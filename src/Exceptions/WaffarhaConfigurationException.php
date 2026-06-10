<?php

declare(strict_types=1);

namespace Maat\Waffarha\Exceptions;

/**
 * Thrown when the SDK is misconfigured (e.g. a missing base URL or credentials)
 * before any HTTP request is attempted.
 */
class WaffarhaConfigurationException extends WaffarhaException
{
    public static function missing(string $key): self
    {
        return new self(
            "Waffarha SDK is missing required configuration value [{$key}]. ".
            'Set the corresponding environment variable and clear your config cache.'
        );
    }
}
