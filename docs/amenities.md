# `amenities()->list()` — amenity catalogue

Fetch active amenities grouped by category. Use each amenity `id` with the
`amenities[]` filter on [`cityFolders()->units()`](city-folders.md).

```php
Waffarha::amenities()->list(array $query = []): AmenityGroupCollection
```

- **HTTP:** `GET {base_url}/amenities`
- **Returns:** [`AmenityGroupCollection`](data-objects.md#amenitygroupcollection) of
  [`AmenityGroup`](data-objects.md#amenitygroup) rows
- **Headers:** optional `lang: ar` to localize `title` / `category_name`

Categories with zero active amenities are omitted.

## Example

```php
$groups = Waffarha::amenities()->list();

foreach ($groups as $group) {
    echo $group->categoryName, PHP_EOL;
    foreach ($group as $amenity) {
        echo '  #', $amenity->id, ' ', $amenity->title, PHP_EOL;
    }
}

// Then filter folder units by amenity ids:
Waffarha::cityFolders()->units(12, [
    'amenities' => [3, 7],
]);
```

## Response

```json
{
  "ResponseCode": "200",
  "Result": "true",
  "ResponseMsg": "Amenities Retrieved Successfully!",
  "amenities": [
    {
      "category_id": 1,
      "category_name": "Essentials",
      "category_name_en": "Essentials",
      "category_name_ar": "أساسيات",
      "category_icon": "https://cdn.example/icons/essentials.svg",
      "amenities": [
        {
          "id": 3,
          "title": "Wifi",
          "title_en": "Wifi",
          "title_ar": "واي فاي",
          "image": "https://cdn.example/amenities/wifi.png"
        }
      ]
    }
  ]
}
```
