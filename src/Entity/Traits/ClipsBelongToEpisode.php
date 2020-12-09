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

namespace App\Entity\Traits;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\Clip;
use App\Entity\Episode;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait ClipsBelongToEpisode
 *
 * This trait defines the OWNING (Mapping) side of a ManyToOne bidirectional relationship.
 * Ownership is ALWAYS on the MANY side of the relationship (which may be confusing sometimes).
 *
 * 1. Requires `Owner` entity to implement `$items` property with `OneToMany` and `mappedBy="clips"` annotation.
 * 2. Requires `Owner` entity to implement linkItem(Clip $clip) public method.
 * 3. Optionally `Owner` entity may have `cascade={"remove"}` to avoid orphan `Clip` objects on `Episode` deletion.
 * 4. `cascade={"persist"}` on this side on the relationship is fine (applies to one `Episode` only).
 *
 * @author  "Matthias Morin" <mat@tangoman.io>
 */
trait ClipsBelongToEpisode
{
    /**
     * @var Episode|null the episode to which this clip belongs
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Episode", inversedBy="clips", cascade={"persist"})
     * @ApiProperty(iri="http://schema.org/partOfEpisode")
     * @Groups({"export", "write:clip", "read:clip"})
     */
    private $partOfEpisode;

    public function getPartOfEpisode(): ?Episode
    {
        return $this->partOfEpisode;
    }

    public function setPartOfEpisode(?Episode $episode): void
    {
        if ($episode) {
            $this->linkEpisode($episode);

            /** @var Clip $this */
            $episode->linkClip($this);
        } else {
            $this->unLinkEpisode();
        }
    }

    public function linkEpisode(Episode $episode): void
    {
        $this->partOfEpisode = $episode;
    }

    public function unLinkEpisode(): void
    {
        $this->partOfEpisode = null;
    }
}
