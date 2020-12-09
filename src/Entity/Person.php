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

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\PeopleBelongToClips;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A person (alive, dead, undead, or fictional).
 *
 * @see http://schema.org/Person Documentation on Schema.org
 *
 * @author "Matthias Morin" <mat@tangoman.io>
 *
 * @ORM\Entity
 * @ApiResource(
 *     iri="http://schema.org/Person",
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_ADMIN')"}
 *     },
 *     itemOperations={
 *         "delete"={"security"="is_granted('ROLE_ADMIN')"},
 *         "get",
 *         "patch"={"security"="is_granted('ROLE_ADMIN')"},
 *         "put"={"security"="is_granted('ROLE_ADMIN')"}
 *     }
 * )
 * @UniqueEntity(fields={"name","alternateName"})
 */
class Person
{
    use PeopleBelongToClips;

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string an alias for the item
     *
     * @ORM\Column(type="text", unique=true)
     * @ApiProperty(iri="http://schema.org/alternateName")
     * @Assert\Type(type="string")
     * @Assert\NotNull
     * @Groups({"export", "write:person", "read:person", "read:clip"})
     */
    private $alternateName;

    /**
     * @var string the name of the item
     *
     * @ORM\Column(type="text", unique=true)
     * @ApiProperty(iri="http://schema.org/name")
     * @Assert\Type(type="string")
     * @Assert\NotNull
     * @Groups({"export", "write:person", "read:person", "read:clip"})
     */
    private $name;

    public function __construct()
    {
        $this->clips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAlternateName(string $alternateName): void
    {
        $this->alternateName = $alternateName;
    }

    public function getAlternateName(): string
    {
        return $this->alternateName;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

