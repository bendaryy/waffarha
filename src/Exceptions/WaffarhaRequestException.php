<?php

declare(strict_types=1);

namespace Maat\Waffarha\Exceptions;

use Throwable;

/**
 * Thrown when the Waffarha API returns an unsuccessful HTTP status, or when the
 * request cannot be completed (connection error, timeout).
 */
class WaffarhaRequestException extends WaffarhaException
{
    /**
     * @param  int  $status  HTTP status code (0 when the request never completed).
     * @param  string|null  $body  Raw response body, when available.
     */
    public function __construct(
        string $message,
        public readonly int $status = 0,
        public readonly ?string $body = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);
    }

    public static function fromStatus(string $method, string $url, int $status, ?string $body): self
    {
        return new self(
            "Waffarha API request [{$method} {$url}] failed with status {$status}.",
            $status,
            $body,
        );
    }

    public static function connectionError(string $method, string $url, Throwable $previous): self
    {
        return new self(
            "Waffarha API request [{$method} {$url}] could not be completed: {$previous->getMessage()}",
            0,
            null,
            $previous,
        );
    }
}
