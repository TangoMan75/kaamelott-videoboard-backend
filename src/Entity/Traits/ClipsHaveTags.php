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

use App\Entity\Tag;
use App\Entity\Clip;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Trait ClipsHaveTags
 *
 * This trait defines the OWNING side of a ManyToMany bidirectional relationship.
 *
 * 1. Requires owned `Tag` entity to implement `$clips` property with `ManyToMany` and `mappedBy="tags"` annotation.
 * 2. Requires owned `Tag` entity to implement `linkClip` and `unlinkClip` methods.
 * 3. Requires formType to own `'by_reference => false,` attribute to force use of `add` and `remove` methods.
 * 4. Entity constructor must initialize Collection object
 *     $this->tags = new Collection();
 *
 * @author  "Matthias Morin" <mat@tangoman.io>
 * @package App\Traits
 */
trait ClipsHaveTags
{
    /**
     * @var Collection<Tag>|null
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="clips", cascade={"persist"})
     * @ORM\OrderBy({"name"="ASC"})
     * @Groups({"export", "write:clip", "read:clip"})
     */
    private $tags;

    public function addTag(Tag $tag): void
    {
        $this->linkTag($tag);

        /** @var Clip $this */
        $tag->linkClip($this);
    }

    public function removeTag(Tag $tag): void
    {
        $this->unlinkTag($tag);

        /** @var Clip $this */
        $tag->unlinkClip($this);
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function linkTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }
    }

    public function unlinkTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }
}
