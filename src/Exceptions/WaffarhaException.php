<?php

declare(strict_types=1);

namespace Maat\Waffarha\Exceptions;

use Exception;

/**
 * Base exception for every error raised by the Waffarha SDK.
 *
 * Catch this type to handle any SDK failure generically, or catch one of the
 * specific subclasses to react to a particular failure mode.
 */
class WaffarhaException extends Exception {}
