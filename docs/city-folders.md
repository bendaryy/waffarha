# `cityFolders()->list()` / `units()` — browse city folders

City folders group Waffarha-exposed units by destination. Only units that
exist in Maat's Waffarha catalogue (`waffarha_units`, currently available
for display) are included — never the full guest-app inventory.

```php
Waffarha::cityFolders()->list(array $query = []): CityFolderCollection
Waffarha::cityFolders()->units(int|string $cityFolderId, array $query = []): CityFolderUnits
```

## List folders

- **HTTP:** `GET {base_url}/city-folders`
- **Returns:** [`CityFolderCollection`](data-objects.md#cityfoldercollection)

Folders with **zero** Waffarha-exposed units are omitted. `unit_count` and
`cover_images` count only those units.

```php
$folders = Waffarha::cityFolders()->list();

foreach ($folders as $folder) {
    echo $folder->id, ' ', $folder->name, ' (', $folder->unitCount, ')', PHP_EOL;
}
```

```json
{
  "ResponseCode": "200",
  "Result": "true",
  "ResponseMsg": "City Folders Retrieved Successfully!",
  "city_folders": [
    {
      "id": 12,
      "name": "New Cairo",
      "name_en": "New Cairo",
      "name_ar": "القاهرة الجديدة",
      "unit_count": 8,
      "cover_images": ["https://cdn.example/a.jpg", "https://cdn.example/b.jpg"]
    }
  ]
}
```

Pass `lang: ar` header to prefer `name_ar` in the localized `name` field.

## Folder units

- **HTTP:** `GET {base_url}/city-folders/{id}/units`
- **Returns:** [`CityFolderUnits`](data-objects.md#cityfolderunits) — folder
  summary + paginated [`Unit`](data-objects.md#unit) rows (same shape as
  [`units()->list()`](get-units.md)). Money fields are always **EGP**.

An inactive folder returns HTTP **404**.

### Search & filter query params

All filters are optional. Sent as the query string (GET). Arrays use
`ptype[]` / `facilities[]` (or `ptype[0]`, `facilities[0]`).

| Key | Type | Description |
|-----|------|-------------|
| `keyword` | string | Free-text match on title / city / address |
| `check_in` | `Y-m-d` | Stay start — also used for availability filtering |
| `check_out` | `Y-m-d` | Stay end (must be after `check_in`) |
| `guests` | int (≥ 1) | Minimum persons allowed |
| `beds` | int | Minimum beds |
| `bedroom` | int | Minimum bedrooms |
| `bathrooms` | int | Minimum bathrooms |
| `price_min` | number | Min nightly price (EGP) |
| `price_max` | number | Max nightly price (EGP) |
| `m2_min` | int | Min square meters |
| `m2_max` | int | Max square meters |
| `ptype` | int[] | Property type / category ids |
| `facilities` | int[] | Facility ids — from [`facilities()->list()`](facilities.md) |
| `latitude` | number | Map center latitude (with `longtitude`) |
| `longtitude` | number | Map center longitude (API spelling) |
| `radius` | number | Search radius in km around lat/lng |
| `zoom` | number | Map zoom — used to derive radius when `radius` is omitted |
| `sort_by` | string | See sort values below |
| `sort_order` | `asc` \| `desc` | Sort direction (default `desc` when applicable) |
| `page` | int | Page number (default `1`) |
| `per_page` | int | Page size (default `20`, max `100`) |

**`sort_by` values:** `rating`, `price`, `newest`, `oldest`, `distance`,
`price_asc`, `price_desc`, `rating_desc`.

When `sort_by` is omitted, units keep the folder's host-defined pivot
`sort_order`.

### Full search example (JSON → query)

Use this object as the `$query` argument (or as Postman query params):

```json
{
  "keyword": "cairo",
  "check_in": "2026-08-12",
  "check_out": "2026-08-15",
  "guests": 2,
  "beds": 2,
  "bedroom": 1,
  "bathrooms": 1,
  "price_min": 500,
  "price_max": 5000,
  "m2_min": 50,
  "m2_max": 200,
  "ptype": [1],
  "facilities": [3],
  "latitude": 30.0444,
  "longtitude": 31.2357,
  "radius": 10,
  "sort_by": "price_asc",
  "sort_order": "asc",
  "page": 1,
  "per_page": 20
}
```

```php
$result = Waffarha::cityFolders()->units(12, [
    'keyword' => 'cairo',
    'check_in' => '2026-08-12',
    'check_out' => '2026-08-15',
    'guests' => 2,
    'beds' => 2,
    'bedroom' => 1,
    'bathrooms' => 1,
    'price_min' => 500,
    'price_max' => 5000,
    'm2_min' => 50,
    'm2_max' => 200,
    'ptype' => [1],
    'facilities' => [3],
    'latitude' => 30.0444,
    'longtitude' => 31.2357,
    'radius' => 10,
    'sort_by' => 'price_asc',
    'sort_order' => 'asc',
    'page' => 1,
    'per_page' => 20,
]);

echo $result->cityFolder?->name;
foreach ($result as $unit) {
    echo $unit->uuid, ' ', $unit->price, ' ', $unit->priceCurrency, PHP_EOL;
}
```

### Response shape

```json
{
  "ResponseCode": "200",
  "Result": "true",
  "ResponseMsg": "City Folder Units Retrieved Successfully!",
  "city_folder": {
    "id": 12,
    "name": "New Cairo",
    "name_en": "New Cairo",
    "name_ar": "القاهرة الجديدة"
  },
  "units": [
    {
      "uuid": "4e2248c9-5641-45af-bb0f-d7edb3e7e4be",
      "title": "Fantastic holiday",
      "city": "New Cairo",
      "images": ["https://…/cover.png"],
      "price": "1000",
      "price_currency": "EGP",
      "cleaning_fee": "100",
      "cleaning_fee_currency": "EGP"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1,
    "next_page_url": null,
    "prev_page_url": null
  }
}
```
