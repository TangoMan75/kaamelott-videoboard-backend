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

namespace App\Factory;

/**
 * @author Matthias Morin <mat@tangoman.io>
 */
interface FactoryInterface
{
    /**
     * Create entity into database from normalized data or returns existing one
     *
     * @param array $normalizedEntity
     *
     * @return mixed
     */
    public function create(array $normalizedEntity);
}
