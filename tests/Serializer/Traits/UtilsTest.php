<?php

/**
 * This file is part of the TangoMan package.
 *
 * Copyright (c) 2020 "Matthias Morin" <mat@tangoman.io>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Serializer\Traits;

use App\Serializer\Exception\EmptyKeyException;
use App\Serializer\Exception\EmptyObjectException;
use App\Serializer\Exception\EmptyPropertyException;
use App\Serializer\Exception\InvalidItemException;
use App\Serializer\Exception\PropertyNotFoundException;
use App\Serializer\Traits\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    const FIXTURE_1 = [
        'array_a_array_a'           => ['a'],
        'array_a_array_b'           => ['a'],
        'array_a_array_empty'       => ['a'],
        'array_a_array_null'        => ['a'],
        'array_a_null'              => ['a'],
        'array_a_string_a'          => ['a'],
        'array_a_string_b'          => ['a'],
        'array_a_string_empty'      => ['a'],
        'array_b_array_a'           => ['b'],
        'array_b_array_b'           => ['b'],
        'array_b_array_empty'       => ['b'],
        'array_b_array_null'        => ['b'],
        'array_b_null'              => ['b'],
        'array_b_string_a'          => ['b'],
        'array_b_string_b'          => ['b'],
        'array_b_string_empty'      => ['b'],
        'array_empty_array_a'       => [],
        'array_empty_array_b'       => [],
        'array_empty_array_empty'   => [],
        'array_empty_array_null'    => [],
        'array_empty_null'          => [],
        'array_empty_string_a'      => [],
        'array_empty_string_b'      => [],
        'array_empty_string_empty'  => [],
        'array_null_array_a'        => [null],
        'array_null_array_b'        => [null],
        'array_null_array_empty'    => [null],
        'array_null_array_null'     => [null],
        'array_null_null'           => [null],
        'array_null_string_a'       => [null],
        'array_null_string_b'       => [null],
        'array_null_string_empty'   => [null],
        'null_array_a'              => null,
        'null_array_b'              => null,
        'null_array_empty'          => null,
        'null_array_null'           => null,
        'null_null'                 => null,
        'null_string_a'             => null,
        'null_string_b'             => null,
        'null_string_empty'         => null,
        'string_a_array_a'          => 'a',
        'string_a_array_b'          => 'a',
        'string_a_array_empty'      => 'a',
        'string_a_array_null'       => 'a',
        'string_a_null'             => 'a',
        'string_a_string_a'         => 'a',
        'string_a_string_b'         => 'a',
        'string_a_string_empty'     => 'a',
        'string_b_array_a'          => 'a',
        'string_b_array_b'          => 'a',
        'string_b_array_empty'      => 'a',
        'string_b_array_null'       => 'a',
        'string_b_null'             => 'a',
        'string_b_string_a'         => 'a',
        'string_b_string_b'         => 'a',
        'string_b_string_empty'     => 'a',
        'string_empty_array_a'      => '',
        'string_empty_array_b'      => '',
        'string_empty_array_empty'  => '',
        'string_empty_array_null'   => '',
        'string_empty_null'         => '',
        'string_empty_string_a'     => '',
        'string_empty_string_b'     => '',
        'string_empty_string_empty' => '',
    ];

    const FIXTURE_2 = [
        'array_a_array_a'           => ['a'],
        'array_a_array_b'           => ['b'],
        'array_a_array_empty'       => [],
        'array_a_array_null'        => [null],
        'array_a_null'              => null,
        'array_a_string_a'          => 'a',
        'array_a_string_b'          => 'b',
        'array_a_string_empty'      => '',
        'array_b_array_a'           => ['a'],
        'array_b_array_b'           => ['b'],
        'array_b_array_empty'       => [],
        'array_b_array_null'        => [null],
        'array_b_null'              => null,
        'array_b_string_a'          => 'a',
        'array_b_string_b'          => 'b',
        'array_b_string_empty'      => '',
        'array_empty_array_a'       => ['a'],
        'array_empty_array_b'       => ['b'],
        'array_empty_array_empty'   => [],
        'array_empty_array_null'    => [null],
        'array_empty_null'          => null,
        'array_empty_string_a'      => 'a',
        'array_empty_string_b'      => 'b',
        'array_empty_string_empty'  => '',
        'array_null_array_a'        => ['a'],
        'array_null_array_b'        => ['b'],
        'array_null_array_empty'    => [],
        'array_null_array_null'     => [null],
        'array_null_null'           => null,
        'array_null_string_a'       => 'a',
        'array_null_string_b'       => 'b',
        'array_null_string_empty'   => '',
        'null_array_a'              => ['a'],
        'null_array_b'              => ['b'],
        'null_array_empty'          => [],
        'null_array_null'           => [null],
        'null_null'                 => null,
        'null_string_a'             => 'a',
        'null_string_b'             => 'b',
        'null_string_empty'         => '',
        'string_a_array_a'          => ['a'],
        'string_a_array_b'          => ['b'],
        'string_a_array_empty'      => [],
        'string_a_array_null'       => [null],
        'string_a_null'             => null,
        'string_a_string_a'         => 'a',
        'string_a_string_b'         => 'b',
        'string_a_string_empty'     => '',
        'string_b_array_a'          => ['a'],
        'string_b_array_b'          => ['b'],
        'string_b_array_empty'      => [],
        'string_b_array_null'       => [null],
        'string_b_null'             => null,
        'string_b_string_a'         => 'a',
        'string_b_string_b'         => 'b',
        'string_b_string_empty'     => '',
        'string_empty_array_a'      => ['a'],
        'string_empty_array_b'      => ['b'],
        'string_empty_array_empty'  => [],
        'string_empty_array_null'   => [null],
        'string_empty_null'         => null,
        'string_empty_string_a'     => 'a',
        'string_empty_string_b'     => 'b',
        'string_empty_string_empty' => '',
    ];

    const EXPECTED_1 = [
        'array_a_array_a'          => ['a'],
        'array_a_array_b'          => [
            'b',
            'a',
        ],
        'array_a_array_empty'      => ['a'],
        'array_a_array_null'       => [
            null,
            'a',
        ],
        'array_a_string_a'         => ['a'],
        'array_a_string_b'         => ['a'],
        'array_b_array_a'          => [
            'a',
            'b',
        ],
        'array_b_array_b'          => ['b'],
        'array_b_array_empty'      => ['b'],
        'array_b_array_null'       => [
            null,
            'b',
        ],
        'array_b_string_a'         => ['b'],
        'array_b_string_b'         => ['b'],
        'array_empty_array_a'      => ['a'],
        'array_empty_array_b'      => ['b'],
        'array_empty_array_empty'  => [],
        'array_empty_array_null'   => [null],
        'array_empty_string_a'     => [],
        'array_empty_string_b'     => [],
        'array_null_array_a'       => [
            'a',
            null,
        ],
        'array_null_array_b'       => [
            'b',
            null,
        ],
        'array_null_array_empty'   => [null],
        'array_null_array_null'    => [null],
        'array_null_string_a'      => [null],
        'array_null_string_b'      => [null],
        'null_array_a'             => ['a'],
        'null_array_b'             => ['b'],
        'null_array_empty'         => [],
        'null_array_null'          => [null],
        'null_string_a'            => 'a',
        'null_string_b'            => 'b',
        'string_a_array_a'         => 'a',
        'string_a_array_b'         => 'a',
        'string_a_array_empty'     => 'a',
        'string_a_array_null'      => 'a',
        'string_a_string_a'        => 'a',
        'string_a_string_b'        => 'a',
        'string_b_array_a'         => 'a',
        'string_b_array_b'         => 'a',
        'string_b_array_empty'     => 'a',
        'string_b_array_null'      => 'a',
        'string_b_string_a'        => 'a',
        'string_b_string_b'        => 'a',
        'string_empty_array_a'     => ['a'],
        'string_empty_array_b'     => ['b'],
        'string_empty_array_empty' => [],
        'string_empty_array_null'  => [null],
        'string_empty_string_a'    => 'a',
        'string_empty_string_b'    => 'b',
        'array_a_null'             => ['a'],
        'array_a_string_empty'     => ['a'],
        'array_b_null'             => ['b'],
        'array_b_string_empty'     => ['b'],
        'array_empty_null'         => [],
        'array_empty_string_empty' => [],
        'array_null_null'          => [null],
        'array_null_string_empty'  => [null],
        'string_a_null'            => 'a',
        'string_a_string_empty'    => 'a',
        'string_b_null'            => 'a',
        'string_b_string_empty'    => 'a',
    ];

    const EXPECTED_2 = [
        'array_a_array_a'          => ['a'],
        'array_a_array_b'          => [
            'a',
            'b',
        ],
        'array_a_array_empty'      => ['a'],
        'array_a_array_null'       => [
            'a',
            null,
        ],
        'array_a_null'             => ['a'],
        'array_a_string_a'         => 'a',
        'array_a_string_b'         => 'b',
        'array_a_string_empty'     => ['a'],
        'array_b_array_a'          => [
            'b',
            'a',
        ],
        'array_b_array_b'          => ['b'],
        'array_b_array_empty'      => ['b'],
        'array_b_array_null'       => [
            'b',
            null,
        ],
        'array_b_null'             => ['b'],
        'array_b_string_a'         => 'a',
        'array_b_string_b'         => 'b',
        'array_b_string_empty'     => ['b'],
        'array_empty_array_a'      => ['a'],
        'array_empty_array_b'      => ['b'],
        'array_empty_array_empty'  => [],
        'array_empty_array_null'   => [null],
        'array_empty_null'         => [],
        'array_empty_string_a'     => 'a',
        'array_empty_string_b'     => 'b',
        'array_empty_string_empty' => [],
        'array_null_array_a'       => [
            null,
            'a',
        ],
        'array_null_array_b'       => [
            null,
            'b',
        ],
        'array_null_array_empty'   => [null],
        'array_null_array_null'    => [null],
        'array_null_null'          => [null],
        'array_null_string_a'      => 'a',
        'array_null_string_b'      => 'b',
        'array_null_string_empty'  => [null],
        'string_a_array_a'         => ['a'],
        'string_a_array_b'         => ['b'],
        'string_a_array_empty'     => [],
        'string_a_array_null'      => [null],
        'string_a_null'            => 'a',
        'string_a_string_a'        => 'a',
        'string_a_string_b'        => 'b',
        'string_a_string_empty'    => 'a',
        'string_b_array_a'         => ['a'],
        'string_b_array_b'         => ['b'],
        'string_b_array_empty'     => [],
        'string_b_array_null'      => [null],
        'string_b_null'            => 'a',
        'string_b_string_a'        => 'a',
        'string_b_string_b'        => 'b',
        'string_b_string_empty'    => 'a',
        'null_array_a'             => ['a'],
        'null_array_b'             => ['b'],
        'null_array_empty'         => [],
        'null_array_null'          => [null],
        'null_string_a'            => 'a',
        'null_string_b'            => 'b',
        'string_empty_array_a'     => ['a'],
        'string_empty_array_b'     => ['b'],
        'string_empty_array_empty' => [],
        'string_empty_array_null'  => [null],
        'string_empty_string_a'    => 'a',
        'string_empty_string_b'    => 'b',
    ];

    /**
     * @var Utils
     */
    private $trait;

    public function testRemoveNullFields(): void
    {
        $result = $this->trait->removeNullFields(
            [
                'integer'      => 0,
                'string'       => 'string',
                'empty_string' => '',
                'null_value'   => null,
                'array'        => ['foobar'],
                'empty_array'  => [],
            ]
        );
        $this->assertEquals(
            [
                'integer' => 0,
                'string'  => 'string',
                'array'   => ['foobar'],
            ],
            $result
        );
    }

    public function testRemoveNullScalars(): void
    {
        $result = $this->trait->removeNullScalars(
            [
                'integer'      => 0,
                'string'       => 'string',
                'empty_string' => '',
                'null_value'   => null,
                'array'        => ['foobar'],
                'empty_array'  => [],
            ]
        );
        $this->assertEquals(
            [
                'integer'     => 0,
                'string'      => 'string',
                'array'       => ['foobar'],
                'empty_array' => [],
            ],
            $result
        );
    }

    /**
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws PropertyNotFoundException
     */
    public function testSortArray(): void
    {
        $result = $this->trait->sortArray(
            [
                'array' => [
                    'c',
                    'a',
                    'b',
                ],
            ],
            'array'
        );
        $this->assertEquals(
            [
                'array' => [
                    'a',
                    'b',
                    'c',
                ],
            ],
            $result
        );
    }

    public function testSortArrayEmptyObjectException(): void
    {
        $this->expectException(EmptyObjectException::class);
        $this->trait->sortArray([], 'key');
    }

    public function testSortArrayEmptyKeyException(): void
    {
        $this->expectException(EmptyKeyException::class);
        $this->trait->sortArray(['foobar'], '');
    }

    public function testSortArrayPropertyNotFoundException(): void
    {
        $this->expectException(PropertyNotFoundException::class);
        $this->trait->sortArray(['foo' => 'bar'], 'key');
    }

    /**
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws PropertyNotFoundException
     */
    public function testStringToBoolean(): void
    {
        $result = $this->trait->stringToBoolean(['bool' => 'true'], 'bool');
        $this->assertEquals(['bool' => true], $result);

        $result = $this->trait->stringToBoolean(['bool' => 'false'], 'bool');
        $this->assertEquals(['bool' => false], $result);
    }

    public function testStringToBooleanEmptyObjectException(): void
    {
        $this->expectException(EmptyObjectException::class);
        $this->trait->stringToBoolean([], 'key');
    }

    public function testStringToBooleanEmptyKeyException(): void
    {
        $this->expectException(EmptyKeyException::class);
        $this->trait->stringToBoolean(['foobar'], '');
    }

    public function testStringToBooleanPropertyNotFoundException(): void
    {
        $this->expectException(PropertyNotFoundException::class);
        $this->trait->stringToBoolean(['foo' => 'bar'], 'key');
    }

    /**
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws PropertyNotFoundException
     */
    public function testStringToArray(): void
    {
        $result = $this->trait->stringToArray(['array' => 'foo, bar'], 'array');
        $this->assertEquals(
            [
                'array' => [
                    'foo',
                    'bar',
                ],
            ],
            $result
        );
    }

    public function testStringToArrayEmptyObjectException(): void
    {
        $this->expectException(EmptyObjectException::class);
        $this->trait->stringToArray([], 'key');
    }

    public function testStringToArrayEmptyKeyException(): void
    {
        $this->expectException(EmptyKeyException::class);
        $this->trait->stringToArray(['foobar'], '');
    }

    public function testStringToArrayPropertyNotFoundException(): void
    {
        $this->expectException(PropertyNotFoundException::class);
        $this->trait->stringToArray(['foo' => 'bar'], 'key');
    }

    /**
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws PropertyNotFoundException
     * @throws InvalidItemException
     */
    public function testArrayToString(): void
    {
        $result = $this->trait->arrayToString(
            [
                'array' => [
                    'foo',
                    'bar',
                ],
            ],
            'array'
        );
        $this->assertEquals(['array' => 'foo, bar'], $result);
    }

    public function testArrayToStringEmptyObjectException(): void
    {
        $this->expectException(EmptyObjectException::class);
        $this->trait->arrayToString([], 'key');
    }

    public function testArrayToStringEmptyKeyException(): void
    {
        $this->expectException(EmptyKeyException::class);
        $this->trait->arrayToString(['foobar'], '');
    }

    public function testArrayToStringPropertyNotFoundException(): void
    {
        $this->expectException(PropertyNotFoundException::class);
        $this->trait->arrayToString(['foo' => 'bar'], 'key');
    }

    /**
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws EmptyPropertyException
     * @throws PropertyNotFoundException
     */
    public function testStringToAssociativeArray(): void
    {
        $result = $this->trait->stringToAssociativeArray(['array' => 'foobar'], 'array', 'name');
        $this->assertEquals(['array' => ['name' => 'foobar']], $result);
    }

    public function testStringToAssociativeArrayEmptyObjectException(): void
    {
        $this->expectException(EmptyObjectException::class);
        $this->trait->stringToAssociativeArray([], 'key', 'name');
    }

    public function testStringToAssociativeArrayEmptyKeyException(): void
    {
        $this->expectException(EmptyKeyException::class);
        $this->trait->stringToAssociativeArray(['foobar'], '', 'name');
    }

    public function testStringToAssociativeArrayEmptyPropertyException(): void
    {
        $this->expectException(EmptyPropertyException::class);
        $this->trait->stringToAssociativeArray(['foobar'], 'key', '');
    }

    public function testStringToAssociativeArrayPropertyNotFoundException(): void
    {
        $this->expectException(PropertyNotFoundException::class);
        $this->trait->stringToAssociativeArray(['foo' => 'bar'], 'key', 'name');
    }

    /**
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws EmptyPropertyException
     * @throws PropertyNotFoundException
     * @throws InvalidItemException
     */
    public function testAssociativeArrayToString(): void
    {
        $result = $this->trait->associativeArrayToString(['array' => ['name' => 'foobar']], 'array', 'name');
        $this->assertEquals(['array' => 'foobar'], $result);
    }

    public function testAssociativeArrayToStringEmptyObjectException(): void
    {
        $this->expectException(EmptyObjectException::class);
        $this->trait->associativeArrayToString([], 'key', 'name');
    }

    public function testAssociativeArrayToStringEmptyKeyException(): void
    {
        $this->expectException(EmptyKeyException::class);
        $this->trait->associativeArrayToString(['foobar'], '', 'name');
    }

    public function testAssociativeArrayToStringEmptyPropertyException(): void
    {
        $this->expectException(EmptyPropertyException::class);
        $this->trait->associativeArrayToString(['foobar'], 'key', '');
    }

    public function testAssociativeArrayToStringPropertyNotFoundException(): void
    {
        $this->expectException(PropertyNotFoundException::class);
        $this->trait->associativeArrayToString(['foo' => 'bar'], 'key', 'name');
    }

    /**
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws EmptyPropertyException
     * @throws PropertyNotFoundException
     */
    public function testStringToCollection(): void
    {
        $result = $this->trait->stringToCollection(['array' => 'foobar'], 'array', 'name');
        $this->assertEquals(['array' => [['name' => 'foobar']]], $result);
    }

    public function testStringToCollectionEmptyObjectException(): void
    {
        $this->expectException(EmptyObjectException::class);
        $this->trait->stringToCollection([], 'key', 'name');
    }

    public function testStringToCollectionEmptyKeyException(): void
    {
        $this->expectException(EmptyKeyException::class);
        $this->trait->stringToCollection(['foobar'], '', 'name');
    }

    public function testStringToCollectionEmptyPropertyException(): void
    {
        $this->expectException(EmptyPropertyException::class);
        $this->trait->stringToCollection(['foobar'], 'key', '');
    }

    public function testStringToCollectionPropertyNotFoundException(): void
    {
        $this->expectException(PropertyNotFoundException::class);
        $this->trait->stringToCollection(['foo' => 'bar'], 'key', 'name');
    }

    /**
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws EmptyPropertyException
     * @throws PropertyNotFoundException
     * @throws InvalidItemException
     */
    public function testCollectionToString(): void
    {
        $result = $this->trait->collectionToString(['array' => [['name' => 'foobar']]], 'array', 'name');
        $this->assertEquals(['array' => 'foobar'], $result);
    }

    public function testCollectionToStringEmptyObjectException(): void
    {
        $this->expectException(EmptyObjectException::class);
        $this->trait->collectionToString([], 'key', 'name');
    }

    public function testCollectionToStringEmptyKeyException(): void
    {
        $this->expectException(EmptyKeyException::class);
        $this->trait->collectionToString(['foobar'], '', 'name');
    }

    public function testCollectionToStringEmptyPropertyException(): void
    {
        $this->expectException(EmptyPropertyException::class);
        $this->trait->collectionToString(['foobar'], 'key', '');
    }

    public function testCollectionToStringPropertyNotFoundException(): void
    {
        $this->expectException(PropertyNotFoundException::class);
        $this->trait->collectionToString(['foo' => 'bar'], 'key', 'name');
    }

    public function testMergerShouldReturnExpectedResult(): void
    {
        $result = $this->trait->mergeNormalizedObjects(self::FIXTURE_1, self::FIXTURE_2);

        $this->assertSame(self::EXPECTED_1, $result);
    }

    public function testMergerWithOverwriteOptionShouldReturnExpectedResult(): void
    {
        $result = $this->trait->mergeNormalizedObjects(self::FIXTURE_1, self::FIXTURE_2, true);

        $this->assertSame(self::EXPECTED_2, $result);
    }

    protected function setUp(): void
    {
        $this->trait = $this->getObjectForTrait('App\Serializer\Traits\Utils');
    }
}
