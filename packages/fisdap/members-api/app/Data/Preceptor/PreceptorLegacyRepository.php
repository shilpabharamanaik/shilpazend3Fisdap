<?php namespace Fisdap\Data\Preceptor;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;
use Illuminate\Auth\AuthManager;


/**
 * Interface PreceptorLegacyRepository
 *
 * @package Fisdap\Data\Preceptor
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
interface PreceptorLegacyRepository extends Repository {

    public function getPreceptorsBySite($siteId, AuthManager $auth);
}