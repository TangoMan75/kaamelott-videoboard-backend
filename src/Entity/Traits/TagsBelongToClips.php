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
use App\Entity\Tag;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait TagsBelongToClips
 *
 * This trait defines the INVERSE side of a ManyToMany bidirectional relationship.
 *
 * 1. Requires `Clip` entity to implement `$tags` property with `ManyToMany` and `inversedBy="clips"` annotation.
 * 2. Requires `Clip` entity to implement `linkTag` and `unlinkTag` methods.
 * 3. Requires formType to own `'by_reference => false,` attribute to force use of `add` and `remove` methods.
 * 4. Entity constructor must initialize Collection object
 *     $this->clips = new ArrayCollection();
 *
 * @author  "Matthias Morin" <mat@tangoman.io>
 * @package App\Traits
 */
trait TagsBelongToClips
{
    /**
     * @var Collection<Clip>|null
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Clip", mappedBy="tags", cascade={"persist"})
     * @ORM\OrderBy({"name"="ASC"})
     * @Groups({"export", "write:tag", "read:tag"})
     */
    private $clips;

    public function addClip(Clip $clip): void
    {
        $this->linkClip($clip);
        /** @var Tag $this */
        $clip->linkTag($this);
    }

    public function removeClip(Clip $clip): void
    {
        $this->unlinkClip($clip);
        /** @var Tag $this */
        $clip->unlinkTag($this);
    }

    public function getClips(): Collection
    {
        return $this->clips;
    }

    public function linkClip(Clip $clip): void
    {
        if (!$this->clips->contains($clip)) {
            $this->clips[] = $clip;
        }
    }

    public function unlinkClip(Clip $clip): void
    {
        $this->clips->removeElement($clip);
    }
}
