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

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Make sure tag is not already in database
 * @author Matthias Morin <mat@tangoman.io>
 */
class TagFactory implements FactoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * TagFactory constructor.
     *
     * @param EntityManagerInterface $em
     * @param TagRepository          $tagRepository
     */
    public function __construct(EntityManagerInterface $em, TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
        $this->em = $em;
    }

    /**
     * Create new Tag when missing or return existing Tag from database
     * @param array $tag
     *
     * @return Tag
     * @throws \Exception
     */
    public function create(array $tag): Tag
    {
        if ($tag['name'] === '' || $tag['name'] === null) {
            throw new \Exception('Tag name cannot be empty', 1);
        }

        $tagEntity = $this->tagRepository->findOneBy(['name' => $tag['name']]);
        if ($tagEntity) {
            return $tagEntity;
        }

        $tagEntity = new Tag();

        if ($tag['name'] ?? null) {
            $tagEntity->setName($tag['name']);
        }

        if ($tag['clips'] ?? null) {
            foreach ($tag['clips'] as $event) {
                $tagEntity->addClip($event);
            }
        }

        $this->em->persist($tagEntity);
        $this->em->flush();

        return $tagEntity;
    }
}