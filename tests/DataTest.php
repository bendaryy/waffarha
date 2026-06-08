<?php

declare(strict_types=1);

namespace Maat\Waffarha\Tests;

use Maat\Waffarha\Data\TokenResponse;
use Maat\Waffarha\Data\Unit;
use Maat\Waffarha\Data\UnitCollection;
use PHPUnit\Framework\TestCase as BaseTestCase;

class DataTest extends BaseTestCase
{
    public function test_unit_maps_real_fields_and_keeps_raw_attributes(): void
    {
        $unit = Unit::fromArray([
            'uuid' => '4e2248c9',
            'title' => 'Fantastic holiday',
            'city' => 'Alexandria',
            'images' => ['cover.png'],
            'price' => '1000',
            'price_currency' => 'EGP',
            'base_price' => '1000',
        ]);

        $this->assertSame('4e2248c9', $unit->uuid);
        $this->assertSame('Fantastic holiday', $unit->title);
        $this->assertSame('Alexandria', $unit->city);
        $this->assertSame(['cover.png'], $unit->images);
        $this->assertSame('1000', $unit->price);
        $this->assertSame('EGP', $unit->priceCurrency);
        // Non-promoted fields survive in the raw bag.
        $this->assertSame('1000', $unit->get('base_price'));
        $this->assertNull($unit->get('missing'));
    }

    public function test_unit_falls_back_to_id_for_uuid(): void
    {
        $unit = Unit::fromArray(['id' => 42, 'title' => 'Unit']);

        $this->assertSame('42', $unit->uuid);
        $this->assertSame('Unit', $unit->title);
    }

    public function test_collection_reads_units_and_pagination(): void
    {
        $collection = UnitCollection::fromArray([
            'units' => [['uuid' => 'a', 'title' => 'A'], ['uuid' => 'b']],
            'pagination' => ['total' => 2, 'current_page' => 1, 'last_page' => 5, 'next_page_url' => 'https://x/?page=2'],
        ]);

        $this->assertCount(2, $collection);
        $this->assertSame('a', $collection->items[0]->uuid);
        $this->assertSame('A', $collection->items[0]->title);
        $this->assertSame(2, $collection->meta?->total);
        $this->assertSame(5, $collection->meta?->lastPage);
        $this->assertSame('https://x/?page=2', $collection->meta?->nextPageUrl);
    }

    public function test_collection_is_empty_when_units_key_is_absent(): void
    {
        $collection = UnitCollection::fromArray(['ResponseCode' => '200', 'Result' => 'true']);

        $this->assertCount(0, $collection);
        $this->assertNull($collection->meta);
    }

    public function test_token_response_defaults_token_type_and_nullable_refresh(): void
    {
        $token = TokenResponse::fromArray(['access_token' => 'abc', 'expires_in' => 60]);

        $this->assertSame('abc', $token->accessToken);
        $this->assertSame(60, $token->expiresIn);
        $this->assertSame('Bearer', $token->tokenType);
        $this->assertNull($token->refreshToken);
    }
}
