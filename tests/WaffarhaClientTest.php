<?php

declare(strict_types=1);

namespace Maat\Waffarha\Tests;

use Illuminate\Support\Facades\Http;
use Maat\Waffarha\Auth\TokenManager;
use Maat\Waffarha\Data\AvailabilityCheck;
use Maat\Waffarha\Data\Booking;
use Maat\Waffarha\Data\BookingCollection;
use Maat\Waffarha\Data\GuestBookDetails;
use Maat\Waffarha\Data\Payout;
use Maat\Waffarha\Data\PayoutCollection;
use Maat\Waffarha\Data\UnitCalendar;
use Maat\Waffarha\Data\UnitCollection;
use Maat\Waffarha\Data\UnitDetail;
use Maat\Waffarha\Data\WhatsAppContact;
use Maat\Waffarha\Exceptions\WaffarhaConfigurationException;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;
use Maat\Waffarha\Http\Transport;
use Maat\Waffarha\Resources\Bookings;
use Maat\Waffarha\Resources\Payouts;
use Maat\Waffarha\Resources\Units;
use Maat\Waffarha\Resources\WhatsApp;
use Maat\Waffarha\WaffarhaClient;

class WaffarhaClientTest extends TestCase
{
    public function test_units_accessor_returns_a_memoized_resource(): void
    {
        $client = $this->app->make(WaffarhaClient::class);

        $this->assertInstanceOf(Units::class, $client->units());
        $this->assertSame($client->units(), $client->units(), 'units() should return the same instance.');
    }

    public function test_bookings_accessor_returns_a_memoized_resource(): void
    {
        $client = $this->app->make(WaffarhaClient::class);

        $this->assertInstanceOf(Bookings::class, $client->bookings());
        $this->assertSame($client->bookings(), $client->bookings(), 'bookings() should return the same instance.');
    }

    public function test_payouts_accessor_returns_a_memoized_resource(): void
    {
        $client = $this->app->make(WaffarhaClient::class);

        $this->assertInstanceOf(Payouts::class, $client->payouts());
        $this->assertSame($client->payouts(), $client->payouts(), 'payouts() should return the same instance.');
    }

    public function test_whatsapp_accessor_returns_a_memoized_resource(): void
    {
        $client = $this->app->make(WaffarhaClient::class);

        $this->assertInstanceOf(WhatsApp::class, $client->whatsapp());
        $this->assertSame($client->whatsapp(), $client->whatsapp(), 'whatsapp() should return the same instance.');
    }

    public function test_whatsapp_get_returns_typed_contact_from_settings_phone(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/whatsapp' => Http::response([
                'ResponseCode' => '200',
                'Result' => 'true',
                'ResponseMsg' => 'WhatsApp contact retrieved successfully.',
                'whatsapp' => [
                    'phone_number' => '01044660885',
                    'phone_digits' => '201044660885',
                    'url' => 'https://wa.me/201044660885',
                    'deep_link' => 'https://api.whatsapp.com/send?phone=201044660885',
                ],
            ], 200),
        ]);

        $contact = $this->app->make(WaffarhaClient::class)->whatsapp()->get();

        $this->assertInstanceOf(WhatsAppContact::class, $contact);
        $this->assertSame('01044660885', $contact->phoneNumber);
        $this->assertSame('201044660885', $contact->phoneDigits);
        $this->assertSame('https://wa.me/201044660885', $contact->url);
        $this->assertSame('https://api.whatsapp.com/send?phone=201044660885', $contact->deepLink);
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

    public function test_list_bookings_returns_a_typed_collection(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/bookings*' => Http::response([
                'ResponseCode' => '200',
                'Result' => 'true',
                'ResponseMsg' => 'Waffarha bookings retrieved successfully.',
                'bookings' => [
                    [
                        'uuid' => 'b-1',
                        'provider_booking_id' => 'WAF-1',
                        'status' => 'Confirmed',
                        'total_amount' => '4500.00',
                        'currency' => 'EGP',
                        'guests_count' => 2,
                        'guest' => ['name' => 'Ahmed Mohamed', 'email' => 'ahmed@example.com'],
                    ],
                    ['uuid' => 'b-2', 'status' => 'Pending'],
                ],
                'pagination' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 2],
            ]),
        ]);

        $bookings = $this->app->make(WaffarhaClient::class)->bookings()->list(['per_page' => 20]);

        $this->assertInstanceOf(BookingCollection::class, $bookings);
        $this->assertCount(2, $bookings);
        $this->assertSame('b-1', $bookings->items[0]->uuid);
        $this->assertSame('Confirmed', $bookings->items[0]->status);
        $this->assertSame('4500.00', $bookings->items[0]->totalAmount);
        $this->assertSame(2, $bookings->items[0]->guestsCount);
        $this->assertSame('Ahmed Mohamed', $bookings->items[0]->guest?->name);
        $this->assertSame(2, $bookings->meta?->total);
    }

    public function test_list_bookings_sends_filters_as_query_string_with_bearer_token(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/bookings*' => Http::response(['bookings' => []]),
        ]);

        $this->app->make(WaffarhaClient::class)->bookings()->list([
            'status' => 'Confirmed',
            'check_in_from' => '2026-08-01',
            'check_in_to' => '2026-08-31',
        ]);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/bookings')) {
                return false;
            }

            return $request->method() === 'GET'
                && str_contains($request->url(), 'status=Confirmed')
                && str_contains($request->url(), 'check_in_from=2026-08-01')
                && str_contains($request->url(), 'check_in_to=2026-08-31')
                && $request->body() === ''
                && $request->hasHeader('Authorization', 'Bearer access-1');
        });
    }

    public function test_get_booking_returns_a_typed_booking_from_the_uuid_path(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/bookings/b-1' => Http::response([
                'id' => 'b-1',
                'property_id' => 'p-9',
                'property_title' => 'Beachfront Villa',
                'number_of_guests' => 3,
                'status' => 'Confirmed',
                'guest' => ['name' => 'Sara', 'phone' => '+201234567890'],
            ]),
        ]);

        $booking = $this->app->make(WaffarhaClient::class)->bookings()->get('b-1');

        $this->assertInstanceOf(Booking::class, $booking);
        // id → uuid, property_id → propertyUuid, number_of_guests → guestsCount.
        $this->assertSame('b-1', $booking->uuid);
        $this->assertSame('p-9', $booking->propertyUuid);
        $this->assertSame('Beachfront Villa', $booking->propertyTitle);
        $this->assertSame(3, $booking->guestsCount);
        $this->assertSame('Sara', $booking->guest?->name);
    }

    public function test_create_booking_sends_payload_as_json_body_and_returns_a_booking(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/bookings' => Http::response([
                'uuid' => 'b-new', 'provider_booking_id' => 'WAF-123456', 'status' => 'Pending',
            ]),
        ]);

        $booking = $this->app->make(WaffarhaClient::class)->bookings()->create([
            'provider_booking_id' => 'WAF-123456',
            'property_uuid' => 'b6d0b8d2',
            'check_in' => '2026-08-12',
            'check_out' => '2026-08-15',
            'guests_count' => 2,
            'total_amount' => 4500.00,
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertSame('b-new', $booking->uuid);
        $this->assertSame('WAF-123456', $booking->providerBookingId);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/bookings')
                && $request->method() === 'POST'
                && $request['provider_booking_id'] === 'WAF-123456'
                && $request->hasHeader('Authorization', 'Bearer access-1');
        });
    }

    /*
     * TEMPORARILY DISABLED: cancel/update endpoints are off on the Maat side
     * while the booking-state machine is being finalised, and the matching
     * SDK helpers have been removed from Resources/Bookings. Restore this
     * test verbatim alongside the helpers when the endpoints come back.
     *
     * // public function test_cancel_booking_sends_reason_in_the_body(): void
     * // {
     * //     $this->fakeToken();
     * //     Http::fake([
     * //         'maat.test/waffarha/bookings/b-1' => Http::response(['uuid' => 'b-1', 'status' => 'Cancelled']),
     * //     ]);
     * //
     * //     $booking = $this->app->make(WaffarhaClient::class)->bookings()->cancel('b-1', 'Guest no-show');
     * //
     * //     $this->assertInstanceOf(Booking::class, $booking);
     * //     $this->assertSame('Cancelled', $booking->status);
     * //
     * //     Http::assertSent(function ($request) {
     * //         return str_contains($request->url(), '/bookings/b-1')
     * //             && $request->method() === 'DELETE'
     * //             && $request['reason'] === 'Guest no-show';
     * //     });
     * // }
     */

    public function test_unit_calendar_returns_a_typed_calendar_iterable(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/unit/u-1/calendar*' => Http::response([
                'ResponseCode' => '200',
                'Result' => 'true',
                'ResponseMsg' => 'Calendar retrieved successfully.',
                'property_uuid' => 'u-1',
                'currency' => 'EGP',
                'base_price' => 1500.00,
                'window' => ['start_date' => '2026-08-01', 'end_date' => '2026-08-05', 'days' => 5],
                'linked_dates' => [
                    [
                        'id' => 42,
                        'name' => 'Eid Al-Adha',
                        'start_date' => '2026-08-04',
                        'end_date' => '2026-08-05',
                        'required_nights' => 2,
                        'message' => 'Must book the whole Eid Al-Adha holiday.',
                    ],
                ],
                'calendar' => [
                    ['date' => '2026-08-01', 'price' => 1500.00, 'currency' => 'EGP', 'available' => true,  'is_weekend' => false, 'reason' => null],
                    ['date' => '2026-08-02', 'price' => 1800.00, 'currency' => 'EGP', 'available' => true,  'is_weekend' => true,  'reason' => 'weekend_rate'],
                    ['date' => '2026-08-03', 'price' => 1500.00, 'currency' => 'EGP', 'available' => false, 'is_weekend' => false, 'reason' => 'booked'],
                    ['date' => '2026-08-04', 'price' => 2000.00, 'currency' => 'EGP', 'available' => true,  'is_weekend' => false, 'reason' => 'linked_date'],
                    ['date' => '2026-08-05', 'price' => 2000.00, 'currency' => 'EGP', 'available' => true,  'is_weekend' => false, 'reason' => 'linked_date'],
                ],
            ]),
        ]);

        $calendar = $this->app->make(WaffarhaClient::class)
            ->units()
            ->calendar('u-1', ['start_date' => '2026-08-01', 'end_date' => '2026-08-05']);

        $this->assertInstanceOf(UnitCalendar::class, $calendar);
        $this->assertSame('u-1', $calendar->propertyUuid);
        $this->assertSame('EGP', $calendar->currency);
        $this->assertSame(1500.00, $calendar->basePrice);
        $this->assertSame('2026-08-01', $calendar->startDate);
        $this->assertSame('2026-08-05', $calendar->endDate);
        $this->assertSame(5, $calendar->totalDays);
        $this->assertCount(5, $calendar);

        // Plain available day.
        $this->assertSame('2026-08-01', $calendar->days[0]->date);
        $this->assertSame(1500.00, $calendar->days[0]->price);
        $this->assertTrue($calendar->days[0]->available);
        $this->assertFalse($calendar->days[0]->isWeekend);
        $this->assertNull($calendar->days[0]->reason);

        // Weekend rate.
        $this->assertSame('weekend_rate', $calendar->days[1]->reason);
        $this->assertTrue($calendar->days[1]->isWeekend);

        // Booked day.
        $this->assertFalse($calendar->days[2]->available);
        $this->assertSame('booked', $calendar->days[2]->reason);

        // Linked-date day: still individually available, but flagged so the
        // partner UI can warn the guest before they pick the range. Match
        // by date range against the top-level linked_dates list.
        $this->assertTrue($calendar->days[3]->available);
        $this->assertSame('linked_date', $calendar->days[3]->reason);

        // Top-level linked_dates list carries the rule details.
        $this->assertCount(1, $calendar->linkedDates);
        $this->assertSame(42, $calendar->linkedDates[0]->id);
        $this->assertSame('Eid Al-Adha', $calendar->linkedDates[0]->name);
        $this->assertSame(2, $calendar->linkedDates[0]->requiredNights);
        $this->assertSame('Must book the whole Eid Al-Adha holiday.', $calendar->linkedDates[0]->message);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/unit/u-1/calendar')
            && $request->method() === 'GET'
            && $request['start_date'] === '2026-08-01'
            && $request['end_date'] === '2026-08-05'
            && $request->hasHeader('Authorization', 'Bearer access-1'));
    }

    public function test_check_availability_returns_a_typed_breakdown_when_available(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/unit/u-1/check' => Http::response([
                'ResponseCode' => '200',
                'Result' => 'true',
                'ResponseMsg' => 'That Date Range Available!',
                'available' => true,
                'property_uuid' => 'u-1',
                'booking_dates' => [
                    'check_in' => '2026-08-12',
                    'check_out' => '2026-08-15',
                    'total_days' => 3,
                    'normal_days' => 1,
                    'weekend_days' => 2,
                ],
                'property' => [
                    'uuid' => 'u-1',
                    'title' => 'Catalina Updated',
                    'image' => 'https://cdn.example.com/catalina.jpg',
                    'address' => '2FXH+75M, New Cairo 1',
                    'city' => 'New Cairo',
                    'beds' => 1,
                    'bathroom' => 2,
                ],
                'financial' => [
                    'currency' => 'EGP',
                    'subtotal' => 4500.00,
                    'cleaning_fee' => 250.00,
                    'access' => 100.00,
                    'host_tax_rate' => 14.00,
                    'tax_from_host' => 630.00,
                    'commission_percentage' => 1.00,
                    'commission_amount' => 45.00,
                    'total' => 5480.00,
                ],
                'special_rates_applied' => [
                    [
                        'id' => 15739,
                        'name' => 'Winter Promo',
                        'start_date' => '2026-08-12',
                        'end_date' => '2026-08-20',
                        'nightly_price_override' => 20.0,
                        'effective_nightly_price' => 1800.00,
                        'base_price' => 1500.00,
                        'is_increase' => true,
                        'is_discount' => false,
                        'is_premium' => true,
                        'discount_percentage' => null,
                        'increase_percentage' => 20.0,
                    ],
                ],
                'breakdown' => [
                    [
                        'date' => '2026-08-12',
                        'day_name_english' => 'Wednesday',
                        'day_name_arabic' => 'الأربعاء',
                        'is_weekend' => false,
                        'base_price' => 1500.00,
                        'price_after_special_rate' => 1500.00,
                        'price' => 1500.00,
                        'has_special_rate' => false,
                        'special_rate_id' => null,
                        'special_rate_name' => null,
                        'special_rate_percentage' => null,
                        'special_rate_is_increase' => null,
                        'is_discount' => false,
                        'is_premium' => false,
                        'discount_percentage' => null,
                        'increase_percentage' => null,
                        'weekend_percentage' => null,
                        'weekend_amount' => null,
                    ],
                    [
                        'date' => '2026-08-13',
                        'day_name_english' => 'Thursday',
                        'day_name_arabic' => 'الخميس',
                        'is_weekend' => true,
                        'base_price' => 1500.00,
                        'price_after_special_rate' => 1500.00,
                        'price' => 1500.00,
                        'has_special_rate' => false,
                        'special_rate_id' => null,
                        'special_rate_name' => null,
                        'special_rate_percentage' => null,
                        'special_rate_is_increase' => null,
                        'is_discount' => false,
                        'is_premium' => false,
                        'discount_percentage' => null,
                        'increase_percentage' => null,
                        'weekend_percentage' => null,
                        'weekend_amount' => null,
                    ],
                    [
                        'date' => '2026-08-14',
                        'day_name_english' => 'Friday',
                        'day_name_arabic' => 'الجمعة',
                        'is_weekend' => true,
                        'base_price' => 1500.00,
                        'price_after_special_rate' => 1800.00,
                        'price' => 1500.00,
                        'has_special_rate' => true,
                        'special_rate_id' => 15739,
                        'special_rate_name' => 'Winter Promo',
                        'special_rate_percentage' => 20.0,
                        'special_rate_is_increase' => true,
                        'is_discount' => false,
                        'is_premium' => true,
                        'discount_percentage' => null,
                        'increase_percentage' => 20.0,
                        'weekend_percentage' => 10.0,
                        'weekend_amount' => 180.0,
                    ],
                ],
            ]),
        ]);

        $check = $this->app->make(WaffarhaClient::class)
            ->units()
            ->checkAvailability('u-1', [
                'check_in' => '2026-08-12',
                'check_out' => '2026-08-15',
                'guests_count' => 2,
            ]);

        $this->assertInstanceOf(AvailabilityCheck::class, $check);
        $this->assertTrue($check->available);
        $this->assertSame('u-1', $check->propertyUuid);
        $this->assertSame('2026-08-12', $check->checkIn);
        $this->assertSame('2026-08-15', $check->checkOut);
        $this->assertSame(3, $check->nights);
        $this->assertSame(3, $check->bookingDates->totalDays);
        $this->assertSame(1, $check->bookingDates->normalDays);
        $this->assertSame(2, $check->bookingDates->weekendDays);
        $this->assertSame('EGP', $check->currency);
        $this->assertSame(4500.00, $check->subtotal);
        $this->assertSame(250.00, $check->cleaningFee);
        $this->assertSame(100.00, $check->access);
        $this->assertSame(14.00, $check->hostTaxRate);
        $this->assertSame(630.00, $check->taxFromHost);
        $this->assertSame(5480.00, $check->total);
        $this->assertSame(1.00, $check->commissionPercentage);
        $this->assertSame(45.00, $check->commissionAmount);
        $this->assertSame('EGP', $check->financial->currency);
        $this->assertSame(100.00, $check->financial->access);
        $this->assertSame(630.00, $check->financial->taxFromHost);
        $this->assertSame(45.00, $check->financial->commissionAmount);
        $this->assertNotNull($check->property);
        $this->assertSame('u-1', $check->property->uuid);
        $this->assertSame('Catalina Updated', $check->property->title);
        $this->assertSame('New Cairo', $check->property->city);
        $this->assertSame(1, $check->property->beds);
        $this->assertSame(2, $check->property->bathroom);
        $this->assertCount(3, $check);
        $this->assertSame(1500.00, $check->breakdown[0]->price);
        $this->assertSame('Wednesday', $check->breakdown[0]->dayNameEnglish);
        $this->assertSame('الأربعاء', $check->breakdown[0]->dayNameArabic);
        $this->assertSame(1500.00, $check->breakdown[0]->basePrice);
        $this->assertNull($check->breakdown[0]->specialRateId);
        $this->assertTrue($check->breakdown[2]->isWeekend);
        $this->assertTrue($check->breakdown[2]->hasSpecialRate);
        $this->assertSame(15739, $check->breakdown[2]->specialRateId);
        $this->assertSame('Winter Promo', $check->breakdown[2]->specialRateName);
        $this->assertSame(20.0, $check->breakdown[2]->specialRatePercentage);
        $this->assertTrue($check->breakdown[2]->isPremium);
        $this->assertSame(10.0, $check->breakdown[2]->weekendPercentage);
        $this->assertSame(180.0, $check->breakdown[2]->weekendAmount);

        $this->assertCount(1, $check->specialRatesApplied);
        $this->assertSame(15739, $check->specialRatesApplied[0]->id);
        $this->assertSame('Winter Promo', $check->specialRatesApplied[0]->name);
        $this->assertSame(20.0, $check->specialRatesApplied[0]->nightlyPriceOverride);
        $this->assertSame(1800.00, $check->specialRatesApplied[0]->effectiveNightlyPrice);
        $this->assertSame(1500.00, $check->specialRatesApplied[0]->basePrice);
        $this->assertTrue($check->specialRatesApplied[0]->isPremium);
        $this->assertSame(20.0, $check->specialRatesApplied[0]->increasePercentage);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/unit/u-1/check')
            && $request->method() === 'POST'
            && $request['check_in'] === '2026-08-12'
            && $request['check_out'] === '2026-08-15'
            && $request['guests_count'] === 2);
    }

    public function test_check_availability_parses_legacy_top_level_money_fields_for_backwards_compat(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/unit/u-1/check' => Http::response([
                'available' => true,
                'property_uuid' => 'u-1',
                'check_in' => '2026-08-12',
                'check_out' => '2026-08-13',
                'nights' => 1,
                'currency' => 'EGP',
                'subtotal' => 1500.00,
                'breakdown' => [
                    ['date' => '2026-08-12', 'price' => 1500.00, 'is_weekend' => false, 'has_special_rate' => false],
                ],
            ]),
        ]);

        $check = $this->app->make(WaffarhaClient::class)
            ->units()
            ->checkAvailability('u-1', ['check_in' => '2026-08-12', 'check_out' => '2026-08-13']);

        $this->assertSame(1500.00, $check->subtotal);
        $this->assertNull($check->cleaningFee);
        $this->assertSame(1500.00, $check->total);
        $this->assertNull($check->commissionPercentage);
        $this->assertNull($check->commissionAmount);
        $this->assertNull($check->property);

        $this->assertSame('2026-08-12', $check->bookingDates->checkIn);
        $this->assertSame('2026-08-13', $check->bookingDates->checkOut);
        $this->assertSame(1, $check->bookingDates->totalDays);
        $this->assertNull($check->bookingDates->normalDays);
        $this->assertNull($check->bookingDates->weekendDays);
        $this->assertSame([], $check->specialRatesApplied);
    }

    public function test_check_availability_financial_total_fallback_excludes_commission(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/unit/u-1/check' => Http::response([
                'available' => true,
                'property_uuid' => 'u-1',
                'booking_dates' => [
                    'check_in' => '2026-08-12', 'check_out' => '2026-08-13',
                    'total_days' => 1, 'normal_days' => 1, 'weekend_days' => 0,
                ],
                'financial' => [
                    'currency' => 'EGP',
                    'subtotal' => 1500.00,
                    'cleaning_fee' => 250.00,
                    'commission_percentage' => 1.00,
                    'commission_amount' => 15.00,
                    // `total` intentionally omitted so the SDK has to derive it —
                    // commission must NOT be folded in (same convention as
                    // v1/u_simulate_booking).
                ],
                'breakdown' => [
                    ['date' => '2026-08-12', 'price' => 1500.00],
                ],
            ]),
        ]);

        $check = $this->app->make(WaffarhaClient::class)
            ->units()
            ->checkAvailability('u-1', ['check_in' => '2026-08-12', 'check_out' => '2026-08-13']);

        $this->assertSame(1750.00, $check->total);
        $this->assertSame(1750.00, $check->financial->total);
        $this->assertSame(15.00, $check->commissionAmount);
    }

    public function test_check_availability_throws_a_typed_request_exception_on_409(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/unit/u-1/check' => Http::response([
                'ResponseCode' => '409',
                'Result' => 'false',
                'available' => false,
                'reason' => 'booking_overlap',
                'ResponseMsg' => 'That Date Range Already Booked!',
            ], 409),
        ]);

        try {
            $this->app->make(WaffarhaClient::class)
                ->units()
                ->checkAvailability('u-1', ['check_in' => '2026-08-12', 'check_out' => '2026-08-15']);
            $this->fail('Expected WaffarhaRequestException was not thrown.');
        } catch (WaffarhaRequestException $e) {
            $this->assertSame(409, $e->status);
            $this->assertIsString($e->body);
            $decoded = json_decode((string) $e->body, true);
            $this->assertIsArray($decoded);
            $this->assertSame('booking_overlap', $decoded['reason'] ?? null);
        }
    }

    public function test_book_details_returns_guest_receipt_payload(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/book_details' => Http::response([
                'bookdetails' => [
                    'currency' => 'EGP',
                    'uuid' => 'b-1',
                    'title' => 'Catalina Updated',
                    'check_in' => '2026-08-12',
                    'check_out' => '2026-08-15',
                    'total_day' => 3,
                    'guest_name' => 'Ahmed Mohamed',
                    'subtotal' => 4500.00,
                    'cleaning_fee' => 250.00,
                    'access' => 100.00,
                    'host_tax_rate' => 14.00,
                    'tax_from_host' => 630.00,
                    'total' => 5480.00,
                    'day_breakdown' => [
                        ['date' => '2026-08-12', 'price' => 1500.00],
                    ],
                    'financial_summary' => [
                        'currency' => 'EGP',
                        'total_amount' => 5480.00,
                        'access' => 100.00,
                        'tax_from_host' => 630.00,
                    ],
                ],
                'ResponseCode' => '200',
                'Result' => 'true',
                'ResponseMsg' => 'Book Property Details Founded!',
            ], 200),
        ]);

        $receipt = $this->app->make(WaffarhaClient::class)
            ->bookings()
            ->bookDetails('b-1');

        $this->assertInstanceOf(GuestBookDetails::class, $receipt);
        $this->assertSame('EGP', $receipt->currency);
        $this->assertSame('b-1', $receipt->uuid);
        $this->assertSame(4500.00, $receipt->subtotal);
        $this->assertSame(100.00, $receipt->access);
        $this->assertSame(630.00, $receipt->taxFromHost);
        $this->assertSame(5480.00, $receipt->total);
        $this->assertSame('Ahmed Mohamed', $receipt->guestName);
        $this->assertSame('Ahmed Mohamed', $receipt->get('guest_name'));
    }

    public function test_preview_returns_booking_shaped_payload_like_create(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/bookings/preview' => Http::response([
                'ResponseCode' => '200',
                'Result' => 'true',
                'ResponseMsg' => 'Booking preview computed successfully.',
                'booking' => [
                    'id' => null,
                    'provider' => 'Waffarha',
                    'status' => null,
                    'check_in' => '2026-08-12',
                    'check_out' => '2026-08-15',
                    'total_days' => 3,
                    'guests_count' => 2,
                    'total_amount' => 5480.00,
                    'currency' => 'EGP',
                    'financial' => [
                        'currency' => 'EGP',
                        'subtotal' => 4500.00,
                        'cleaning_fee' => 250.00,
                        'access' => 100.00,
                        'host_tax_rate' => 14.00,
                        'tax_from_host' => 630.00,
                        'total' => 5480.00,
                    ],
                    'property' => [
                        'uuid' => 'u-1',
                        'title' => 'Catalina Updated',
                        'city' => 'Cairo',
                    ],
                    'guest' => ['name' => 'Ahmed Mohamed'],
                    'breakdown' => [
                        ['date' => '2026-08-12', 'price' => 1500.00],
                    ],
                ],
            ], 200),
        ]);

        $preview = $this->app->make(WaffarhaClient::class)
            ->bookings()
            ->preview([
                'property_uuid' => 'u-1',
                'check_in' => '2026-08-12',
                'check_out' => '2026-08-15',
                'guests_count' => 2,
                'guest' => ['name' => 'Ahmed Mohamed'],
            ]);

        $this->assertInstanceOf(Booking::class, $preview);
        $this->assertSame('EGP', $preview->currency);
        $this->assertSame('5480', $preview->totalAmount);
        $this->assertSame('u-1', $preview->propertyUuid);
        $this->assertSame(100.00, $preview->financial?->access);
        $this->assertSame(630.00, $preview->financial?->taxFromHost);
        $this->assertSame(5480.00, $preview->financial?->total);
        $this->assertSame('Ahmed Mohamed', $preview->guest?->name);
    }

    public function test_a_failed_bookings_response_throws_a_typed_request_exception(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/bookings*' => Http::response(['message' => 'boom'], 500),
        ]);

        try {
            $this->app->make(WaffarhaClient::class)->bookings()->list();
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
        $this->app->forgetInstance(Transport::class);
        $this->app->forgetInstance(TokenManager::class);

        $this->expectException(WaffarhaConfigurationException::class);
        $this->app->make(WaffarhaClient::class);
    }

    public function test_payouts_list_parses_envelope_into_collection(): void
    {
        $this->fakeToken();
        Http::fake([
            'maat.test/waffarha/payouts*' => Http::response([
                'ResponseCode' => '200',
                'Result' => 'true',
                'ResponseMsg' => 'Payouts retrieved successfully.',
                'payouts' => [
                    [
                        'uuid' => '1a2b3c4d-5e6f-7890-abcd-ef1234567890',
                        'booking' => ['uuid' => 'uuid-100'],
                        'amount' => 4500.00,
                        'currency' => 'EGP',
                        'status' => 'pending',
                        'status_label' => 'Pending',
                        'proof_url' => null,
                        'proof_type' => null,
                        'provider_notes' => null,
                        'rejection_reason' => null,
                        'proof_submitted_at' => null,
                        'reviewed_at' => null,
                        'created_at' => '2026-06-12 09:00:01',
                        'updated_at' => '2026-06-12 09:00:01',
                    ],
                ],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 50,
                    'total' => 1,
                ],
            ]),
        ]);

        $payouts = $this->app->make(WaffarhaClient::class)->payouts()->list(['status' => 'pending']);

        $this->assertInstanceOf(PayoutCollection::class, $payouts);
        $this->assertCount(1, $payouts);
        $this->assertSame('1a2b3c4d-5e6f-7890-abcd-ef1234567890', $payouts->items[0]->uuid);
        $this->assertSame('uuid-100', $payouts->items[0]->bookingUuid);
        $this->assertSame(4500.0, $payouts->items[0]->amount);
        $this->assertSame('pending', $payouts->items[0]->status);
        $this->assertSame(1, $payouts->meta?->total);

        Http::assertSent(static function ($request): bool {
            return str_contains((string) $request->url(), 'waffarha/payouts')
                && str_contains((string) $request->url(), 'status=pending');
        });
    }

    public function test_payouts_get_unwraps_the_payout_envelope(): void
    {
        $this->fakeToken();
        $uuid = '1a2b3c4d-5e6f-7890-abcd-ef1234567890';
        Http::fake([
            "maat.test/waffarha/payouts/{$uuid}" => Http::response([
                'ResponseCode' => '200',
                'Result' => 'true',
                'ResponseMsg' => 'Payout retrieved successfully.',
                'payout' => [
                    'uuid' => $uuid,
                    'booking' => ['uuid' => 'uuid-100'],
                    'amount' => 2500.00,
                    'currency' => 'EGP',
                    'status' => 'proof_submitted',
                    'status_label' => 'Proof Submitted',
                    'proof_url' => 'https://cdn.example/proof.pdf',
                ],
            ]),
        ]);

        // The single-payout endpoints return `{ "ResponseCode": ..., "payout":
        // { ... } }`. Payout::fromArray() unwraps the `payout` key
        // transparently so consumers get typed properties either way.
        $payout = $this->app->make(WaffarhaClient::class)->payouts()->get($uuid);

        $this->assertInstanceOf(Payout::class, $payout);
        $this->assertSame($uuid, $payout->uuid);
        $this->assertSame('uuid-100', $payout->bookingUuid);
        $this->assertSame('proof_submitted', $payout->status);
        $this->assertSame('https://cdn.example/proof.pdf', $payout->proofUrl);
    }

    public function test_payouts_submit_proof_sends_multipart_request(): void
    {
        $this->fakeToken();
        $uuid = '1a2b3c4d-5e6f-7890-abcd-ef1234567890';
        Http::fake([
            "maat.test/waffarha/payouts/{$uuid}/proof" => Http::response([
                'ResponseCode' => '200',
                'Result' => 'true',
                'ResponseMsg' => 'Proof uploaded successfully.',
                'payout' => [
                    'uuid' => $uuid,
                    'booking' => ['uuid' => 'uuid-100'],
                    'amount' => 2500.00,
                    'currency' => 'EGP',
                    'status' => 'proof_submitted',
                    'status_label' => 'Proof Submitted',
                    'proof_url' => 'https://cdn.example/proof.pdf',
                    'proof_type' => 'file',
                    'provider_notes' => 'WAF-#1278',
                ],
            ]),
        ]);

        $tmp = tempnam(sys_get_temp_dir(), 'waffarha_proof_');
        file_put_contents($tmp, '%PDF-1.4 fake content');

        try {
            $payout = $this->app->make(WaffarhaClient::class)
                ->payouts()
                ->submitProof($uuid, $tmp, 'WAF-#1278');

            $this->assertSame('proof_submitted', $payout->status);
            $this->assertSame('https://cdn.example/proof.pdf', $payout->proofUrl);

            Http::assertSent(static function ($request) use ($uuid): bool {
                $contentType = $request->header('Content-Type')[0] ?? '';
                $body = (string) $request->body();

                return str_contains((string) $request->url(), "waffarha/payouts/{$uuid}/proof")
                    && str_starts_with($contentType, 'multipart/form-data')
                    && str_contains($body, 'name="proof"')
                    && str_contains($body, 'name="notes"')
                    && str_contains($body, 'WAF-#1278');
            });
        } finally {
            @unlink($tmp);
        }
    }
}
