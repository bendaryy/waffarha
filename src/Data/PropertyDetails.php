<?php

declare(strict_types=1);

namespace Maat\Waffarha\Data;

/**
 * The core property object of a single-unit response, returned by the Maat API
 * under the (sic) `propetydetails` key.
 *
 * Field naming follows the API but is normalised where the API misspells things
 * (`longtitude` is exposed as {@see PropertyDetails::$longitude}). Monetary and
 * count fields arrive as numeric strings and are kept verbatim as strings to
 * avoid precision/rounding surprises. The full decoded object is retained in
 * {@see PropertyDetails::$attributes} (reachable via {@see PropertyDetails::get()}).
 *
 * @phpstan-type PropertyDetailsPayload array<string, mixed>
 */
final readonly class PropertyDetails
{
    /**
     * @param  list<string>  $images
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public ?string $uuid,
        public ?string $title,
        public ?string $propertyTitle,
        public ?string $city,
        public ?string $address,
        public array $images,
        public ?string $price,
        public ?string $currency,
        public ?string $cleaningFee,
        public ?string $rate,
        public ?string $beds,
        public ?string $bedroom,
        public ?string $bathroom,
        public ?string $latitude,
        public ?string $longitude,
        public ?string $ownerName,
        public ?string $ownerImage,
        public ?string $mobile,
        public ?string $googleMapHint,
        public ?string $countryId,
        public ?string $weekendPercentage,
        public ?int $plimit,
        public ?int $minimumDays,
        public ?string $checkInTime,
        public ?string $checkOutTime,
        public ?bool $exclusiveUnit,
        public ?bool $exclusivePrice,
        public ?bool $conciergeAvailable,
        public ?bool $autoConfirm,
        public ?bool $sameDayBooking,
        public ?bool $selfCheckIn,
        public array $attributes,
    ) {}

    /**
     * @param  PropertyDetailsPayload  $data
     */
    public static function fromArray(array $data): self
    {
        $str = static fn (string $key): ?string => isset($data[$key]) && is_scalar($data[$key]) ? (string) $data[$key] : null;
        $int = static fn (string $key): ?int => isset($data[$key]) && is_scalar($data[$key]) ? (int) $data[$key] : null;
        $bool = static fn (string $key): ?bool => array_key_exists($key, $data) ? (bool) $data[$key] : null;

        /** @var list<string> $images */
        $images = isset($data['images']) && is_array($data['images'])
            ? array_values(array_map(static fn ($image): string => (string) $image, $data['images']))
            : [];

        return new self(
            uuid: $str('uuid'),
            title: $str('title'),
            propertyTitle: $str('property_title'),
            city: $str('city'),
            address: $str('address'),
            images: $images,
            price: $str('price'),
            currency: $str('currency'),
            cleaningFee: $str('cleaning_fee'),
            rate: $str('rate'),
            beds: $str('beds'),
            bedroom: $str('bedroom'),
            bathroom: $str('bathroom'),
            latitude: $str('latitude'),
            // API misspells the key as "longtitude"; tolerate the correct one too.
            longitude: $str('longtitude') ?? $str('longitude'),
            ownerName: $str('owner_name'),
            ownerImage: $str('owner_image'),
            mobile: $str('mobile'),
            googleMapHint: $str('google_map_hint'),
            countryId: $str('country_id'),
            weekendPercentage: $str('weekend_percentage'),
            plimit: $int('plimit'),
            minimumDays: $int('minimum_days'),
            checkInTime: $str('check_in_time'),
            checkOutTime: $str('check_out_time'),
            exclusiveUnit: $bool('exclusive_unit'),
            exclusivePrice: $bool('exclusive_price'),
            conciergeAvailable: $bool('concierge_available'),
            autoConfirm: $bool('auto_confirm'),
            sameDayBooking: $bool('same_day_booking'),
            selfCheckIn: $bool('self_check_in'),
            attributes: $data,
        );
    }

    /**
     * Read a raw attribute by key (e.g. `average_price`), with an optional fallback.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
