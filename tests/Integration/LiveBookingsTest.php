<?php

declare(strict_types=1);

namespace Maat\Waffarha\Tests\Integration;

use Maat\Waffarha\Data\Booking;
use Maat\Waffarha\Data\BookingCollection;
use Maat\Waffarha\WaffarhaClient;
use PHPUnit\Framework\Attributes\Group;

/**
 * End-to-end checks against the real Maat API. Assertions are intentionally
 * tolerant of live data (an account may legitimately have zero bookings) — they
 * verify the SDK plumbing (auth, request shaping, DTO mapping), not specific
 * records.
 *
 * This suite is read-only: it never creates/updates/cancels live bookings. It
 * also doubles as the way to capture the real bookings response shape so the
 * provisional {@see Booking} / {@see BookingCollection} mapping can be refined.
 */
#[Group('live')]
class LiveBookingsTest extends IntegrationTestCase
{
    public function test_it_lists_real_bookings(): void
    {
        $bookings = $this->app->make(WaffarhaClient::class)->bookings()->list(['per_page' => 5]);

        $this->assertInstanceOf(BookingCollection::class, $bookings);
        // Do not assume any bookings exist; only assert the plumbing maps cleanly.
        $this->assertGreaterThanOrEqual(0, count($bookings));

        if (count($bookings) > 0) {
            $this->assertInstanceOf(Booking::class, $bookings->items[0]);
        }
    }
}
