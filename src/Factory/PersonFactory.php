<?php

/**
 * This file is part of the TangoMan package.

 * Copyright (c) 2020 "Matthias Morin" <mat@tangoman.io>

 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Person;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Make sure person is not already in database
 * @author Matthias Morin <mat@tangoman.io>
 */
class PersonFactory implements FactoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * PersonFactory constructor.
     *
     * @param EntityManagerInterface $em
     * @param PersonRepository       $personRepository
     */
    public function __construct(EntityManagerInterface $em, PersonRepository $personRepository)
    {
        $this->personRepository = $personRepository;
        $this->em = $em;
    }

    /**
     * Create new Person when missing or return existing Person from database
     *
     * @param array $person Normalized Person
     *
     * @return Person
     * @throws \Exception
     */
    public function create(array $person): Person
    {
        if (!($person['name'] ?? null) && !($person['alternateName'] ?? null)) {
            throw new \Exception('Person name and alternateName cannot both be empty', 1);
        }

        if ($person['name'] ?? null) {
            $personEntity = $this->personRepository->findOneBy(['name' => $person['name']]);
            if ($personEntity) {
                return $personEntity;
            }
        }

        if ($person['alternateName'] ?? null) {
            $personEntity = $this->personRepository->findOneBy(['alternateName' => $person['alternateName']]);
            if ($personEntity) {
                return $personEntity;
            }
        }

        $personEntity = new Person();

        if ($person['alternateName'] ?? null) {
            $personEntity->setAlternateName($person['alternateName']);
        }
        if ($person['name'] ?? null) {
            $personEntity->setName($person['name']);
        }

        if ($person['clips'] ?? null) {
            foreach ($person['clips'] as $event) {
                $personEntity->addClip($event);
            }
        }

        $this->em->persist($personEntity);
        $this->em->flush();

        return $personEntity;
    }
}