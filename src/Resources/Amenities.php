<?php

declare(strict_types=1);

namespace Maat\Waffarha\Resources;

use Maat\Waffarha\Data\AmenityGroupCollection;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;

/**
 * Amenities catalogue — ids for the `amenities[]` city-folder unit filter.
 */
final class Amenities extends Resource
{
    /**
     * Active amenities grouped by category.
     *
     * - **HTTP:** `GET amenities`
     * - Pass `lang: ar` header to localize titles / category names.
     *
     * @param  array<string, scalar|null>  $query
     *
     * @throws WaffarhaRequestException
     */
    public function list(array $query = []): AmenityGroupCollection
    {
        return AmenityGroupCollection::fromArray(
            $this->transport->send('GET', 'amenities', query: $query)
        );
    }
}
