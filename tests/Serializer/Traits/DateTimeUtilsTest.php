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

use App\Serializer\Traits\Utils;
use App\Serializer\Exception\InvalidDateException;
use App\Serializer\Exception\InvalidDurationException;
use PHPUnit\Framework\TestCase;

class DateTimeUtilsTest extends TestCase
{
    /**
     * @var Utils
     */
    private $trait;

    protected function setUp()
    {
        $this->trait = $this->getObjectForTrait('App\Serializer\Traits\DateTimeUtils');
    }

    /**
     * testDatetimeToDate
     */
    public function testDatetimeToDate()
    {
        $result = $this->trait->datetimeToDate(['date' => '2020-01-01T00:00:00+02:00'], 'date');
        $this->assertEquals(['date' => '2020-01-01'], $result);
    }

    public function testDatetimeToDateInvalidDateExceptionEmpty()
    {
        $this->expectException(InvalidDateException::class);
        $this->trait->datetimeToDate(['date' => ''], 'date');
    }

    public function testDatetimeToDateInvalidDateExceptionTooShort()
    {
        $this->expectException(InvalidDateException::class);
        $this->trait->datetimeToDate(['date' => '2020'], 'date');
    }

    /**
     * testDurationToString
     */
    public function testDurationToString()
    {
        $result = $this->trait->durationToString(['duration' => 3900], 'duration');
        $this->assertEquals(['duration' => '01:05'], $result);
    }

    /**
     * testDurationToInteger
     */
    public function testDurationToInteger()
    {
        $result = $this->trait->durationToInteger(['duration' => '01:05'], 'duration');
        $this->assertEquals(['duration' => 3900], $result);
    }

    public function testDurationToIntegerInvalidDurationException()
    {
        $this->expectException(InvalidDurationException::class);
        $this->trait->durationToInteger(['duration' => ''], 'duration');
    }

    public function testDurationToIntegerInvalidDurationExceptionTooShort()
    {
        $this->expectException(InvalidDurationException::class);
        $this->trait->durationToInteger(['duration' => '02'], 'duration');
    }

    public function testDurationToIntegerInvalidDurationExceptionTooLong()
    {
        $this->expectException(InvalidDurationException::class);
        $this->trait->durationToInteger(['duration' => '01:05:59'], 'duration');
    }
}
