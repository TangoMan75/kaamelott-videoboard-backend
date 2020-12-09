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

use Psr\Log\LoggerInterface;

class EpisodeJsonEncoder extends AbstractJsonEncoder
{
    const FORMAT = 'episode:json';

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        parent::__construct();

        $this->logger = $logger;
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

        return $item;
    }

    protected function transformForImport(array $item): array
    {
        $item = $this->removeNullFields($item);

        return $item;
    }
}
