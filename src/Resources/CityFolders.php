<?php

declare(strict_types=1);

namespace Maat\Waffarha\Resources;

use Maat\Waffarha\Data\CityFolderCollection;
use Maat\Waffarha\Data\CityFolderUnits;
use Maat\Waffarha\Exceptions\WaffarhaRequestException;

/**
 * City folders browse API — list folders for a country, then list/filter the
 * Waffarha-exposed units inside one folder.
 */
final class CityFolders extends Resource
{
    /**
     * Active city folders that contain at least one Waffarha-exposed unit.
     *
     * - **HTTP:** `GET city-folders`
     *
     * @param  array<string, scalar|null>  $query
     *
     * @throws WaffarhaRequestException
     */
    public function list(array $query = []): CityFolderCollection
    {
        return CityFolderCollection::fromArray(
            $this->transport->send('GET', 'city-folders', query: $query)
        );
    }

    /**
     * Paginated units inside a city folder (Waffarha-exposed units only).
     *
     * - **HTTP:** `GET city-folders/{id}/units`
     * - **Query:** optional search filters (`keyword`, dates, beds, `sort_by`,
     *   …) and `page` / `per_page`.
     *
     * @param  array<string, scalar|null>  $query
     *
     * @throws WaffarhaRequestException
     */
    public function units(int|string $cityFolderId, array $query = []): CityFolderUnits
    {
        return CityFolderUnits::fromArray(
            $this->transport->send('GET', "city-folders/{$cityFolderId}/units", query: $query)
        );
    }
}
