<?php

declare(strict_types=1);

namespace Maat\Waffarha\Facades;

use Illuminate\Support\Facades\Facade;
use Maat\Waffarha\Resources\Bookings;
use Maat\Waffarha\Resources\Payouts;
use Maat\Waffarha\Resources\Units;
use Maat\Waffarha\WaffarhaClient;

/**
 * @see WaffarhaClient
 *
 * @method static Units units()
 * @method static Bookings bookings()
 * @method static Payouts payouts()
 * @method static array<string, mixed> request(string $method, string $endpoint, array<string, mixed> $data = [], array<string, scalar|null> $query = [])
 */
class Waffarha extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'waffarha';
    }
}
