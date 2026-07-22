<?php

declare(strict_types=1);

namespace Maat\Waffarha\Tests;

use Maat\Waffarha\Data\AvailabilityCheck;
use Maat\Waffarha\Data\AvailabilityFinancial;
use Maat\Waffarha\Data\Booking;
use Maat\Waffarha\Data\BookingCollection;
use Maat\Waffarha\Data\BookingFinancial;
use Maat\Waffarha\Data\CityFolder;
use Maat\Waffarha\Data\CityFolderCollection;
use Maat\Waffarha\Data\CityFolderUnits;
use Maat\Waffarha\Data\Facility;
use Maat\Waffarha\Data\FacilityCollection;
use Maat\Waffarha\Data\FacilityGroup;
use Maat\Waffarha\Data\OrphanGap;
use Maat\Waffarha\Data\Payout;
use Maat\Waffarha\Data\PayoutCollection;
use Maat\Waffarha\Data\TokenResponse;
use Maat\Waffarha\Data\Unit;
use Maat\Waffarha\Data\UnitCalendar;
use Maat\Waffarha\Data\UnitCalendarDay;
use Maat\Waffarha\Data\UnitCollection;
use Maat\Waffarha\Data\WhatsAppContact;
use PHPUnit\Framework\TestCase as BaseTestCase;

class DataTest extends BaseTestCase
{
    public function test_unit_maps_real_fields_and_keeps_raw_attributes(): void
    {
        $unit = Unit::fromArray([
            'uuid' => '4e2248c9',
            'title' => 'Fantastic holiday',
            'city' => 'Alexandria',
            'images' => ['cover.png'],
            'price' => '1000',
            'price_currency' => 'EGP',
            'base_price' => '1000',
        ]);

        $this->assertSame('4e2248c9', $unit->uuid);
        $this->assertSame('Fantastic holiday', $unit->title);
        $this->assertSame('Alexandria', $unit->city);
        $this->assertSame(['cover.png'], $unit->images);
        $this->assertSame('1000', $unit->price);
        $this->assertSame('EGP', $unit->priceCurrency);
        // Non-promoted fields survive in the raw bag.
        $this->assertSame('1000', $unit->get('base_price'));
        $this->assertNull($unit->get('missing'));
    }

    public function test_unit_falls_back_to_id_for_uuid(): void
    {
        $unit = Unit::fromArray(['id' => 42, 'title' => 'Unit']);

        $this->assertSame('42', $unit->uuid);
        $this->assertSame('Unit', $unit->title);
    }

    public function test_collection_reads_units_and_pagination(): void
    {
        $collection = UnitCollection::fromArray([
            'units' => [['uuid' => 'a', 'title' => 'A'], ['uuid' => 'b']],
            'pagination' => ['total' => 2, 'current_page' => 1, 'last_page' => 5, 'next_page_url' => 'https://x/?page=2'],
        ]);

        $this->assertCount(2, $collection);
        $this->assertSame('a', $collection->items[0]->uuid);
        $this->assertSame('A', $collection->items[0]->title);
        $this->assertSame(2, $collection->meta?->total);
        $this->assertSame(5, $collection->meta?->lastPage);
        $this->assertSame('https://x/?page=2', $collection->meta?->nextPageUrl);
    }

    public function test_collection_is_empty_when_units_key_is_absent(): void
    {
        $collection = UnitCollection::fromArray(['ResponseCode' => '200', 'Result' => 'true']);

        $this->assertCount(0, $collection);
        $this->assertNull($collection->meta);
    }

    public function test_booking_maps_create_payload_keys_and_keeps_raw_attributes(): void
    {
        $booking = Booking::fromArray([
            'uuid' => 'b-1',
            'provider' => 'waffarha',
            'provider_booking_id' => 'WAF-1',
            'property_uuid' => 'prop-9',
            'check_in' => '2026-08-12',
            'check_out' => '2026-08-15',
            'guests_count' => 2,
            'total_amount' => '4500.00',
            'currency' => 'EGP',
            'notes' => 'Late arrival.',
            'guest' => ['name' => 'Ahmed', 'passport_number' => 'A12345678'],
        ]);

        $this->assertSame('b-1', $booking->uuid);
        $this->assertSame('waffarha', $booking->provider);
        $this->assertSame('WAF-1', $booking->providerBookingId);
        $this->assertSame('prop-9', $booking->propertyUuid);
        $this->assertSame(2, $booking->guestsCount);
        $this->assertSame('4500.00', $booking->totalAmount);
        $this->assertSame('Ahmed', $booking->guest?->name);
        $this->assertSame('A12345678', $booking->guest?->passportNumber);
        // Non-promoted fields survive in the raw bag.
        $this->assertSame('Late arrival.', $booking->get('notes'));
        $this->assertNull($booking->get('missing'));
    }

    public function test_booking_falls_back_to_webhook_keys(): void
    {
        $booking = Booking::fromArray([
            'id' => 'b-2',
            'property_id' => 'prop-7',
            'number_of_guests' => 4,
            'status' => 'Confirmed',
        ]);

        // id → uuid, property_id → propertyUuid, number_of_guests → guestsCount.
        $this->assertSame('b-2', $booking->uuid);
        $this->assertSame('prop-7', $booking->propertyUuid);
        $this->assertSame(4, $booking->guestsCount);
        $this->assertSame('Confirmed', $booking->status);
        $this->assertNull($booking->guest);
    }

    public function test_booking_collection_reads_bookings_and_pagination(): void
    {
        $collection = BookingCollection::fromArray([
            'bookings' => [['uuid' => 'b-1', 'status' => 'Confirmed'], ['id' => 'b-2']],
            'pagination' => ['total' => 2, 'current_page' => 1],
        ]);

        $this->assertCount(2, $collection);
        $this->assertSame('b-1', $collection->items[0]->uuid);
        $this->assertSame('b-2', $collection->items[1]->uuid);
        $this->assertSame(2, $collection->meta?->total);
    }

    public function test_booking_collection_tolerates_a_bare_list_and_missing_wrapper(): void
    {
        $fromList = BookingCollection::fromArray([['uuid' => 'b-1'], ['uuid' => 'b-2']]);
        $this->assertCount(2, $fromList);
        $this->assertNull($fromList->meta);

        $empty = BookingCollection::fromArray(['ResponseCode' => '200', 'Result' => 'true']);
        $this->assertCount(0, $empty);
    }

    public function test_token_response_defaults_token_type_and_nullable_refresh(): void
    {
        $token = TokenResponse::fromArray(['access_token' => 'abc', 'expires_in' => 60]);

        $this->assertSame('abc', $token->accessToken);
        $this->assertSame(60, $token->expiresIn);
        $this->assertSame('Bearer', $token->tokenType);
        $this->assertNull($token->refreshToken);
    }

    public function test_unit_calendar_day_promotes_new_availability_flags(): void
    {
        $day = UnitCalendarDay::fromArray([
            'date' => '2026-08-12',
            'price' => 1500.50,
            'currency' => 'EGP',
            'available' => false,
            'is_booked' => true,
            'available_for_checkin' => false,
            'available_for_checkout' => true,
            'is_weekend' => false,
            'reason' => 'booked',
        ]);

        $this->assertSame('2026-08-12', $day->date);
        $this->assertSame(1500.50, $day->price);
        $this->assertSame('EGP', $day->currency);
        $this->assertFalse($day->available);
        $this->assertTrue($day->isBooked);
        $this->assertFalse($day->availableForCheckin);
        $this->assertTrue($day->availableForCheckout);
        $this->assertFalse($day->isWeekend);
        $this->assertSame('booked', $day->reason);
    }

    public function test_unit_calendar_day_handles_legacy_payload_without_new_flags(): void
    {
        $day = UnitCalendarDay::fromArray([
            'date' => '2026-08-12',
            'price' => 1500,
            'currency' => 'EGP',
            'available' => true,
            'is_weekend' => true,
            'reason' => 'weekend_rate',
        ]);

        $this->assertTrue($day->available);
        $this->assertNull($day->isBooked);
        $this->assertNull($day->availableForCheckin);
        $this->assertNull($day->availableForCheckout);
        $this->assertTrue($day->isWeekend);
    }

    public function test_unit_calendar_parses_blocklist_orphan_gaps_and_same_day_booking(): void
    {
        $calendar = UnitCalendar::fromArray([
            'property_uuid' => 'prop-9',
            'currency' => 'EGP',
            'base_price' => '1500',
            'window' => ['start_date' => '2026-08-01', 'end_date' => '2026-08-05', 'days' => 5],
            'calendar' => [
                ['date' => '2026-08-01', 'available' => true, 'is_booked' => false, 'available_for_checkin' => true, 'available_for_checkout' => true],
            ],
            'blocklist' => ['2026-08-10', '2026-08-11'],
            'orphan_gaps' => [
                ['start_date' => '2026-04-27', 'end_date' => '2026-04-28', 'gap_nights' => 1, 'base_minimum_stay' => 2, 'dynamic_minimum_nights' => 1],
            ],
            'same_day_booking' => true,
        ]);

        $this->assertSame('prop-9', $calendar->propertyUuid);
        $this->assertSame('EGP', $calendar->currency);
        $this->assertSame(1500.0, $calendar->basePrice);
        $this->assertSame('2026-08-01', $calendar->startDate);
        $this->assertSame('2026-08-05', $calendar->endDate);
        $this->assertSame(5, $calendar->totalDays);

        $this->assertCount(1, $calendar);
        $this->assertSame(['2026-08-10', '2026-08-11'], $calendar->blocklist);
        $this->assertTrue($calendar->sameDayBooking);

        $this->assertCount(1, $calendar->orphanGaps);
        $gap = $calendar->orphanGaps[0];
        $this->assertInstanceOf(OrphanGap::class, $gap);
        $this->assertSame('2026-04-27', $gap->startDate);
        $this->assertSame('2026-04-28', $gap->endDate);
        $this->assertSame(1, $gap->gapNights);
        $this->assertSame(2, $gap->baseMinimumStay);
        $this->assertSame(1, $gap->dynamicMinimumNights);
    }

    public function test_unit_calendar_defaults_new_top_level_fields_when_missing(): void
    {
        $calendar = UnitCalendar::fromArray([
            'property_uuid' => 'prop-9',
            'currency' => 'EGP',
            'base_price' => 1500,
            'window' => ['start_date' => '2026-08-01', 'end_date' => '2026-08-05', 'days' => 5],
            'calendar' => [['date' => '2026-08-01']],
        ]);

        $this->assertSame([], $calendar->blocklist);
        $this->assertSame([], $calendar->orphanGaps);
        $this->assertNull($calendar->sameDayBooking);
        $this->assertNull($calendar->baseMinimumStay);
        $this->assertSame([], $calendar->minimumStayOverrides);
    }

    public function test_unit_calendar_parses_minimum_stay_overrides(): void
    {
        $calendar = UnitCalendar::fromArray([
            'property_uuid' => 'prop-9',
            'currency' => 'EGP',
            'base_price' => 1500,
            'window' => ['start_date' => '2026-08-01', 'end_date' => '2026-08-05', 'days' => 5],
            'calendar' => [['date' => '2026-08-01']],
            'base_minimum_stay' => 2,
            'minimum_stay_overrides' => [
                [
                    'start_date' => '2026-08-10',
                    'end_date' => '2026-08-20',
                    'minimum_nights' => 3,
                    'base_minimum_stay' => 2,
                    'effective_minimum_nights' => 3,
                ],
            ],
        ]);

        $this->assertSame(2, $calendar->baseMinimumStay);
        $this->assertCount(1, $calendar->minimumStayOverrides);
        $override = $calendar->minimumStayOverrides[0];
        $this->assertSame('2026-08-10', $override->startDate);
        $this->assertSame('2026-08-20', $override->endDate);
        $this->assertSame(3, $override->minimumNights);
        $this->assertSame(2, $override->baseMinimumStay);
        $this->assertSame(3, $override->effectiveMinimumNights);
    }

    public function test_availability_financial_parses_long_stay_and_service_fee(): void
    {
        $financial = AvailabilityFinancial::fromArray([
            'currency' => 'EGP',
            'base_price' => 1500,
            'subtotal' => 4500,
            'long_stay_discount' => 450,
            'long_stay_applied' => true,
            'discount_percentage' => 10,
            'discount_amount' => 405,
            'subtotal_after_discount' => 3645,
            'cleaning_fee' => 250,
            'access' => 100,
            'service_fee' => 50,
            'tax_rate' => 14,
            'tax' => 7,
            'host_tax_rate' => 14,
            'tax_from_host' => 630,
            'commission_percentage' => 1,
            'commission_amount' => 40.5,
            'total' => 4632,
        ]);

        $this->assertSame(1500.0, $financial->basePrice);
        $this->assertSame(450.0, $financial->longStayDiscount);
        $this->assertTrue($financial->longStayApplied);
        $this->assertSame(50.0, $financial->serviceFee);
        $this->assertSame(14.0, $financial->taxRate);
        $this->assertSame(7.0, $financial->tax);
        $this->assertSame(4632.0, $financial->total);
    }

    public function test_booking_financial_parses_new_fee_fields(): void
    {
        $financial = BookingFinancial::fromArray([
            'currency' => 'EGP',
            'subtotal' => 4500,
            'long_stay_discount' => 200,
            'long_stay_applied' => true,
            'cleaning_fee' => 250,
            'access' => 100,
            'service_fee' => 50,
            'tax_rate' => 14,
            'tax' => 7,
            'host_tax_rate' => 14,
            'tax_from_host' => 630,
            'total' => 5337,
        ]);

        $this->assertSame(200.0, $financial->longStayDiscount);
        $this->assertTrue($financial->longStayApplied);
        $this->assertSame(50.0, $financial->serviceFee);
        $this->assertSame(14.0, $financial->taxRate);
        $this->assertSame(7.0, $financial->tax);
        $this->assertSame(5337.0, $financial->total);
    }

    public function test_availability_check_parses_xuru_and_minimum_stay_metadata(): void
    {
        $check = AvailabilityCheck::fromArray([
            'available' => true,
            'property_uuid' => 'prop-9',
            'is_xuru_unit' => true,
            'xuru_status' => true,
            'xuru_price_applied' => true,
            'effective_minimum_stay' => 3,
            'booking_dates' => [
                'check_in' => '2026-08-12',
                'check_out' => '2026-08-15',
                'total_days' => 3,
            ],
            'financial' => [
                'currency' => 'EGP',
                'subtotal' => 4500,
                'service_fee' => 50,
                'tax' => 7,
                'cleaning_fee' => 0,
                'access' => 0,
                'host_tax_rate' => 0,
                'tax_from_host' => 0,
                'total' => 4557,
            ],
            'property' => [
                'uuid' => 'prop-9',
                'title' => 'Unit',
                'minimum_days' => 2,
            ],
            'breakdown' => [],
        ]);

        $this->assertTrue($check->isXuruUnit);
        $this->assertTrue($check->xuruStatus);
        $this->assertTrue($check->xuruPriceApplied);
        $this->assertSame(3, $check->effectiveMinimumStay);
        $this->assertSame(50.0, $check->serviceFee);
        $this->assertSame(7.0, $check->tax);
        $this->assertSame(2, $check->property?->minimumDays);
    }

    public function test_payout_promotes_fields_and_keeps_booking_envelope(): void
    {
        $payout = Payout::fromArray([
            'uuid' => '1a2b3c4d-5e6f-7890-abcd-ef1234567890',
            'booking' => ['uuid' => 'b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d'],
            'amount' => 4500.00,
            'currency' => 'EGP',
            'status' => 'proof_submitted',
            'status_label' => 'Proof Submitted',
            'proof_url' => 'https://cdn.example.com/p.pdf',
            'proof_type' => 'file',
            'provider_notes' => 'WAF-#1278',
            'rejection_reason' => null,
            'proof_submitted_at' => '2026-06-13 17:45:12',
            'reviewed_at' => null,
            'created_at' => '2026-06-12 09:00:01',
            'updated_at' => '2026-06-13 17:45:12',
            'extra_field' => 'kept',
        ]);

        $this->assertSame('1a2b3c4d-5e6f-7890-abcd-ef1234567890', $payout->uuid);
        $this->assertSame('b6d0b8d2-9c5e-4f1a-9c2a-7a4b8e3f1a0d', $payout->bookingUuid);
        $this->assertSame(4500.0, $payout->amount);
        $this->assertSame('EGP', $payout->currency);
        $this->assertSame('proof_submitted', $payout->status);
        $this->assertSame('Proof Submitted', $payout->statusLabel);
        $this->assertSame('https://cdn.example.com/p.pdf', $payout->proofUrl);
        $this->assertSame('file', $payout->proofType);
        $this->assertSame('WAF-#1278', $payout->providerNotes);
        $this->assertNull($payout->rejectionReason);
        $this->assertSame('2026-06-13 17:45:12', $payout->proofSubmittedAt);
        $this->assertNull($payout->reviewedAt);
        $this->assertSame('2026-06-12 09:00:01', $payout->createdAt);
        $this->assertSame('2026-06-13 17:45:12', $payout->updatedAt);
        // Non-promoted attributes are kept in the raw bag.
        $this->assertSame('kept', $payout->get('extra_field'));
    }

    public function test_payout_handles_missing_booking_block_gracefully(): void
    {
        $payout = Payout::fromArray([
            'uuid' => '11111111-2222-3333-4444-555555555555',
            'amount' => 100.5,
            'status' => 'pending',
        ]);

        $this->assertSame('11111111-2222-3333-4444-555555555555', $payout->uuid);
        $this->assertNull($payout->bookingUuid);
        $this->assertSame(100.5, $payout->amount);
        $this->assertSame('pending', $payout->status);
    }

    public function test_payout_unwraps_single_payout_envelope(): void
    {
        $payout = Payout::fromArray([
            'ResponseCode' => '200',
            'Result' => 'true',
            'ResponseMsg' => 'Payout retrieved successfully.',
            'payout' => [
                'uuid' => '99999999-aaaa-bbbb-cccc-dddddddddddd',
                'booking' => ['uuid' => 'uuid-555'],
                'amount' => 1234.56,
                'status' => 'completed',
            ],
        ]);

        $this->assertSame('99999999-aaaa-bbbb-cccc-dddddddddddd', $payout->uuid);
        $this->assertSame('uuid-555', $payout->bookingUuid);
        $this->assertSame(1234.56, $payout->amount);
        $this->assertSame('completed', $payout->status);
        // The attributes bag holds the unwrapped row, not the envelope.
        $this->assertSame('99999999-aaaa-bbbb-cccc-dddddddddddd', $payout->attributes['uuid']);
        $this->assertArrayNotHasKey('ResponseCode', $payout->attributes);
    }

    public function test_payout_collection_reads_payouts_and_pagination(): void
    {
        $collection = PayoutCollection::fromArray([
            'payouts' => [
                ['uuid' => 'uuid-1', 'amount' => 100, 'status' => 'pending'],
                ['uuid' => 'uuid-2', 'amount' => 200, 'status' => 'proof_submitted'],
            ],
            'pagination' => [
                'current_page' => 1,
                'last_page' => 3,
                'per_page' => 50,
                'total' => 125,
            ],
        ]);

        $this->assertCount(2, $collection);
        $this->assertSame('uuid-1', $collection->items[0]->uuid);
        $this->assertSame('proof_submitted', $collection->items[1]->status);
        $this->assertNotNull($collection->meta);
        $this->assertSame(125, $collection->meta->total);
    }

    public function test_payout_collection_falls_back_through_envelope_variants(): void
    {
        $fromData = PayoutCollection::fromArray([
            'data' => [['uuid' => 'uuid-5', 'amount' => 50, 'status' => 'completed']],
        ]);
        $this->assertCount(1, $fromData);
        $this->assertSame('uuid-5', $fromData->items[0]->uuid);

        $fromBareList = PayoutCollection::fromArray([
            ['uuid' => 'uuid-9', 'amount' => 9, 'status' => 'rejected'],
        ]);
        $this->assertCount(1, $fromBareList);
        $this->assertSame('uuid-9', $fromBareList->items[0]->uuid);
    }

    public function test_whatsapp_contact_unwraps_envelope_and_maps_fields(): void
    {
        $contact = WhatsAppContact::fromArray([
            'ResponseCode' => '200',
            'whatsapp' => [
                'phone_number' => '01044660885',
                'phone_digits' => '201044660885',
                'url' => 'https://wa.me/201044660885',
                'deep_link' => 'https://api.whatsapp.com/send?phone=201044660885',
            ],
        ]);

        $this->assertSame('01044660885', $contact->phoneNumber);
        $this->assertSame('201044660885', $contact->phoneDigits);
        $this->assertSame('https://wa.me/201044660885', $contact->url);
        $this->assertSame('https://api.whatsapp.com/send?phone=201044660885', $contact->deepLink);
        $this->assertSame('01044660885', $contact->get('phone_number'));
    }

    public function test_city_folder_collection_maps_rows(): void
    {
        $folders = CityFolderCollection::fromArray([
            'city_folders' => [
                [
                    'id' => 12,
                    'name' => 'New Cairo',
                    'name_en' => 'New Cairo',
                    'name_ar' => 'القاهرة الجديدة',
                    'unit_count' => 8,
                    'cover_images' => ['a.jpg', 'b.jpg'],
                ],
            ],
        ]);

        $this->assertCount(1, $folders);
        $folder = $folders->items[0];
        $this->assertInstanceOf(CityFolder::class, $folder);
        $this->assertSame(12, $folder->id);
        $this->assertSame('New Cairo', $folder->name);
        $this->assertSame(8, $folder->unitCount);
        $this->assertSame(['a.jpg', 'b.jpg'], $folder->coverImages);
    }

    public function test_city_folder_units_maps_folder_and_units(): void
    {
        $result = CityFolderUnits::fromArray([
            'city_folder' => ['id' => 12, 'name' => 'New Cairo', 'name_en' => 'New Cairo', 'name_ar' => null],
            'units' => [
                ['uuid' => 'u-1', 'title' => 'Unit', 'price' => '1000', 'price_currency' => 'EGP'],
            ],
            'pagination' => ['current_page' => 1, 'total' => 1],
        ]);

        $this->assertSame(12, $result->cityFolder?->id);
        $this->assertSame('New Cairo', $result->cityFolder?->name);
        $this->assertCount(1, $result);
        $this->assertSame('u-1', $result->items[0]->uuid);
        $this->assertSame(1, $result->meta?->total);
    }

    public function test_facility_collection_maps_grouped_amenities(): void
    {
        $collection = FacilityCollection::fromArray([
            'facilities' => [
                [
                    'category_id' => 1,
                    'category_name' => 'Essentials',
                    'category_name_en' => 'Essentials',
                    'category_name_ar' => null,
                    'category_icon' => 'icon.svg',
                    'facilities' => [
                        [
                            'id' => 3,
                            'title' => 'Wifi',
                            'title_en' => 'Wifi',
                            'title_ar' => null,
                            'image' => 'wifi.png',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertCount(1, $collection);
        $group = $collection->items[0];
        $this->assertInstanceOf(FacilityGroup::class, $group);
        $this->assertSame(1, $group->categoryId);
        $this->assertSame('Essentials', $group->categoryName);
        $this->assertCount(1, $group);
        $facility = $group->facilities[0];
        $this->assertInstanceOf(Facility::class, $facility);
        $this->assertSame(3, $facility->id);
        $this->assertSame('Wifi', $facility->title);
        $this->assertSame('wifi.png', $facility->image);
    }
}
