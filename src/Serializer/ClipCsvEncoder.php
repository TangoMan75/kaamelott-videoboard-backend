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

namespace App\Serializer;

use App\Factory\EpisodeFactory;
use App\Factory\PersonFactory;
use App\Factory\TagFactory;
use App\Serializer\Service\RelationshipResolverService;
use App\Serializer\Traits\Utils;
use Psr\Log\LoggerInterface;

class ClipCsvEncoder extends AbstractCsvEncoder
{
    use Utils;

    const FORMAT = 'clip:csv';

    /**
     * @var EpisodeFactory
     */
    private $episodeFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var RelationshipResolverService
     */
    private $relationshipResolverService;

    /**
     * @var TagFactory
     */
    private $tagFactory;

    public function __construct(
        EpisodeFactory $episodeFactory,
        LoggerInterface $logger,
        PersonFactory $personFactory,
        RelationshipResolverService $relationshipResolverService,
        TagFactory $tagFactory
    ) {
        parent::__construct();

        $this->episodeFactory = $episodeFactory;
        $this->logger = $logger;
        $this->personFactory = $personFactory;
        $this->relationshipResolverService = $relationshipResolverService;
        $this->tagFactory = $tagFactory;
    }

    public function supportsEncoding(string $format): bool
    {
        return self::FORMAT === $format;
    }

    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }

    protected function transformForExport(array $item): array
    {
        $item = $this->removeNullFields($item);

        try {
            $item = $this->escapeLineBreaks($item, 'citation');
        } catch (\Exception $exception) {
            $this->logger->warning($exception);
        }

        try {
            $item = $this->collectionToString($item, 'characters', 'alternateName');
        } catch (\Exception $exception) {
            $this->logger->warning($exception);
        }

        try {
            $item = $this->collectiontoString($item, 'tags', 'name');
        } catch (\Exception $exception) {
            $this->logger->warning($exception);
        }

        return $item;
    }

    protected function transformForImport(array $item): array
    {
        $item = $this->removeNullFields($item);

        try {
            $item = $this->unescapeLineBreaks($item, 'citation');
        } catch (\Exception $exception) {
            $this->logger->warning($exception);
        }

        try {
            $item = $this->relationshipResolverService->fixRelationship(
                $item,
                'partOfEpisode',
                $this->episodeFactory
            );
        } catch (\Exception $exception) {
            $this->logger->warning($exception);
        }

        try {
            $item = $this->stringToCollection($item, 'characters', 'alternateName');
        } catch (\Exception $exception) {
            $this->logger->warning($exception);
        }

        try {
            $item = $this->relationshipResolverService->fixRelationships(
                $item,
                'characters',
                'alternateName',
                $this->personFactory
            );
        } catch (\Exception $exception) {
            $this->logger->warning($exception);
        }

        try {
            $item = $this->stringToCollection($item, 'tags', 'name');
        } catch (\Exception $exception) {
            $this->logger->warning($exception);
        }

        try {
            $item = $this->relationshipResolverService->fixRelationships(
                $item,
                'tags',
                'name',
                $this->tagFactory
            );
        } catch (\Exception $exception) {
            $this->logger->warning($exception);
        }

        return $item;
    }
}
