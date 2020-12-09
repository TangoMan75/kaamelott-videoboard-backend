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
use App\Entity\Person;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait PeopleBelongToClips
 *
 * This trait defines the INVERSE side of a ManyToMany bidirectional relationship.
 *
 * 1. Requires `Clip` entity to implement `$people` property with `ManyToMany` and `inversedBy="clips"` annotation.
 * 2. Requires `Clip` entity to implement `linkPerson` and `unlinkPerson` methods.
 * 3. Requires formType to own `'by_reference => false,` attribute to force use of `add` and `remove` methods.
 * 4. Entity constructor must initialize Collection object
 *     $this->clips = new ArrayCollection();
 *
 * @author "Matthias Morin" <mat@tangoman.io>
 */
trait PeopleBelongToClips
{
    /**
     * @var Collection<Clip>|null Clip that this person is a performer or participant in
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Clip", mappedBy="characters", cascade={"persist"})
     * @Groups({"export", "write:person", "read:person"})
     */
    private $clips;

    public function addClip(Clip $clip): void
    {
        $this->linkClip($clip);

        /** @var Person $this */
        $clip->linkPerson($this);
    }

    public function removeClip(Clip $clip): void
    {
        $this->unlinkClip($clip);

        /** @var Person $this */
        $clip->unlinkPerson($this);
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
