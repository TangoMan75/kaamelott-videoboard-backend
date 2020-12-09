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

namespace App\Serializer\Service;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Factory\FactoryInterface;
use App\Serializer\Exception\EmptyObjectException;
use App\Serializer\Exception\EmptyKeyException;
use App\Serializer\Exception\EmptyPropertyException;
use App\Serializer\Exception\PropertyNotFoundException;
use Psr\Log\LoggerInterface;

class RelationshipResolverService
{
    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RelationshipManager constructor.
     *
     * @param IriConverterInterface $iriConverter
     * @param LoggerInterface       $logger
     */
    public function __construct(IriConverterInterface $iriConverter, LoggerInterface $logger)
    {
        $this->iriConverter = $iriConverter;
        $this->logger = $logger;
    }

    /**
     * Creates relationship in normalized item with appropriate IRI
     * https://en.wikipedia.org/wiki/Internationalized_Resource_Identifier
     *
     * @param array            $normalizedObject
     * @param string           $key
     * @param FactoryInterface $childEntityFactory
     *
     * @return array
     * @throws EmptyObjectException
     * @throws EmptyPropertyException
     * @throws PropertyNotFoundException
     */
    public function fixRelationship(array $normalizedObject, string $key, FactoryInterface $childEntityFactory): array
    {
        if ($normalizedObject === []) {
            throw new EmptyObjectException();
        }

        if ($key === '') {
            throw new EmptyPropertyException();
        }

        if (!array_key_exists($key, $normalizedObject)) {
            throw new PropertyNotFoundException();
        }

        $iri = null;
        try {
            $entity = $childEntityFactory->create($normalizedObject[$key]);
            $iri = $this->iriConverter->getIriFromItem($entity);
        } catch (\Exception $exception) {
            $this->logger->error($exception);
        }

        $normalizedObject[$key] = $iri;

        return $normalizedObject;
    }

    /**
     * Recursively creates relationship in normalized item with appropriate IRI
     *
     * @param array            $normalizedObject
     * @param string           $key
     * @param string           $propertyName
     * @param FactoryInterface $childEntityFactory
     *
     * @return array
     * @throws EmptyObjectException
     * @throws EmptyKeyException
     * @throws EmptyPropertyException
     * @throws PropertyNotFoundException
     */
    public function fixRelationships(
        array $normalizedObject,
        string $key,
        string $propertyName,
        FactoryInterface $childEntityFactory
    ): array
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
        foreach($normalizedObject[$key] as $item) {
            $iri = null;
            try {
                $entity = $childEntityFactory->create($item);
                $iri = $this->iriConverter->getIriFromItem($entity);
            } catch (\Exception $exception) {
                $this->logger->error($exception);
            }
            $result[] = $iri;
        }

        $normalizedObject[$key] = $result;

        return $normalizedObject;
    }
}
