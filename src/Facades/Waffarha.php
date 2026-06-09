<?php

namespace Maat\Waffarha\Facades;

use Illuminate\Support\Facades\Facade;
use Maat\Waffarha\WaffarhaClient;

/**
 * @see WaffarhaClient
 *
 * @method static array|null getUnits(array $queryParameters = [])
 * @method static array|null getUnit(string $uuid)
 * @method static array|null listBookings(array $queryParameters = [])
 * @method static array|null createBooking(array $payload)
 * @method static array|null getBooking(string $uuid)
 * @method static array|null updateBooking(string $uuid, array $payload)
 * @method static array|null cancelBooking(string $uuid, ?string $reason = null)
 * @method static array|null request(string $method, string $endpoint, array $data = [])
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
