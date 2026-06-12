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

Methods: `count()`, `getIterator()`, `toArray(): array` (raw day rows).

### UnitCalendarDay

A single row inside `UnitCalendar::$days`.

| Property | Type | Source key |
|----------|------|-----------|
| `date` | `?string` | `date` (`Y-m-d`) |
| `price` | `?float` | `price` (EGP, rounded to 2 decimals) |
| `currency` | `?string` | `currency` |
| `available` | `?bool` | `available` |
| `isWeekend` | `?bool` | `is_weekend` |
| `linkedDateId` | `?int` | `linked_date_id` — cross-reference into `UnitCalendar::$linkedDates` |
| `reason` | `?string` | `reason` — `null`, `"weekend_rate"`, `"special_rate"`, `"linked_date"`, `"booked"`, or `"blocked"` (priority: `booked` > `blocked` > `linked_date` > `special_rate` > `weekend_rate`) |
| `attributes` | `array<string,mixed>` | full decoded row |

Methods: `get(string $key, mixed $default = null)`, `toArray()`.

### LinkedDateSummary

A single host-defined minimum-stay rule overlapping the calendar window.
Inside `UnitCalendar::$linkedDates`.

| Property | Type | Source key |
|----------|------|-----------|
| `id` | `?int` | `id` — referenced by `UnitCalendarDay::$linkedDateId` |
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
- from `$financial`: `currency`, `subtotal`, `cleaningFee`, `total`,
  `commissionPercentage`, `commissionAmount`

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
in `$currency` (always `"EGP"` today).

| Property | Type | Source key |
|----------|------|-----------|
| `currency` | `?string` | `currency` |
| `subtotal` | `?float` | `subtotal` — sum of nightly prices (EGP, rounded to 2 decimals) |
| `cleaningFee` | `?float` | `cleaning_fee` — one-time per-booking cleaning fee (EGP). `0.0` when the host has not configured one; `null` only on older API responses that omitted the field |
| `commissionPercentage` | `?float` | `commission_percentage` — Maat's platform commission rate from `tbl_setting.commission` (e.g. `1.00` = 1%). `null` on older responses |
| `commissionAmount` | `?float` | `commission_amount` — `subtotal × commission_percentage / 100`, rounded to 2 decimals. Informational only — not added to `total` |
| `total` | `?float` | `total` — `subtotal + cleaning_fee`. Falls back to `subtotal + (cleaning_fee ?? 0)` for older API responses that did not send `total` |

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
| `status` | `?string` | `status` |
| `cancellationReason` | `?string` | `cancellation_reason` |
| `notes` | `?string` | `notes` |
| `guest` | `?Guest` | `guest` |
| `createdAt` | `?string` | `created_at` |
| `updatedAt` | `?string` | `updated_at` |
| `attributes` | `array<string,mixed>` | full decoded payload |

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
