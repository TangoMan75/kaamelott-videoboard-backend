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

use App\Entity\Episode;
use App\Repository\EpisodeRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Make sure episode is not already in database
 * @author Matthias Morin <mat@tangoman.io>
 */
class EpisodeFactory implements FactoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EpisodeRepository
     */
    private $episodeRepository;

    /**
     * EpisodeFactory constructor.
     *
     * @param EntityManagerInterface $em
     * @param EpisodeRepository          $episodeRepository
     */
    public function __construct(EntityManagerInterface $em, EpisodeRepository $episodeRepository)
    {
        $this->episodeRepository = $episodeRepository;
        $this->em = $em;
    }

    /**
     * Create new Episode when missing or return existing Episode from database
     * @param array $episode
     *
     * @return Episode
     * @throws \Exception
     */
    public function create(array $episode): Episode
    {
        if (!($episode['name'] ?? null) && !($episode['episodeNumber'] ?? null)) {
            throw new \Exception('Episode name and episodeNumber cannot both be empty', 1);
        }

        if ($episode['name'] ?? null) {
            $episodeEntity = $this->episodeRepository->findOneBy(['name' => $episode['name']]);
            if ($episodeEntity) {
                return $episodeEntity;
            }
        }

        if ($episode['episodeNumber'] ?? null) {
            $episodeEntity = $this->episodeRepository->findOneBy(['episodeNumber' => $episode['episodeNumber']]);
            if ($episodeEntity) {
                return $episodeEntity;
            }
        }

        $episodeEntity = new Episode();

        if ($episode['name'] ?? null) {
            $episodeEntity->setName($episode['name']);
        }

        if ($episode['episodeNumber'] ?? null) {
            $episodeEntity->setEpisodeNumber($episode['episodeNumber']);
        }

        if ($episode['clips'] ?? null) {
            foreach ($episode['clips'] as $event) {
                $episodeEntity->addClip($event);
            }
        }

        $this->em->persist($episodeEntity);
        $this->em->flush();

        return $episodeEntity;
    }
}