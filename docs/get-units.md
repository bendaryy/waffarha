# `units()->list()` — list units

Fetch a paginated list of syndicated units.

```php
Waffarha::units()->list(array $query = []): UnitCollection
```

- **HTTP:** `GET {base_url}/units`
- **Returns:** [`UnitCollection`](data-objects.md#unitcollection) — an iterable,
  countable collection of [`Unit`](data-objects.md#unit) summary objects.
- **Throws:** `WaffarhaRequestException` on a non-2xx status or transport failure.

## Parameters

`$query` is sent as the query string. Observed parameters:

| Param | Type | Description |
|-------|------|-------------|
| `page` | int | Page number. |
| `per_page` | int | Items per page. |

Unknown parameters are passed through as-is.

## Example

```php
use Maat\Waffarha\Facades\Waffarha;

$units = Waffarha::units()->list(['page' => 1, 'per_page' => 5]);

echo count($units);            // items on this page
echo $units->meta?->total;     // total across all pages

foreach ($units as $unit) {
    echo $unit->uuid, ' ', $unit->title, ' — ', $unit->price, ' ', $unit->priceCurrency, PHP_EOL;
}
```

## Response shape

```json
{
    "ResponseCode": "200",
    "Result": "true",
    "ResponseMsg": "Waffarha units retrieved successfully.",
    "units": [
        {
            "uuid": "4e2248c9-5641-45af-bb0f-d7edb3e7e4be",
            "title": "Fantastic holiday",
            "city": "Bab Shar', Alexandria Governorate",
            "images": ["https://.../cover.png"],
            "price": "1000",
            "price_currency": "EGP",
            "base_price": "1000",
            "base_price_currency": "EGP",
            "cleaning_fee": "100",
            "cleaning_fee_currency": "EGP",
            "base_cleaning_fee": "100",
            "latitude": "31.200090633505",
            "longitude": "29.918738678098",
            "country_id": "3"
        }
    ],
    "pagination": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 5,
        "total": 4,
        "next_page_url": null,
        "prev_page_url": null
    }
}
```

The `units` array maps to `Unit` objects and `pagination` to a `PaginationMeta`.
Each `Unit` promotes the common fields and keeps the full payload — so fields not
promoted (e.g. `base_price`, `cleaning_fee_currency`) remain reachable via
`$unit->get('base_price')`. See the full field tables in
[data objects](data-objects.md#unit).

> Note: the list item shape differs from the single-unit detail shape. To get the
> richer payload (amenities, descriptions, policies, …) use
> [`units()->get()`](get-unit.md).
