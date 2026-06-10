# Data objects

Every method returns typed, immutable objects instead of raw arrays. All live in
the `Maat\Waffarha\Data` namespace, are `final readonly`, and expose a static
`fromArray()` factory.

> **Numeric strings.** Monetary and count fields (`price`, `cleaning_fee`,
> `beds`, …) are returned by the API as strings (e.g. `"1000"`) and kept verbatim
> as strings to avoid precision/rounding surprises. Genuine integers
> (`plimit`, ids) and booleans are typed as such.

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
