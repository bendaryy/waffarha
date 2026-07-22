# `facilities()->list()` — amenity catalogue

Fetch active facilities grouped by category. Use each facility `id` with the
`facilities[]` filter on [`cityFolders()->units()`](city-folders.md).

```php
Waffarha::facilities()->list(array $query = []): FacilityCollection
```

- **HTTP:** `GET {base_url}/facilities`
- **Returns:** [`FacilityCollection`](data-objects.md#facilitycollection) of
  [`FacilityGroup`](data-objects.md#facilitygroup) rows
- **Headers:** optional `lang: ar` to localize `title` / `category_name`

Categories with zero active facilities are omitted.

## Example

```php
$groups = Waffarha::facilities()->list();

foreach ($groups as $group) {
    echo $group->categoryName, PHP_EOL;
    foreach ($group as $facility) {
        echo '  #', $facility->id, ' ', $facility->title, PHP_EOL;
    }
}

// Then filter folder units by amenity ids:
Waffarha::cityFolders()->units(12, [
    'facilities' => [3, 7],
]);
```

## Response

```json
{
  "ResponseCode": "200",
  "Result": "true",
  "ResponseMsg": "Facilities Retrieved Successfully!",
  "facilities": [
    {
      "category_id": 1,
      "category_name": "Essentials",
      "category_name_en": "Essentials",
      "category_name_ar": "أساسيات",
      "category_icon": "https://cdn.example/icons/essentials.svg",
      "facilities": [
        {
          "id": 3,
          "title": "Wifi",
          "title_en": "Wifi",
          "title_ar": "واي فاي",
          "image": "https://cdn.example/facilities/wifi.png"
        }
      ]
    }
  ]
}
```
