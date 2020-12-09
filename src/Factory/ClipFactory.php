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

namespace App\Factory;

use App\Entity\Clip;
use App\Repository\ClipRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Make sure clip is not already in database
 * @author Matthias Morin <mat@tangoman.io>
 */
class ClipFactory implements FactoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ClipRepository
     */
    private $clipRepository;

    /**
     * ClipFactory constructor.
     *
     * @param EntityManagerInterface $em
     * @param ClipRepository         $clipRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        ClipRepository $clipRepository
    ) {
        $this->em = $em;
        $this->clipRepository = $clipRepository;
    }

    /**
     * Create new Clip when missing or return existing Clip from database
     *
     * @param array $clip
     *
     * @return Clip
     * @throws \Exception
     */
    public function create(array $clip): Clip
    {
        if ($clip['name'] === '' || $clip['name'] === null) {
            throw new \Exception('Clip name cannot be empty', 1);
        }

        $clipEntity = $this->clipRepository->findOneBy(['name' => $clip['name']]);
        if ($clipEntity) {
            return $clipEntity;
        }

        $clipEntity = new Clip();

        if ($clip['name'] ?? null) {
            $clipEntity->setName($clip['name']);
        }
        if ($clip['url'] ?? null) {
            $clipEntity->setUrl($clip['url']);
        }
        if ($clip['citation'] ?? null) {
            $clipEntity->setCitation($clip['citation']);
        }
        if ($clip['thumbnailUrl'] ?? null) {
            $clipEntity->setThumbnailUrl($clip['thumbnailUrl']);
        }
        if ($clip['autoplay'] ?? null) {
            $clipEntity->setAutoplay($clip['autoplay']);
        }
        if ($clip['duration'] ?? null) {
            $clipEntity->setDuration($clip['duration']);
        }

        if ($clip['partOfEpisode'] ?? null) {
            $clipEntity->setPartOfEpisode($episode);
        }
        if ($clip['characters'] ?? null) {
            foreach ($clip['characters'] as $person) {
                $clipEntity->addPerson($person);
            }
        }
        if ($clip['tags'] ?? null) {
            foreach ($clip['tags'] as $tag) {
                $clipEntity->addTag($tag);
            }
        }

        $this->em->persist($clipEntity);
        $this->em->flush();

        return $clipEntity;
    }
}