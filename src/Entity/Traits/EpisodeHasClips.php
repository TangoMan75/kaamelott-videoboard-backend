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

use App\Entity\Clip;
use App\Entity\Episode;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait EpisodeHasClips
 *
 * This trait defines the INVERSE side of a OneToMany bidirectional relationship.
 *
 * 1. Requires `Clip` entity to implement `$episode` property with `ManyToOne` and `inversedBy="items"` annotation.
 * 2. Requires `Clip` entity to implement linkEpisode(Episode $episode) public method.
 * 3. Requires formType to own `'by_reference => false,` attribute to force use of `add` and `remove` methods.
 * 4. Entity constructor must initialize ArrayCollection object
 *     $this->items = new ArrayCollection();
 * 5. Add use statement
 *     use Doctrine\Common\Collections\ArrayCollection;
 *
 * @author  "Matthias Morin" <mat@tangoman.io>
 */
trait EpisodeHasClips
{
    /**
     * @var Collection<Clip>|null
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Clip", mappedBy="partOfEpisode", cascade={"persist"})
     * @ORM\OrderBy({"id"="ASC"})
     * @Groups({"export", "write:episode", "read:episode"})
     */
    private $clips;

    public function addClip(Clip $clip): void
    {
        $this->linkClip($clip);

        /** @var Episode $this */
        $clip->linkEpisode($this);
    }

    public function removeClip(Clip $clip): void
    {
        $this->unlinkClip($clip);
        $clip->unlinkEpisode();
    }

    public function getClips(): Collection
    {
        return $this->clips;
    }

    public function linkClip(Clip $clip): void
    {
        $this->clips[] = $clip;
    }

    public function unlinkClip(Clip $clip): void
    {
        $this->clips->removeElement($clip);
    }
}
