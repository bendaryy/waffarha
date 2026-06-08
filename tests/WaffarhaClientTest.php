<?php

declare(strict_types=1);

namespace Maat\Waffarha\Tests;

use Illuminate\Support\Facades\Http;
use Maat\Waffarha\Data\UnitCollection;
use Maat\Waffarha\Data\UnitDetail;
use Maat\Waffarha\Exceptions\WaffarhaConfigurationException;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;
use Maat\Waffarha\Resources\Units;
use Maat\Waffarha\WaffarhaClient;

class WaffarhaClientTest extends TestCase
{
    public function test_units_accessor_returns_a_memoized_resource(): void
    {
        $client = $this->app->make(WaffarhaClient::class);

        $this->assertInstanceOf(Units::class, $client->units());
        $this->assertSame($client->units(), $client->units(), 'units() should return the same instance.');
    }

    private function fakeToken(): void
    {
        Http::fake([
            'maat.test/waffarha/oauth/token' => Http::response([
                'expires_in' => 3600,
                'access_token' => 'access-1',
                'refresh_token' => 'refresh-1',
            ]),
        ]);
    }

    public function test_get_units_returns_a_typed_collection(): void
    {
        Http::fake([
            'maat.test/waffarha/oauth/token' => Http::response([
                'expires_in' => 3600, 'access_token' => 'access-1', 'refresh_token' => 'refresh-1',
            ]),
            'maat.test/waffarha/units*' => Http::response([
                'ResponseCode' => '200',
                'Result' => 'true',
                'ResponseMsg' => 'Waffarha units retrieved successfully.',
                'units' => [
                    ['uuid' => 'u-1', 'title' => 'Unit One', 'city' => 'Cairo', 'price' => '1000', 'price_currency' => 'EGP', 'images' => ['a.png']],
                    ['uuid' => 'u-2', 'title' => 'Unit Two', 'city' => 'Dahab', 'price' => '6720', 'price_currency' => 'EGP', 'images' => []],
                ],
                'pagination' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 2],
            ]),
        ]);

        $units = $this->app->make(WaffarhaClient::class)->units()->list(['page' => 1, 'per_page' => 20]);

        $this->assertInstanceOf(UnitCollection::class, $units);
        $this->assertCount(2, $units);
        $this->assertSame('u-1', $units->items[0]->uuid);
        $this->assertSame('Unit One', $units->items[0]->title);
        $this->assertSame('Cairo', $units->items[0]->city);
        $this->assertSame('1000', $units->items[0]->price);
        $this->assertSame(['a.png'], $units->items[0]->images);
        $this->assertSame(2, $units->meta?->total);
        $this->assertSame(1, $units->meta?->lastPage);
    }

    public function test_get_units_sends_params_as_query_string_with_bearer_token(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/units*' => Http::response(['units' => []]),
        ]);

        $this->app->make(WaffarhaClient::class)->units()->list(['page' => 2]);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/units')) {
                return false;
            }

            return $request->method() === 'GET'
                && str_contains($request->url(), 'page=2')
                && $request->body() === ''
                && $request->hasHeader('Authorization', 'Bearer access-1');
        });
    }

    public function test_get_unit_returns_a_typed_detail_from_the_singular_path(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/unit/u-1' => Http::response([
                'ResponseCode' => '200',
                'Result' => 'true',
                'ResponseMsg' => 'ok',
                'propetydetails' => [
                    'uuid' => 'u-1',
                    'title' => 'Unit One',
                    'property_title' => 'Apartment',
                    'city' => 'Cairo',
                    'price' => '1000',
                    'currency' => 'EGP',
                    'cleaning_fee' => '100',
                    'beds' => '5',
                    'bathroom' => '1',
                    'bedroom' => '1',
                    'plimit' => 5,
                    'minimum_days' => 4,
                    'self_check_in' => true,
                    'auto_confirm' => false,
                    'latitude' => '31.20',
                    'longtitude' => '29.91',
                    'images' => ['a.png'],
                    'average_price' => false,
                ],
                'house_descriptions' => [
                    ['category_name' => 'Calm', 'category_icon' => 'icon', 'descriptions' => [
                        ['description_id' => 1, 'description' => 'Serene', 'sort_order' => 1],
                    ]],
                ],
                'house_rules' => [],
                'house_safety' => [
                    ['id' => 1, 'name' => 'Carbon monoxide alarm', 'category_id' => 1, 'sort_order' => 1, 'category_name' => 'Safety devices', 'name_ar' => 'إنذار'],
                ],
                'amenities' => [
                    ['id' => 61, 'img' => 'wifi.png', 'title' => 'Wifi', 'title_ar' => 'واي فاي'],
                ],
                'every_corner_count' => [
                    ['category_id' => 4, 'category_name' => 'Bedroom 1', 'images' => [['id' => 309, 'img' => 'g.png']]],
                ],
                'reviewlist' => [],
                'total_review' => 0,
                'guest_cancellation_policy' => [
                    'id' => 1, 'name' => 'Flexible', 'display_name' => 'Flexible', 'short_description' => 'Flexible',
                    'descriptions' => [['id' => 1, 'description' => 'Full refund']],
                ],
                'host_cancellation_policies' => [
                    ['id' => 4, 'name' => '30 days', 'display_name' => '30 days', 'host_cancellation_enabled' => true, 'host_cancellation_notes' => null, 'custom_compensation_30_days' => '0.00', 'descriptions' => [['id' => 1, 'description' => 'E£100']]],
                ],
            ]),
        ]);

        $detail = $this->app->make(WaffarhaClient::class)->units()->get('u-1');

        $this->assertInstanceOf(UnitDetail::class, $detail);

        // Core property fields (note: currency, and longtitude → longitude).
        $this->assertSame('u-1', $detail->property->uuid);
        $this->assertSame('Unit One', $detail->property->title);
        $this->assertSame('EGP', $detail->property->currency);
        $this->assertSame('29.91', $detail->property->longitude);
        $this->assertSame(5, $detail->property->plimit);
        $this->assertTrue($detail->property->selfCheckIn);
        $this->assertFalse($detail->property->autoConfirm);
        $this->assertSame(['a.png'], $detail->property->images);
        $this->assertFalse($detail->property->get('average_price'));

        // Typed nested sections.
        $this->assertCount(1, $detail->houseDescriptions);
        $this->assertSame('Calm', $detail->houseDescriptions[0]->categoryName);
        $this->assertSame('Serene', $detail->houseDescriptions[0]->descriptions[0]->description);

        $this->assertCount(1, $detail->amenities);
        $this->assertSame('Wifi', $detail->amenities[0]->title);
        $this->assertSame('wifi.png', $detail->amenities[0]->image);

        $this->assertCount(1, $detail->houseSafety);
        $this->assertSame('Carbon monoxide alarm', $detail->houseSafety[0]->name);
        $this->assertSame('Safety devices', $detail->houseSafety[0]->categoryName);

        $this->assertCount(1, $detail->everyCornerCount);
        $this->assertSame('Bedroom 1', $detail->everyCornerCount[0]->categoryName);
        $this->assertSame('g.png', $detail->everyCornerCount[0]->images[0]->image);

        // Cancellation policies (typed descriptions; host-only fields populated).
        $this->assertSame('Flexible', $detail->guestCancellationPolicy?->displayName);
        $this->assertSame('Full refund', $detail->guestCancellationPolicy?->descriptions[0]->description);
        $this->assertCount(1, $detail->hostCancellationPolicies);
        $this->assertTrue($detail->hostCancellationPolicies[0]->hostCancellationEnabled);
        $this->assertSame('0.00', $detail->hostCancellationPolicies[0]->customCompensation30Days);

        $this->assertSame(0, $detail->totalReview);
    }

    public function test_a_401_triggers_one_refresh_and_retry_then_succeeds(): void
    {
        Http::fake([
            'maat.test/waffarha/oauth/token' => Http::sequence()
                ->push(['expires_in' => 3600, 'access_token' => 'access-1', 'refresh_token' => 'refresh-1'])
                ->push(['expires_in' => 3600, 'access_token' => 'access-2', 'refresh_token' => 'refresh-2']),
            'maat.test/waffarha/units*' => Http::sequence()
                ->push(['message' => 'Unauthenticated.'], 401)
                ->push(['units' => [['uuid' => 'u-1']]], 200),
        ]);

        $units = $this->app->make(WaffarhaClient::class)->units()->list();

        $this->assertCount(1, $units);
        // The retried request must carry the refreshed token.
        Http::assertSent(fn ($request) => str_contains($request->url(), '/units')
            && $request->hasHeader('Authorization', 'Bearer access-2'));
    }

    public function test_a_failed_response_throws_a_typed_request_exception(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/units*' => Http::response(['message' => 'boom'], 500),
        ]);

        try {
            $this->app->make(WaffarhaClient::class)->units()->list();
            $this->fail('Expected WaffarhaRequestException was not thrown.');
        } catch (WaffarhaRequestException $e) {
            $this->assertSame(500, $e->status);
            $this->assertStringContainsString('boom', (string) $e->body);
        }
    }

    public function test_missing_base_url_throws_a_configuration_exception(): void
    {
        config(['waffarha.base_url' => null]);
        $this->app->forgetInstance(WaffarhaClient::class);
        $this->app->forgetInstance(\Maat\Waffarha\Http\Transport::class);
        $this->app->forgetInstance(\Maat\Waffarha\Auth\TokenManager::class);

        $this->expectException(WaffarhaConfigurationException::class);
        $this->app->make(WaffarhaClient::class);
    }
}
