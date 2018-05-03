<?php namespace Fisdap\Data\Portfolio;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;

/**
 * Interface PortfolioUploadsRepository
 *
 * @package Fisdap\Data\Portfolio
 * @copyright 1996-2015 Headwaters Software, Inc.
 */

interface PortfolioUploadsRepository extends Repository {

    /**
     * @param int $studentid
     * @return mixed
     */
    public function getUploadedFilesForStudent($studentid);

}