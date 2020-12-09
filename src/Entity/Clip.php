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
use App\Entity\Traits\ClipsBelongToEpisode;
use App\Entity\Traits\ClipsHavePeople;
use App\Entity\Traits\ClipsHaveTags;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A short TV or radio program or a segment/part of a program.
 *
 * @see http://schema.org/Clip Documentation on Schema.org
 *
 * @author "Matthias Morin" <mat@tangoman.io>
 *
 * @ORM\Entity
 * @ApiResource(
 *     iri="http://schema.org/Clip",
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
 * @UniqueEntity(fields={"name","url","thumbnailUrl"})
 */
class Clip
{
    use ClipsBelongToEpisode;
    use ClipsHavePeople;
    use ClipsHaveTags;

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
     * @Groups({"export", "write:clip", "read:clip"})
     */
    private $name;

    /**
     * @var string URL of the item
     *
     * @ORM\Column(type="text", unique=true)
     * @ApiProperty(iri="http://schema.org/url")
     * @Assert\Url
     * @Assert\NotNull
     * @Groups({"export", "write:clip", "read:clip"})
     */
    private $url;

    /**
     * @var string|null a citation or reference to another creative work, such as another publication, web page, scholarly article, etc
     *
     * @ORM\Column(type="text", nullable=true, unique=true)
     * @ApiProperty(iri="http://schema.org/citation")
     * @Assert\Type(type="string")
     * @Groups({"export", "write:clip", "read:clip"})
     */
    private $citation;

    /**
     * @var string|null a thumbnail image relevant to the Thing
     *
     * @ORM\Column(type="text", nullable=true, unique=true)
     * @ApiProperty(iri="http://schema.org/thumbnailUrl")
     * @Assert\Url
     * @Groups({"export", "write:clip", "read:clip"})
     */
    private $thumbnailUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Type(type="string")
     * @Groups({"export", "write:clip", "read:clip"})
     */
    private $autoplay;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer")
     * @Groups({"export", "write:clip", "read:clip"})
     */
    private $duration;

    public function __construct()
    {
        $this->characters = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setCitation(?string $citation): void
    {
        $this->citation = $citation;
    }

    public function getCitation(): ?string
    {
        return $this->citation;
    }

    public function setThumbnailUrl(?string $thumbnailUrl): void
    {
        $this->thumbnailUrl = $thumbnailUrl;
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->thumbnailUrl;
    }

    public function setAutoplay(?string $autoplay): void
    {
        $this->autoplay = $autoplay;
    }

    public function getAutoplay(): ?string
    {
        return $this->autoplay;
    }

    public function setDuration(?int $duration): void
    {
        $this->duration = $duration;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }
}
