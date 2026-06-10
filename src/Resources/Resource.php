<?php

declare(strict_types=1);

namespace Maat\Waffarha\Resources;

use Maat\Waffarha\Http\Transport;

/**
 * Base class for API resource groups. Each resource holds the shared
 * {@see Transport} and exposes the endpoints for one area of the Maat API.
 */
abstract class Resource
{
    public function __construct(
        protected readonly Transport $transport,
    ) {}
}
