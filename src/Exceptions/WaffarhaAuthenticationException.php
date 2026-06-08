<?php

declare(strict_types=1);

namespace Maat\Waffarha\Exceptions;

/**
 * Thrown when the SDK cannot obtain or refresh an OAuth access token, or when a
 * request keeps returning 401 after a token refresh + retry.
 */
class WaffarhaAuthenticationException extends WaffarhaException
{
}
