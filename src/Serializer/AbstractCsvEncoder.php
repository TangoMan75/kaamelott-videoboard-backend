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

use App\Serializer\Traits\Utils;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

abstract class AbstractCsvEncoder implements EncoderInterface, DecoderInterface
{
    use Utils;

    /**
     * @var CsvEncoder
     */
    private $encoder;

    public function __construct()
    {
        // CsvEncoder cannot autowire as a service
        $this->encoder = new CsvEncoder();
    }

    public function encode($data, string $format, array $context = []): string
    {
        // encode data as csv
        return $this->encoder->encode(array_map([$this, 'transformForExport'], $data), 'csv');
    }

    public function decode(string $data, string $format, array $context = []): array
    {
        // decode data as array with csv decoder
        return array_map([$this, 'transformForImport'], $this->encoder->decode($data, 'csv'));
    }

    abstract public function supportsEncoding(string $format): bool;

    abstract public function supportsDecoding(string $format): bool;

    abstract protected function transformForExport(array $item): array;

    abstract protected function transformForImport(array $item): array;
}
