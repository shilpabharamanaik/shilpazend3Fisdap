<?php namespace Fisdap\Data\Base;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;


interface BaseLegacyRepository extends Repository
{
    public function getBaseAssociationsByProgramOptimized($siteId = null, $programId, $active = true, $type = null, $all = null);
}