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
use App\Entity\Person;
use App\Entity\Clip;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait ClipsHavePeople
 *
 * This trait defines the OWNING side of a ManyToMany bidirectional relationship.
 * 
 * 1. Requires owned `Person` entity to implement `$clips` property with `ManyToMany` and `mappedBy="characters"` annotation.
 * 2. Requires owned `Person` entity to implement `linkClip` and `unlinkClip` methods.
 * 3. Requires formType to own `'by_reference => false,` attribute to force use of `add` and `remove` methods.
 * 4. Entity constructor must initialize Collection object
 *     $this->characters = new Collection();
 *
 * @author "Matthias Morin" <mat@tangoman.io>
 */
trait ClipsHavePeople
{
    /**
     * @var Collection<Person>|null fictional person connected with a creative work
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Person", inversedBy="clip", cascade={"persist"})
     * @ApiProperty(iri="http://schema.org/character")
     * @Groups({"export", "write:clip", "read:clip"})
     */
    private $characters;

    public function addCharacter(Person $person): void
    {
        $this->linkPerson($person);

        /** @var Clip $this */
        $person->linkClip($this);
    }

    public function removeCharacter(Person $person): void
    {
        $this->unlinkPerson($person);

        /** @var Clip $this */
        $person->unlinkClip($this);
    }

    public function getCharacters(): Collection
    {
        return $this->characters;
    }

    public function linkPerson(Person $person): void
    {
        if (!$this->characters->contains($person)) {
            $this->characters[] = $person;
        }
    }

    public function unlinkPerson(Person $person): void
    {
        $this->characters->removeElement($person);
    }
}
