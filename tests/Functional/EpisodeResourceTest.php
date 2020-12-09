<?php

/**
 * This file is part of the TangoMan package.
 *
 * Copyright (c) 2020 "Matthias Morin" <mat@tangoman.io>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Episode;

class EpisodeResourceTest extends ApiTestCase
{
    public function testGetEpisodes(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        $response = static::createClient()->request('GET', '/api/episodes');

        $this->assertResponseIsSuccessful();

        // Asserts that the returned content type is JSON-LD (the default)
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        // Asserts that the returned JSON is a superset of this one
        $this->assertJsonContains(
            [
                '@context' => '/api/contexts/Episode',
                '@id' => '/api/episodes',
                '@type' => 'hydra:Collection',
                'hydra:totalItems' => 100,
                'hydra:view' => [
                    '@id' => '/api/episodes?page=1',
                    '@type' => 'hydra:PartialCollectionView',
                    'hydra:first' => '/api/episodes?page=1',
                    'hydra:last' => '/api/episodes?page=4',
                    'hydra:next' => '/api/episodes?page=2',
                ],
            ]
        );

        // Because test fixtures are automatically loaded between each test, you can assert on them
        $this->assertCount(30, $response->toArray()['hydra:member']);

        // Asserts that the returned JSON is validated by the JSON Schema generated for this resource by API Platform
        // This generated JSON Schema is also used in the OpenAPI spec!
        $this->assertMatchesResourceCollectionJsonSchema(Episode::class);
    }
}
