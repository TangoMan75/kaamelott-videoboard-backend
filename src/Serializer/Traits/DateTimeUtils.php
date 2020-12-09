<?php

/**
 * This file is part of the TangoMan package.
 *
 * Copyright (c) 2020 "Matthias Morin" <mat@tangoman.io>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace App\Serializer\Traits;

use App\Serializer\Exception\EmptyKeyException;
use App\Serializer\Exception\EmptyObjectException;
use App\Serializer\Exception\InvalidDateException;
use App\Serializer\Exception\InvalidDurationException;
use App\Serializer\Exception\PropertyNotFoundException;

/**
 * Trait DateTimeUtils
 *
 * @version 0.1.1
 * @author  "Matthias Morin" <mat@tangoman.io>
 */
trait DateTimeUtils
{
    /**
     * Truncate date
     *
     * @param array  $normalizedObject
     * @param string $key
     *
     * @return array
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws InvalidDateException
     * @throws PropertyNotFoundException
     */
    public function datetimeToDate(array $normalizedObject, string $key): array
    {
        if ($normalizedObject === []) {
            throw new EmptyObjectException();
        }

        if ($key === '') {
            throw new EmptyKeyException();
        }

        if (!array_key_exists($key, $normalizedObject)) {
            throw new PropertyNotFoundException();
        }

        if (strlen($normalizedObject[$key]) < 10 ) {
            throw new InvalidDateException();
        }

        $normalizedObject[$key] = substr($normalizedObject[$key], 0, 10);

        return $normalizedObject;
    }

    /**
     * Converts duration in seconds to '%02d:%02d' formatted string
     *
     * @param array  $normalizedObject
     * @param string $key
     *
     * @return array
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws InvalidDurationException
     * @throws PropertyNotFoundException
     */
    public function durationToString(array $normalizedObject, string $key): array
    {
        if ($normalizedObject === []) {
            throw new EmptyObjectException();
        }

        if ($key === '') {
            throw new EmptyKeyException();
        }

        if (!array_key_exists($key, $normalizedObject)) {
            throw new PropertyNotFoundException();
        }

        if (!is_integer($normalizedObject[$key])) {
            throw new InvalidDurationException();
        }

        // convert duration integer to string
        $hours = intval($normalizedObject[$key] / 3600);
        $minutes = intval(($normalizedObject[$key] % 3600) / 60);

        $normalizedObject[$key] = sprintf('%02d:%02d', $hours, $minutes);

        return $normalizedObject;
    }

    /**
     * Convert string formatted duration to integer
     *
     * @param array  $normalizedObject
     * @param string $key
     *
     * @return array
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws InvalidDurationException
     * @throws PropertyNotFoundException
     */
    public function durationToInteger(array $normalizedObject, string $key): array
    {
        if ($normalizedObject === []) {
            throw new EmptyObjectException();
        }

        if ($key === '') {
            throw new EmptyKeyException();
        }

        if (!array_key_exists($key, $normalizedObject)) {
            throw new PropertyNotFoundException();
        }

        $duration = explode(':', $normalizedObject[$key]);
        if (count($duration) !== 2) {
            throw new InvalidDurationException();
        }

        $normalizedObject[$key] = (intval($duration[0]) * 3600) + (intval($duration[1]) * 60);

        return $normalizedObject;
    }


    /**
     * Converts Europass date format to string
     *
     * @param array  $normalizedObject
     * @param string $key
     *
     * @return array
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws InvalidDateException
     * @throws PropertyNotFoundException
     */
    public function convertEuropassDate(array $normalizedObject, string $key): array
    {
        if ($normalizedObject === []) {
            throw new EmptyObjectException();
        }

        if ($key === '') {
            throw new EmptyKeyException();
        }

        if (!array_key_exists($key, $normalizedObject)) {
            throw new PropertyNotFoundException();
        }

        if (!is_array($normalizedObject[$key])) {
            throw new InvalidDateException();
        }

        $result = [
            'year'  => '0000',
            'month' => '01',
            'day'   => '01',
        ];

        if (array_key_exists('@year', $normalizedObject[$key])) {
            $result['year'] = ltrim($normalizedObject[$key]['@year'], '-');
        }
        if (array_key_exists('@month', $normalizedObject[$key])) {
            $result['month'] = ltrim($normalizedObject[$key]['@month'], '-');
        }
        if (array_key_exists('@day', $normalizedObject[$key])) {
            $result['day'] = ltrim($normalizedObject[$key]['@day'], '-');
        }

        $normalizedObject[$key] = implode('-', $result);

        return $normalizedObject;
    }
}
