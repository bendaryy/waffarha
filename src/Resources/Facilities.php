<?php

declare(strict_types=1);

namespace Maat\Waffarha\Resources;

use Maat\Waffarha\Data\FacilityCollection;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;

/**
 * Facilities catalogue — ids for the `facilities[]` city-folder unit filter.
 */
final class Facilities extends Resource
{
    /**
     * Active facilities grouped by category.
     *
     * - **HTTP:** `GET facilities`
     * - Pass `lang: ar` header to localize titles / category names.
     *
     * @param  array<string, scalar|null>  $query
     *
     * @throws WaffarhaRequestException
     */
    public function list(array $query = []): FacilityCollection
    {
        return FacilityCollection::fromArray(
            $this->transport->send('GET', 'facilities', query: $query)
        );
    }
}
