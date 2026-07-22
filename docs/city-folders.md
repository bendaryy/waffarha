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

## Folder units (with search filters)

- **HTTP:** `GET {base_url}/city-folders/{id}/units`
- **Query:** optional `page`, `per_page`, and search filters (`keyword`,
  `check_in` / `check_out`, `guests`, `beds`, `price_min` / `price_max`,
  `sort_by`, …)
- **Returns:** [`CityFolderUnits`](data-objects.md#cityfolderunits) — folder
  summary + paginated [`Unit`](data-objects.md#unit) rows (same shape as
  [`units()->list()`](get-units.md))

```php
$result = Waffarha::cityFolders()->units(12, [
    'check_in' => '2026-08-12',
    'check_out' => '2026-08-15',
    'guests' => 2,
    'page' => 1,
    'per_page' => 20,
]);

echo $result->cityFolder?->name;
foreach ($result as $unit) {
    echo $unit->uuid, ' ', $unit->price, PHP_EOL;
}
```

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
      "price_currency": "EGP"
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

An inactive folder returns HTTP **404**.
