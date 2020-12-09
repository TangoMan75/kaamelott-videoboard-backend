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

use App\Serializer\Exception\EmptyObjectException;
use App\Serializer\Exception\EmptyKeyException;
use App\Serializer\Exception\EmptyPropertyException;
use App\Serializer\Exception\InvalidItemException;
use App\Serializer\Exception\PropertyNotFoundException;

/**
 * This trait allows a transformer to use a handful of helper functions.
 * @author  "Mattias Morin" <mat@tangoman.io>
 */
trait Utils
{
    /**
     * Remove all empty properties from normalized entity
     *
     * @param array $normalizedObject
     *
     * @return array
     */
    public function removeNullFields(array $normalizedObject): array
    {
        foreach ($normalizedObject as $propertyName => $value) {
            if ($value === null || $value === [] || $value === '') {
                unset($normalizedObject[$propertyName]);
            }
        }

        return $normalizedObject;
    }

    /**
     * Remove all empty scalar properties from normalized entity
     *
     * @param array $normalizedObject
     *
     * @return array
     */
    public function removeNullScalars(array $normalizedObject): array
    {
        foreach ($normalizedObject as $propertyName => $value) {
            if ($value === null || $value === '') {
                unset($normalizedObject[$propertyName]);
            }
        }

        return $normalizedObject;
    }

    public function escapeLineBreaks(array $normalizedObject, string $key): array
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

        $normalizedObject[$key] = str_replace("\n", '\n', $normalizedObject[$key]);

        return $normalizedObject;
    }

    public function unescapeLineBreaks(array $normalizedObject, string $key): array
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

        $normalizedObject[$key] = str_replace('\n', "\n",$normalizedObject[$key]);

        return $normalizedObject;
    }

    public function sortArray(array $normalizedObject, string $key): array
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

        sort($normalizedObject[$key]);

        return $normalizedObject;
    }

    /**
     * Transforms string to integer from property
     *
     * @param array  $normalizedObject
     * @param string $key
     *
     * @return array
     * @throws EmptyKeyException
     * @throws EmptyObjectException
     * @throws PropertyNotFoundException
     */
    public function stringToInteger(array $normalizedObject, string $key): array
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

        $normalizedObject[$key] = intval($normalizedObject[$key]);

        return $normalizedObject;
    }

    /**
     * Transforms string to boolean from property
     *
     * @param array  $normalizedObject
     * @param string $key
     *
     * @return array
     * @throws EmptyObjectException
     * @throws EmptyKeyException
     * @throws PropertyNotFoundException
     */
    public function stringToBoolean(array $normalizedObject, string $key): array
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

        if ($normalizedObject[$key] === 'true') {

            $normalizedObject[$key] = true;
        } else {
            $normalizedObject[$key] = false;
        }

        return $normalizedObject;
    }

    /**
     * Transforms comma separated string to simple array (trimming each value) from property
     *
     * @param array  $normalizedObject
     * @param string $key
     *
     * @return array
     * @throws EmptyObjectException
     * @throws EmptyKeyException
     * @throws PropertyNotFoundException
     */
    public function stringToArray(array $normalizedObject, string $key): array
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

        $normalizedObject[$key] = array_map('trim', explode(',', $normalizedObject[$key]));

        return $normalizedObject;
    }

    /**
     * Transforms simple array to comma separated string (trimming each value) from property
     *
     * @param array  $normalizedObject
     * @param string $key
     *
     * @return array
     * @throws EmptyObjectException
     * @throws EmptyKeyException
     * @throws PropertyNotFoundException
     * @throws InvalidItemException
     */
    public function arrayToString(array $normalizedObject, string $key): array
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
            throw new InvalidItemException();
        }

        $normalizedObject[$key] = implode(', ', array_map('trim', $normalizedObject[$key]));

        return $normalizedObject;
    }

    /**
     * Transforms string to associative array from key with given property name
     *
     * @param array  $normalizedObject
     * @param string $key
     * @param string $propertyName
     *
     * @return array
     * @throws EmptyObjectException
     * @throws EmptyKeyException
     * @throws EmptyPropertyException
     * @throws PropertyNotFoundException
     */
    public function stringToAssociativeArray(array $normalizedObject, string $key, string $propertyName): array
    {
        if ($normalizedObject === []) {
            throw new EmptyObjectException();
        }

        if ($key === '') {
            throw new EmptyKeyException();
        }

        if ($propertyName === '') {
            throw new EmptyPropertyException();
        }

        if (!array_key_exists($key, $normalizedObject)) {
            throw new PropertyNotFoundException();
        }

        $normalizedObject[$key] = [$propertyName => $normalizedObject[$key]];

        return $normalizedObject;
    }

    /**
     * Replaces normalized object value to given contained value by name
     *
     * @param array  $normalizedObject
     * @param string $key
     * @param string $propertyName
     *
     * @return array
     * @throws EmptyObjectException
     * @throws EmptyKeyException
     * @throws EmptyPropertyException
     * @throws PropertyNotFoundException
     * @throws InvalidItemException
     */
    public function associativeArrayToString(array $normalizedObject, string $key, string $propertyName): array
    {
        if ($normalizedObject === []) {
            throw new EmptyObjectException();
        }

        if ($key === '') {
            throw new EmptyKeyException();
        }

        if ($propertyName === '') {
            throw new EmptyPropertyException();
        }

        if (!array_key_exists($key, $normalizedObject)) {
            throw new PropertyNotFoundException();
        }

        if (!is_array($normalizedObject[$key])) {
            throw new InvalidItemException();
        }

        $normalizedObject[$key] = $normalizedObject[$key][$propertyName];

        return $normalizedObject;
    }

    /**
     * Transforms comma separated string to collection of normalized objects (trimming each value) from given property
     *
     * @param array  $normalizedObject
     * @param string $key
     * @param string $propertyName
     *
     * @return array
     * @throws EmptyObjectException
     * @throws EmptyKeyException
     * @throws EmptyPropertyException
     * @throws PropertyNotFoundException
     */
    public function stringToCollection(array $normalizedObject, string $key, string $propertyName): array
    {
        if ($normalizedObject === []) {
            throw new EmptyObjectException();
        }

        if ($key === '') {
            throw new EmptyKeyException();
        }

        if ($propertyName === '') {
            throw new EmptyPropertyException();
        }

        if (!array_key_exists($key, $normalizedObject)) {
            throw new PropertyNotFoundException();
        }

        $result = [];
        foreach (explode(',', $normalizedObject[$key]) as $value) {
            $result[] = [$propertyName => trim($value)];
        }

        $normalizedObject[$key] = $result;

        return $normalizedObject;
    }

    /**
     * Transforms collection of normalized objects to comma separated string (trimming each value) from given property
     *
     * @param array  $normalizedObject
     * @param string $key
     * @param string $propertyName
     *
     * @return array
     * @throws EmptyObjectException
     * @throws EmptyKeyException
     * @throws EmptyPropertyException
     * @throws InvalidItemException
     * @throws PropertyNotFoundException
     */
    public function collectionToString(array $normalizedObject, string $key, string $propertyName): array
    {
        if ($normalizedObject === []) {
            throw new EmptyObjectException();
        }

        if ($key === '') {
            throw new EmptyKeyException();
        }

        if ($propertyName === '') {
            throw new EmptyPropertyException();
        }

        if (!array_key_exists($key, $normalizedObject)) {
            throw new PropertyNotFoundException();
        }

        $temp = [];
        foreach ($normalizedObject[$key] as $value) {
            if (!is_array($value)) {
                throw new InvalidItemException();
            }
            $temp[] = $value[$propertyName];
        }

        $normalizedObject[$key] = implode(', ', array_map('trim', $temp));

        return $normalizedObject;
    }

    /**
     * Merge normalized objects recursively
     *
     * @param array $source
     * @param array $destination
     * @param bool  $overwrite
     *
     * @return array
     */
    public function mergeNormalizedObjects(array $source, array $destination, bool $overwrite = false): array
    {
        $source = $this->removeNullScalars($source);
        $destination = $this->removeNullScalars($destination);

        /* @var $tableNames string[] */
        $tableNames = array_unique(array_merge(array_keys($destination), array_keys($source)), SORT_REGULAR);

        // recursive merging of contained arrays
        foreach ($tableNames as $key) {
            if (
                is_array($source[$key] ?? null) &&
                is_array($destination[$key] ?? null)
            ) {
                if ($overwrite) {
                    $destination[$key] = array_unique(array_merge($source[$key], $destination[$key]), SORT_REGULAR);
                } else {
                    $source[$key] = array_unique(array_merge($destination[$key], $source[$key]), SORT_REGULAR);
                }
            }
        }

        // merge arrays
        if ($overwrite) {
            $result = array_merge($source, $destination);
        } else {
            $result = array_merge($destination, $source);
        }

        // remove `id` column from table to avoid conflict
        unset($result['id']);

        return $result;
    }
}
