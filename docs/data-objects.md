# Data objects

Every method returns typed, immutable objects instead of raw arrays. All live in
the `Maat\Waffarha\Data` namespace, are `final readonly`, and expose a static
`fromArray()` factory.

> **Numeric strings.** Monetary and count fields (`price`, `cleaning_fee`,
> `beds`, …) are returned by the API as strings (e.g. `"1000"`) and kept verbatim
> as strings to avoid precision/rounding surprises. Genuine integers
> (`plimit`, ids) and booleans are typed as such.
>
> **Exception:** the calendar / availability-check DTOs (`UnitCalendarDay`,
> `AvailabilityCheck`, `AvailabilityFinancial`, `AvailabilityNight`)
> expose monetary values (`price`, `subtotal`, `cleaningFee`, `total`,
> `commissionAmount`, …) as `?float` because Maat returns those values as
> JSON numbers rounded server-side to 2 decimals.

## Returned by `units()->list()`

### UnitCollection

Iterable (`foreach`) and countable (`count()`).

| Property | Type | Description |
|----------|------|-------------|
| `items` | `list<Unit>` | The units on this page. |
| `meta` | `?PaginationMeta` | Pagination metadata, or `null` if absent. |

Methods: `count()`, `getIterator()`, `toArray(): array` (raw rows).

### Unit

A unit summary as returned in the list. Promoted fields below; the full payload
is retained.

| Property | Type | Source key |
|----------|------|-----------|
| `uuid` | `?string` | `uuid` (falls back to `id`) |
| `title` | `?string` | `title` |
| `city` | `?string` | `city` |
| `images` | `list<string>` | `images` |
| `price` | `?string` | `price` |
| `priceCurrency` | `?string` | `price_currency` |
| `latitude` | `?string` | `latitude` |
| `longitude` | `?string` | `longitude` |
| `attributes` | `array<string,mixed>` | full decoded row |

Methods: `get(string $key, mixed $default = null)`, `toArray()`. Non-promoted
fields (`base_price`, `cleaning_fee_currency`, `country_id`, …) are read via
`get()`.

### PaginationMeta

| Property | Type | Source key |
|----------|------|-----------|
| `currentPage` | `?int` | `current_page` |
| `lastPage` | `?int` | `last_page` |
| `perPage` | `?int` | `per_page` |
| `total` | `?int` | `total` |
| `nextPageUrl` | `?string` | `next_page_url` |
| `prevPageUrl` | `?string` | `prev_page_url` |

## Returned by `units()->get()`

### UnitDetail

| Property | Type |
|----------|------|
| `property` | `PropertyDetails` |
| `houseDescriptions` | `list<HouseDescription>` |
| `amenities` | `list<Amenity>` |
| `houseSafety` | `list<SafetyItem>` |
| `everyCornerCount` | `list<RoomGallery>` |
| `guestCancellationPolicy` | `?CancellationPolicy` |
| `hostCancellationPolicies` | `list<CancellationPolicy>` |
| `totalReview` | `?int` |
| `houseRules` | `list<array<string,mixed>>` (raw — not yet typed) |
| `reviews` | `list<array<string,mixed>>` (raw — not yet typed, from `reviewlist`) |
| `attributes` | `array<string,mixed>` (full payload) |

Methods: `get(string $key, mixed $default = null)`.

### PropertyDetails

The core property object (response key `propetydetails`).

| Property | Type | Source key |
|----------|------|-----------|
| `uuid` | `?string` | `uuid` |
| `title` | `?string` | `title` |
| `propertyTitle` | `?string` | `property_title` |
| `city` | `?string` | `city` |
| `address` | `?string` | `address` |
| `images` | `list<string>` | `images` |
| `price` | `?string` | `price` |
| `currency` | `?string` | `currency` |
| `cleaningFee` | `?string` | `cleaning_fee` |
| `rate` | `?string` | `rate` |
| `beds` | `?string` | `beds` |
| `bedroom` | `?string` | `bedroom` |
| `bathroom` | `?string` | `bathroom` |
| `latitude` | `?string` | `latitude` |
| `longitude` | `?string` | `longtitude` (API typo) / `longitude` |
| `ownerName` | `?string` | `owner_name` |
| `ownerImage` | `?string` | `owner_image` |
| `mobile` | `?string` | `mobile` |
| `googleMapHint` | `?string` | `google_map_hint` |
| `countryId` | `?string` | `country_id` |
| `weekendPercentage` | `?string` | `weekend_percentage` |
| `plimit` | `?int` | `plimit` |
| `minimumDays` | `?int` | `minimum_days` |
| `checkInTime` | `?string` | `check_in_time` |
| `checkOutTime` | `?string` | `check_out_time` |
| `exclusiveUnit` | `?bool` | `exclusive_unit` |
| `exclusivePrice` | `?bool` | `exclusive_price` |
| `conciergeAvailable` | `?bool` | `concierge_available` |
| `autoConfirm` | `?bool` | `auto_confirm` |
| `sameDayBooking` | `?bool` | `same_day_booking` |
| `selfCheckIn` | `?bool` | `self_check_in` |
| `attributes` | `array<string,mixed>` | full object |

Methods: `get(string $key, mixed $default = null)` — for fields not promoted
(e.g. `average_price`).

### HouseDescription

| Property | Type | Source key |
|----------|------|-----------|
| `categoryName` | `?string` | `category_name` |
| `categoryIcon` | `?string` | `category_icon` |
| `sortOrder` | `?int` | `sort_order` |
| `descriptions` | `list<HouseDescriptionEntry>` | `descriptions` |

### HouseDescriptionEntry

| Property | Type | Source key |
|----------|------|-----------|
| `descriptionId` | `?int` | `description_id` |
| `description` | `?string` | `description` |
| `sortOrder` | `?int` | `sort_order` |
| `attributes` | `array<string,mixed>` | full row |

### Amenity

| Property | Type | Source key |
|----------|------|-----------|
| `id` | `?int` | `id` |
| `title` | `?string` | `title` |
| `titleAr` | `?string` | `title_ar` |
| `image` | `?string` | `img` |

### SafetyItem

| Property | Type | Source key |
|----------|------|-----------|
| `id` | `?int` | `id` |
| `categoryId` | `?int` | `category_id` |
| `sortOrder` | `?int` | `sort_order` |
| `icon` | `?string` | `icon` |
| `name` | `?string` | `name` |
| `description` | `?string` | `description` |
| `nameAr` | `?string` | `name_ar` |
| `descriptionAr` | `?string` | `description_ar` |
| `categoryName` | `?string` | `category_name` |
| `categoryIcon` | `?string` | `category_icon` |

### RoomGallery

| Property | Type | Source key |
|----------|------|-----------|
| `categoryId` | `?int` | `category_id` |
| `categoryName` | `?string` | `category_name` |
| `images` | `list<GalleryImage>` | `images` |

### GalleryImage

| Property | Type | Source key |
|----------|------|-----------|
| `id` | `?int` | `id` |
| `image` | `?string` | `img` |

### CancellationPolicy

Used for both the guest policy and each host policy. The host-only fields are
`null` for the guest policy.

| Property | Type | Source key |
|----------|------|-----------|
| `id` | `?int` | `id` |
| `name` | `?string` | `name` |
| `displayName` | `?string` | `display_name` |
| `shortDescription` | `?string` | `short_description` |
| `descriptions` | `list<PolicyDescription>` | `descriptions` |
| `hostCancellationEnabled` | `?bool` | `host_cancellation_enabled` |
| `hostCancellationNotes` | `?string` | `host_cancellation_notes` |
| `customCompensation30Days` | `?string` | `custom_compensation_30_days` |
| `customCompensation14To29Days` | `?string` | `custom_compensation_14_29_days` |
| `customCompensation7To13Days` | `?string` | `custom_compensation_7_13_days` |

### PolicyDescription

| Property | Type | Source key |
|----------|------|-----------|
| `id` | `?int` | `id` |
| `description` | `?string` | `description` |

## Returned by `units()->calendar()` / `checkAvailability()`

### UnitCalendar

Iterable (`foreach`) and countable (`count()`). Returned by `units()->calendar()`.

| Property | Type | Source key |
|----------|------|-----------|
| `propertyUuid` | `?string` | `property_uuid` |
| `currency` | `?string` | `currency` (always `"EGP"` today) |
| `basePrice` | `?float` | `base_price` |
| `startDate` | `?string` | `window.start_date` |
| `endDate` | `?string` | `window.end_date` |
| `totalDays` | `?int` | `window.days` |
| `days` | `list<UnitCalendarDay>` | `calendar` rows |
| `linkedDates` | `list<LinkedDateSummary>` | `linked_dates` rows |
| `blocklist` | `list<string>` | `blocklist` — sorted unique host-blocked dates (Y-m-d), mirrored per-day on `UnitCalendarDay::$isBooked` |
| `orphanGaps` | `list<OrphanGap>` | `orphan_gaps` — short bookable gaps Maat accepts with a relaxed minimum stay |
| `sameDayBooking` | `?bool` | `same_day_booking` — host opt-in for new check-in on an existing check-out day |
| `baseMinimumStay` | `?int` | `base_minimum_stay` — property default minimum nights |
| `minimumStayOverrides` | `list<MinimumStayOverride>` | `minimum_stay_overrides` — date-ranged special minimums |

Methods: `count()`, `getIterator()`, `toArray(): array` (raw day rows).

### UnitCalendarDay

A single row inside `UnitCalendar::$days`.

| Property | Type | Source key |
|----------|------|-----------|
| `date` | `?string` | `date` (`Y-m-d`) |
| `price` | `?float` | `price` (EGP, rounded to 2 decimals) |
| `currency` | `?string` | `currency` |
| `available` | `?bool` | `available` |
| `isBooked` | `?bool` | `is_booked` — true when the night is taken (existing booking) or host-blocked |
| `availableForCheckin` | `?bool` | `available_for_checkin` — true when a NEW guest can begin a stay on this day; forced to `false` on existing check-out days when the host has `same_day_booking = false` |
| `availableForCheckout` | `?bool` | `available_for_checkout` — true when a NEW guest can end a stay on this day |
| `isWeekend` | `?bool` | `is_weekend` |
| `reason` | `?string` | `reason` — `null`, `"weekend_rate"`, `"special_rate"`, `"linked_date"`, `"booked"`, or `"blocked"` (priority: `booked` > `blocked` > `linked_date` > `special_rate` > `weekend_rate`). For `"linked_date"` days, scan `UnitCalendar::$linkedDates` and pick the rule whose date range covers this day. |
| `attributes` | `array<string,mixed>` | full decoded row |

Methods: `get(string $key, mixed $default = null)`, `toArray()`.

### MinimumStayOverride

A single entry inside `UnitCalendar::$minimumStayOverrides`.

| Property | Type | Source key |
|----------|------|-----------|
| `startDate` | `?string` | `start_date` |
| `endDate` | `?string` | `end_date` |
| `minimumNights` | `?int` | `minimum_nights` |
| `baseMinimumStay` | `?int` | `base_minimum_stay` |
| `effectiveMinimumNights` | `?int` | `effective_minimum_nights` |

### OrphanGap

A single entry inside `UnitCalendar::$orphanGaps`. Represents a short
bookable gap between existing bookings / blocked dates that's smaller than
the property's base minimum stay.

| Property | Type | Source key |
|----------|------|-----------|
| `startDate` | `?string` | `start_date` (Y-m-d, inclusive) |
| `endDate` | `?string` | `end_date` (Y-m-d, inclusive) |
| `gapNights` | `?int` | `gap_nights` |
| `baseMinimumStay` | `?int` | `base_minimum_stay` — the property's normal minimum, surfaced so partners can show the relaxation as a hint |
| `dynamicMinimumNights` | `?int` | `dynamic_minimum_nights` — effective minimum Maat will accept for this gap (always `1` today) |

### LinkedDateSummary

A single host-defined minimum-stay rule overlapping the calendar window.
Inside `UnitCalendar::$linkedDates`.

| Property | Type | Source key |
|----------|------|-----------|
| `id` | `?int` | `id` — stable identifier for the rule |
| `name` | `?string` | `name` |
| `startDate` | `?string` | `start_date` |
| `endDate` | `?string` | `end_date` |
| `requiredNights` | `?int` | `required_nights` |
| `message` | `?string` | `message` — user-facing explanation, safe to render verbatim |
| `attributes` | `array<string,mixed>` | full decoded row |

Methods: `toArray()`.

### AvailabilityCheck

Iterable (`foreach`) and countable (`count()`). Returned by `units()->checkAvailability()`
on the happy path; an unavailable date range surfaces as a `WaffarhaRequestException`
(HTTP 409) instead — see [check-availability.md](check-availability.md).

| Property | Type | Source key |
|----------|------|-----------|
| `available` | `bool` | `available` (defaults to `true` when absent) |
| `propertyUuid` | `?string` | `property_uuid` |
| `isXuruUnit` | `?bool` | `is_xuru_unit` |
| `xuruStatus` | `?bool` | `xuru_status` |
| `xuruPriceApplied` | `?bool` | `xuru_price_applied` |
| `effectiveMinimumStay` | `?int` | `effective_minimum_stay` |
| `checkIn` | `?string` | `check_in` |
| `checkOut` | `?string` | `check_out` |
| `nights` | `?int` | `nights` |
| `currency` | `?string` | `currency` |
| `bookingDates` | `BookingDates` | `booking_dates` block (see below) |
| `financial` | `AvailabilityFinancial` | `financial` block (see below) |
| `property` | `?AvailabilityProperty` | `property` block — compact unit snapshot |
| `specialRatesApplied` | `list<SpecialRateApplied>` | `special_rates_applied` — one entry per distinct rate that hit ≥ 1 night |
| `breakdown` | `list<AvailabilityNight>` | `breakdown` rows |

For ergonomics + IDE autocomplete the most-used fields from the sub-blocks
are also mirrored as top-level read-only properties:

- from `$bookingDates`: `checkIn`, `checkOut`, `nights` (= `totalDays`)
- from `$financial`: `currency`, `subtotal`, `longStayDiscount`, `longStayApplied`,
  `cleaningFee`, `access`, `serviceFee`, `taxRate`, `tax`, `hostTaxRate`,
  `taxFromHost`, `total`, `commissionPercentage`, `commissionAmount`,
  `discountPercentage`, `discountAmount`, `subtotalAfterDiscount`

Methods: `count()`, `getIterator()`.

### BookingDates

Date summary returned inside `AvailabilityCheck::$bookingDates`.

| Property | Type | Source key |
|----------|------|-----------|
| `checkIn` | `?string` | `check_in` — canonical `Y-m-d` |
| `checkOut` | `?string` | `check_out` — canonical `Y-m-d` |
| `totalDays` | `?int` | `total_days` — same as `count($check)` |
| `normalDays` | `?int` | `normal_days` — non-weekend nights. `null` on legacy responses that didn't send the split |
| `weekendDays` | `?int` | `weekend_days` — weekend nights. `null` on legacy responses |

`normalDays + weekendDays === totalDays` whenever both are non-null.

### AvailabilityFinancial

Money block returned inside `AvailabilityCheck::$financial`. All amounts are
in `$currency` (always `"EGP"` today). Cross-endpoint matrix and formulas:
**[financials.md](financials.md)**.

| Property | Type | Source key |
|----------|------|-----------|
| `currency` | `?string` | `currency` |
| `basePrice` | `?float` | `base_price` — nightly base in EGP |
| `subtotal` | `?float` | `subtotal` — sum of nightly prices (EGP, rounded to 2 decimals) |
| `longStayDiscount` | `?float` | `long_stay_discount` — automatic long-stay reduction |
| `longStayApplied` | `?bool` | `long_stay_applied` |
| `discountPercentage` | `?float` | `discount_percentage` — set when request sent `discount_in_percentage` |
| `discountAmount` | `?float` | `discount_amount` |
| `subtotalAfterDiscount` | `?float` | `subtotal_after_discount` |
| `cleaningFee` | `?float` | `cleaning_fee` — one-time per-booking cleaning fee (EGP). `0.0` when the host has not configured one; `null` only on older API responses that omitted the field |
| `access` | `?float` | `access` — one-time access fee (EGP). `null` on older responses |
| `serviceFee` | `?float` | `service_fee` — platform service fee |
| `taxRate` | `?float` | `tax_rate` — tax % on `service_fee` |
| `tax` | `?float` | `tax` — tax amount on `service_fee` |
| `hostTaxRate` | `?float` | `host_tax_rate` — host property tax %. `null` on older responses |
| `taxFromHost` | `?float` | `tax_from_host` — `subtotal × host_tax_rate / 100` (on original subtotal). `null` on older responses |
| `commissionPercentage` | `?float` | `commission_percentage` — Maat's platform commission rate (e.g. `1.00` = 1%). `null` on older responses |
| `commissionAmount` | `?float` | `commission_amount` — commission on post–long-stay base, rounded to 2 decimals. **Not** added to `total` — reported separately |
| `total` | `?float` | `total` — `subtotal_after_discount + cleaning_fee + access + tax_from_host + service_fee + tax`. Falls back to that sum for older API responses that did not send `total` |

### AvailabilityProperty

Compact unit snapshot returned inside `AvailabilityCheck::$property`. Carries
just enough fields for a confirmation card; for the full property detail
call `units()->show($uuid)`.

| Property | Type | Source key |
|----------|------|-----------|
| `uuid` | `?string` | `uuid` — Maat's public identifier; the numeric `id` is never exposed |
| `title` | `?string` | `title` |
| `image` | `?string` | `image` — absolute URL to the primary cover image |
| `address` | `?string` | `address` |
| `city` | `?string` | `city` |
| `beds` | `?int` | `beds` |
| `bathroom` | `?int` | `bathroom` |
| `minimumDays` | `?int` | `minimum_days` — property default minimum stay |

### AvailabilityNight

A single row inside `AvailabilityCheck::$breakdown`. Mirrors the rich
`day_breakdown` produced by Maat's internal pricing pipeline so partners
can render the same UI as a direct Maat checkout. All amounts are in EGP
(matching `AvailabilityFinancial::$currency`).

The price math is:

```
price = price_after_special_rate + weekend_amount
```

| Property | Type | Source key |
|----------|------|-----------|
| `date` | `?string` | `date` |
| `dayNameEnglish` | `?string` | `day_name_english` (e.g. `"Wednesday"`) |
| `dayNameArabic` | `?string` | `day_name_arabic` (e.g. `"الأربعاء"`) |
| `isWeekend` | `?bool` | `is_weekend` |
| `basePrice` | `?float` | `base_price` — the property's nightly base in EGP, before any rate/surcharge |
| `priceAfterSpecialRate` | `?float` | `price_after_special_rate` — base price after the active SpecialRate (if any) |
| `price` | `?float` | `price` — final nightly charge for this date |
| `hasSpecialRate` | `?bool` | `has_special_rate` |
| `specialRateId` | `?int` | `special_rate_id` |
| `specialRateName` | `?string` | `special_rate_name` |
| `specialRatePercentage` | `?float` | `special_rate_percentage` — raw `nightly_price_override` (e.g. `20` = 20%) |
| `specialRateIsIncrease` | `?bool` | `special_rate_is_increase` |
| `isDiscount` | `?bool` | `is_discount` — `true` when the special rate brought the price below base |
| `isPremium` | `?bool` | `is_premium` — `true` when the special rate brought the price above base |
| `discountPercentage` | `?float` | `discount_percentage` — computed off the base price |
| `increasePercentage` | `?float` | `increase_percentage` — computed off the base price |
| `weekendPercentage` | `?float` | `weekend_percentage` — only set when the night was uplifted by the property's weekend rule |
| `weekendAmount` | `?float` | `weekend_amount` — EGP added on top of `price_after_special_rate` for the weekend uplift |
| `attributes` | `array<string,mixed>` | full decoded row (forward-compat escape hatch) |

> Per-night `commission` is intentionally **not** exposed — commission is
> only surfaced at the trip level under `AvailabilityFinancial::$commissionAmount`.

Methods: `toArray()`.

### SpecialRateApplied

A single entry inside `AvailabilityCheck::$specialRatesApplied`. Represents
one distinct host-configured SpecialRate that affected at least one night
in the booking window. All monetary values are in EGP. Dollar equivalents
are intentionally **not** exposed on the Waffarha surface.

| Property | Type | Source key |
|----------|------|-----------|
| `id` | `?int` | `id` |
| `name` | `?string` | `name` |
| `startDate` | `?string` | `start_date` — rate's configured start, not the booking's check-in |
| `endDate` | `?string` | `end_date` |
| `nightlyPriceOverride` | `?float` | `nightly_price_override` — raw stored percentage (e.g. `20` means 20%) |
| `effectiveNightlyPrice` | `?float` | `effective_nightly_price` — EGP, base price after the rate is applied |
| `basePrice` | `?float` | `base_price` — EGP, property's nightly base before the rate |
| `isIncrease` | `?bool` | `is_increase` — `true` for premium pricing |
| `isDiscount` | `?bool` | `is_discount` — `true` when the rate pulled the price below base |
| `isPremium` | `?bool` | `is_premium` — `true` when the rate pushed the price above base |
| `discountPercentage` | `?float` | `discount_percentage` — computed off `base_price` |
| `increasePercentage` | `?float` | `increase_percentage` — computed off `base_price` |

## Returned by `bookings()->*`

> **Provisional.** The bookings response shape is not yet confirmed against the
> live API. The DTOs below are inferred from the documented create-request
> payload and the outbound booking [webhook](webhooks.md) (which use slightly
> different key names), so each field is read from every observed candidate key.
> The full payload is always retained in `$attributes`. Run `composer test:live`
> to capture a real response and tighten the mapping.

### BookingCollection

Iterable (`foreach`) and countable (`count()`). Returned by `bookings()->list()`.

| Property | Type | Description |
|----------|------|-------------|
| `items` | `list<Booking>` | The bookings on this page. |
| `meta` | `?PaginationMeta` | Pagination metadata, or `null` if absent. |

Rows are resolved from the first of `bookings`, `data`, or a bare top-level list.
Methods: `count()`, `getIterator()`, `toArray(): array` (raw rows).

### Booking

| Property | Type | Source key(s) |
|----------|------|---------------|
| `uuid` | `?string` | `uuid` (falls back to `id`) |
| `providerBookingId` | `?string` | `provider_booking_id` |
| `provider` | `?string` | `provider` |
| `propertyUuid` | `?string` | `property_uuid` (falls back to `property_id`) |
| `propertyTitle` | `?string` | `property_title` |
| `checkIn` | `?string` | `check_in` |
| `checkOut` | `?string` | `check_out` |
| `guestsCount` | `?int` | `guests_count` (falls back to `number_of_guests`) |
| `totalAmount` | `?string` | `total_amount` |
| `currency` | `?string` | `currency` |
| `status` | `?string` | `status` — see [booking-statuses.md](booking-statuses.md) |
| `cancellationReason` | `?string` | `cancellation_reason` |
| `notes` | `?string` | `notes` |
| `financial` | `?BookingFinancial` | `financial` (block — see below) |
| `guest` | `?Guest` | `guest` |
| `createdAt` | `?string` | `created_at` |
| `updatedAt` | `?string` | `updated_at` |
| `attributes` | `array<string,mixed>` | full decoded payload |

Methods: `get(string $key, mixed $default = null)`, `toArray()`.

### BookingFinancial

Nested under `Booking::$financial`. Partner-safe slice of the financial
record Maat persists after running the server-side pricing pipeline. All
monetary values are floats in EGP.

Full formulas, discount rules, and a field matrix across check / booking /
receipt: **[financials.md](financials.md)**.

| Property | Type | Source key | Notes |
|----------|------|-----------|-------|
| `currency` | `string` | `currency` | Always `"EGP"`. |
| `subtotal` | `float` | `subtotal` | Sum of every night's `price` from `units()->checkAvailability()`. |
| `longStayDiscount` | `?float` | `long_stay_discount` | Automatic long-stay reduction when eligible. |
| `longStayApplied` | `?bool` | `long_stay_applied` | `true` when long-stay applied. |
| `discountPercentage` | `?float` | `discount_percentage` | Only when a Waffarha % discount applied; otherwise omitted/`null`. |
| `discountAmount` | `?float` | `discount_amount` | Only when discount applied. |
| `subtotalAfterDiscount` | `?float` | `subtotal_after_discount` | After long-stay and/or partner %. |
| `cleaningFee` | `float` | `cleaning_fee` | One-time cleaning fee in EGP. |
| `access` | `float` | `access` | One-time access fee in EGP. |
| `serviceFee` | `float` | `service_fee` | Platform service fee. |
| `taxRate` | `float` | `tax_rate` | Tax % on `service_fee` (0 when omitted upstream). |
| `tax` | `float` | `tax` | Tax amount on `service_fee`. |
| `hostTaxRate` | `float` | `host_tax_rate` | Host property tax %. |
| `taxFromHost` | `float` | `tax_from_host` | Host tax amount on original subtotal. |
| `total` | `float` | `total` | `subtotal_after_discount + cleaning_fee + access + tax_from_host + service_fee + tax` — what the partner is billed. Commission is **not** added. |

> Maat's commission breakdown (commission per day, total commission, net
> amount) is computed server-side for host payouts but is never exposed on
> this DTO — that's internal accounting.

### GuestBookDetails

Returned by [`bookings()->bookDetails()`](book-details.md) only. Guest
receipt payload (always EGP). Preview uses [`Booking`](#booking) instead.
Full `bookdetails` object is in `$attributes`.

| Property | Type | Source key |
|----------|------|-----------|
| `currency` | `?string` | `currency` (always `"EGP"`) |
| `uuid` | `?string` | `uuid` |
| `title` | `?string` | `title` |
| `checkIn` / `checkOut` | `?string` | `check_in` / `check_out` |
| `totalDay` | `?int` | `total_day` |
| `subtotal` | `?float` | `subtotal` |
| `longStayDiscount` | `?float` | `long_stay_discount` |
| `longStayApplied` | `?bool` | `long_stay_applied` |
| `cleaningFee` | `?float` | `cleaning_fee` |
| `access` | `?float` | `access` |
| `serviceFee` | `?float` | `service_fee` |
| `tax` | `?float` | `tax` |
| `hostTaxRate` | `?float` | `host_tax_rate` |
| `taxFromHost` | `?float` | `tax_from_host` |
| `total` | `?float` | `total` |
| `guestName` | `?string` | `guest_name` |
| `dayBreakdown` | `list<array>` | `day_breakdown` |
| `financialSummary` | `array` | `financial_summary` |
| `attributes` | `array` | full `bookdetails` object |

Methods: `get(string $key, mixed $default = null)`, `toArray()`.

### Guest

Nested under `Booking::$guest`.

| Property | Type | Source key |
|----------|------|-----------|
| `name` | `?string` | `name` |
| `email` | `?string` | `email` |
| `phone` | `?string` | `phone` |
| `nationality` | `?string` | `nationality` |
| `passportNumber` | `?string` | `passport_number` |
| `dateOfBirth` | `?string` | `date_of_birth` |
| `attributes` | `array<string,mixed>` | full decoded object |

Methods: `get(string $key, mixed $default = null)`, `toArray()`.

## Returned by `payouts()->list()`, `get()`, `submitProof()`

### PayoutCollection

Iterable (`foreach`) and countable (`count()`). Same envelope shape as
`BookingCollection` — rows under `payouts`, optional `pagination` block.

| Property | Type | Description |
|----------|------|-------------|
| `items` | `list<Payout>` | The payouts on this page. |
| `meta` | `?PaginationMeta` | Pagination metadata, or `null` if absent. |

Methods: `count()`, `getIterator()`, `toArray(): array` (raw rows).

### Payout

A single payout request raised by Maat for one booking. Payouts are
addressed by their public `uuid` — Maat never exposes its internal
sequential id outside the perimeter. Fields below are promoted; the raw
decoded payload is always retained.

| Property | Type | Source key |
|----------|------|-----------|
| `uuid` | `?string` | `uuid` |
| `bookingUuid` | `?string` | `booking.uuid` |
| `amount` | `?float` | `amount` |
| `currency` | `?string` | `currency` |
| `status` | `?string` | `status` — one of `pending`, `proof_submitted`, `completed`, `rejected` |
| `statusLabel` | `?string` | `status_label` — humanised version of `status` |
| `proofUrl` | `?string` | `proof_url` — public URL to the uploaded receipt |
| `proofType` | `?string` | `proof_type` — `image` or `file` |
| `providerNotes` | `?string` | `provider_notes` — note attached at upload time |
| `rejectionReason` | `?string` | `rejection_reason` — set when `status = rejected` |
| `proofSubmittedAt` | `?string` | `proof_submitted_at` (`Y-m-d H:i:s`) |
| `reviewedAt` | `?string` | `reviewed_at` (`Y-m-d H:i:s`) |
| `createdAt` | `?string` | `created_at` (`Y-m-d H:i:s`) |
| `updatedAt` | `?string` | `updated_at` (`Y-m-d H:i:s`) |
| `attributes` | `array<string,mixed>` | full decoded object |

Methods: `get(string $key, mixed $default = null)`, `toArray()`.

## Returned by `whatsapp()->get()`

### WhatsAppContact

Maat support WhatsApp. See [whatsapp.md](whatsapp.md).

| Property | Type | Source key |
|----------|------|-----------|
| `phoneNumber` | `?string` | `phone_number` — as stored in settings |
| `phoneDigits` | `?string` | `phone_digits` — international digits for WhatsApp |
| `url` | `?string` | `url` — `https://wa.me/{phone_digits}` |
| `deepLink` | `?string` | `deep_link` — `https://api.whatsapp.com/send?phone={phone_digits}` |
| `attributes` | `array<string,mixed>` | full decoded `whatsapp` object |

Methods: `get(string $key, mixed $default = null)`, `toArray()`.
