<?php

declare(strict_types=1);

namespace Maat\Waffarha\Tests\Integration;

use Maat\Waffarha\Auth\TokenManager;
use Maat\Waffarha\Data\UnitCollection;
use Maat\Waffarha\Data\UnitDetail;
use Maat\Waffarha\WaffarhaClient;
use PHPUnit\Framework\Attributes\Group;

/**
 * End-to-end checks against the real Maat API. Assertions are intentionally
 * tolerant of live data (counts/fields vary) — they verify the SDK plumbing
 * (auth, request shaping, DTO mapping), not specific records.
 */
#[Group('live')]
class LiveUnitsTest extends IntegrationTestCase
{
    public function test_it_obtains_a_real_access_token(): void
    {
        $token = $this->app->make(TokenManager::class)->token();

        $this->assertNotEmpty($token, 'Expected a non-empty access token from the live token endpoint.');
    }

    public function test_it_fetches_real_units(): void
    {
        $units = $this->app->make(WaffarhaClient::class)->units()->list(['per_page' => 5]);

        $this->assertInstanceOf(UnitCollection::class, $units);
        $this->assertGreaterThan(0, count($units), 'Expected at least one live unit.');

        $first = $units->items[0];
        $this->assertNotNull($first->uuid, 'Live units should expose a uuid.');
        $this->assertNotNull($first->title, 'Live units should expose a title.');
    }

    public function test_it_fetches_a_single_real_unit(): void
    {
        $client = $this->app->make(WaffarhaClient::class);

        $units = $client->units()->list(['per_page' => 1]);
        $this->assertGreaterThan(0, count($units), 'Need at least one unit to fetch its detail.');

        $uuid = (string) $units->items[0]->uuid;
        $detail = $client->units()->get($uuid);

        $this->assertInstanceOf(UnitDetail::class, $detail);
        $this->assertSame($uuid, $detail->property->uuid, 'units()->get() should return the requested unit.');
        $this->assertNotNull($detail->property->title, 'Unit detail should expose a title.');
    }
}
