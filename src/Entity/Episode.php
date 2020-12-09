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
use App\Entity\Traits\EpisodeHasClips;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A media episode (e.g. TV, radio, video game) which can be part of a series or season.
 *
 * @see http://schema.org/Episode Documentation on Schema.org
 *
 * @author "Matthias Morin" <mat@tangoman.io>
 *
 * @ORM\Entity
 * @ApiResource(
 *     iri="http://schema.org/Episode",
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
 */
class Episode
{
    use EpisodeHasClips;

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string the name of the item
     *
     * @ORM\Column(type="text", unique=true)
     * @ApiProperty(iri="http://schema.org/name")
     * @Assert\Type(type="string")
     * @Assert\NotNull
     * @Groups({"export", "write:episode", "read:episode", "read:clip"})
     */
    private $name;

    /**
     * @var string|null position of the episode within an ordered group of episodes
     *
     * @ORM\Column(type="text", nullable=true)
     * @ApiProperty(iri="http://schema.org/episodeNumber")
     * @Assert\Type(type="string")
     * @Groups({"export", "write:episode", "read:episode", "read:clip"})
     */
    private $episodeNumber;

    public function __construct()
    {
        $this->clips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setEpisodeNumber(?string $episodeNumber): void
    {
        $this->episodeNumber = $episodeNumber;
    }

    public function getEpisodeNumber(): ?string
    {
        return $this->episodeNumber;
    }
}
