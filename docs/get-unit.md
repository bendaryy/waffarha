# `units()->get()` — unit details

Retrieve the full details of a single unit by UUID.

```php
Waffarha::units()->get(string $uuid): UnitDetail
```

- **HTTP:** `GET {base_url}/unit/{uuid}` — note the **singular** `unit` path
  (the list endpoint is `units`).
- **Returns:** [`UnitDetail`](data-objects.md#unitdetail) — a richer object than
  the list `Unit`. Its core fields live on `$detail->property`.
- **Throws:** `WaffarhaRequestException` on a non-2xx status or transport failure.

## Example

```php
use Maat\Waffarha\Facades\Waffarha;

$detail = Waffarha::units()->get('4e2248c9-5641-45af-bb0f-d7edb3e7e4be');

// Core property fields
echo $detail->property->title;
echo $detail->property->address;
echo $detail->property->price, ' ', $detail->property->currency;
echo $detail->property->bedroom, ' bed / ', $detail->property->bathroom, ' bath';

// Typed sections
foreach ($detail->amenities as $amenity) {
    echo $amenity->title, PHP_EOL;
}
foreach ($detail->houseDescriptions as $section) {
    echo $section->categoryName, PHP_EOL;
    foreach ($section->descriptions as $line) {
        echo '  - ', $line->description, PHP_EOL;
    }
}

echo $detail->guestCancellationPolicy?->displayName;
```

## Response shape

The detail payload nests the core property under `propetydetails` (spelled as the
API returns it) alongside several sections. Abbreviated:

```json
{
    "ResponseCode": "200",
    "Result": "true",
    "ResponseMsg": "Property Details Founded!",
    "propetydetails": {
        "uuid": "4e2248c9-...",
        "title": "Fantastic holiday",
        "property_title": "Apartment",
        "city": "Bab Shar', Alexandria Governorate",
        "address": "Alexandria, Bab Shar, Alexandria Governorate",
        "images": ["https://.../cover.png", "https://.../extra1.png"],
        "price": "1000",
        "currency": "EGP",
        "cleaning_fee": "100",
        "beds": "5", "bedroom": "1", "bathroom": "1",
        "latitude": "31.200090633505",
        "longtitude": "29.918738678098",
        "owner_name": "Alaa Morsy",
        "check_in_time": "14:00:00", "check_out_time": "12:00:00",
        "self_check_in": true, "auto_confirm": true, "concierge_available": true,
        "plimit": 5, "minimum_days": 4
    },
    "house_descriptions": [
        { "category_name": "Calm & Cozy", "category_icon": "fas fa-couch", "sort_order": 0,
          "descriptions": [ { "description_id": 1, "description": "A serene escape…", "sort_order": 0 } ] }
    ],
    "house_rules": [],
    "house_safety": [
        { "id": 1, "icon": "https://.../carbon-monoxide.png", "category_id": 1, "sort_order": 1,
          "name": "Carbon monoxide alarm", "description": "The Host reported…",
          "category_name": "Safety devices", "category_icon": "https://.../safety-devices.png",
          "name_ar": "إنذار أول أكسيد الكربون", "description_ar": "…" }
    ],
    "amenities": [
        { "id": 61, "img": "https://.../wifi.png", "title": "Wifi", "title_ar": "واي فاي" }
    ],
    "every_corner_count": [
        { "category_id": 4, "category_name": "Bedroom 1", "images": [ { "id": 309, "img": "https://.../g.png" } ] }
    ],
    "reviewlist": [],
    "total_review": 0,
    "guest_cancellation_policy": {
        "id": 1, "name": "Flexible", "display_name": "Flexible", "short_description": "Flexible",
        "descriptions": [ { "id": 1, "description": "Cancel up to 24h before check-in → Full refund." } ]
    },
    "host_cancellation_policies": [
        { "id": 4, "name": "30 days before check-in", "display_name": "30 days before check-in",
          "short_description": "30 days before check-in",
          "custom_compensation_30_days": "0.00", "custom_compensation_14_29_days": "0.00",
          "custom_compensation_7_13_days": "0.00",
          "host_cancellation_enabled": true, "host_cancellation_notes": null,
          "descriptions": [ { "id": 1, "description": "E£100 Guest Compensation" } ] }
    ]
}
```

## Mapping

| Response key | Property on `UnitDetail` | Type |
|--------------|--------------------------|------|
| `propetydetails` | `$detail->property` | [`PropertyDetails`](data-objects.md#propertydetails) |
| `house_descriptions` | `$detail->houseDescriptions` | `list<`[`HouseDescription`](data-objects.md#housedescription)`>` |
| `amenities` | `$detail->amenities` | `list<`[`Amenity`](data-objects.md#amenity)`>` |
| `house_safety` | `$detail->houseSafety` | `list<`[`SafetyItem`](data-objects.md#safetyitem)`>` |
| `every_corner_count` | `$detail->everyCornerCount` | `list<`[`RoomGallery`](data-objects.md#roomgallery)`>` |
| `guest_cancellation_policy` | `$detail->guestCancellationPolicy` | `?`[`CancellationPolicy`](data-objects.md#cancellationpolicy) |
| `host_cancellation_policies` | `$detail->hostCancellationPolicies` | `list<CancellationPolicy>` |
| `total_review` | `$detail->totalReview` | `?int` |
| `house_rules` | `$detail->houseRules` | `list<array<string,mixed>>` (raw — see note) |
| `reviewlist` | `$detail->reviews` | `list<array<string,mixed>>` (raw — see note) |

The full untouched payload is always available via `$detail->get('key')` and
`$detail->attributes`.

> **`houseRules` and `reviews` are not yet typed.** They were empty in every
> observed sample, so their item shapes are unconfirmed and they're returned as
> raw arrays. They'll be promoted to DTOs once a populated sample is available.

See [data objects](data-objects.md) for the complete field tables of every type.
