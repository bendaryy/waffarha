<?php

declare(strict_types=1);

namespace Maat\Waffarha\Resources;

use Maat\Waffarha\Data\UnitCollection;
use Maat\Waffarha\Data\UnitDetail;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;

/**
 * The `units` API: listing units and fetching a single unit's full details.
 */
final class Units extends Resource
{
    /**
     * Fetch a paginated list of syndicated units.
     *
     * @param  array<string, scalar|null>  $query
     *
     * @throws WaffarhaRequestException
     */
    public function list(array $query = []): UnitCollection
    {
        return UnitCollection::fromArray(
            $this->transport->send('GET', 'units', query: $query)
        );
    }

    /**
     * Retrieve the full details of a single unit by UUID.
     *
     * Note the singular `unit` path — the list endpoint is `units`, but a single
     * unit is fetched from `unit/{uuid}`.
     *
     * @throws WaffarhaRequestException
     */
    public function get(string $uuid): UnitDetail
    {
        return UnitDetail::fromArray(
            $this->transport->send('GET', "unit/{$uuid}")
        );
    }
}
