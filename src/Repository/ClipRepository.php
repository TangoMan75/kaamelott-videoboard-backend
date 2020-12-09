<?php

/**
 * This file is part of the TangoMan package.
 *
 * Copyright (c) 2020 "Matthias Morin" <mat@tangoman.io>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Clip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Clip|null find($id, $lockMode = null, $lockVersion = null)
 * @method Clip|null findOneBy(array $criteria, array $orderBy = null)
 * @method Clip[]    findAll()
 * @method Clip[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Clip::class);
    }
}
