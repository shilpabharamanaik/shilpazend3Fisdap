<?php namespace Fisdap\Data\Order;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\Order;


/**
 * Interface OrderRepository
 *
 * @package Fisdap\Data\Order
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
interface OrderRepository extends Repository {
    /**
     * Get all completed orders that are not product codes, filterable by date range
     *
     * @param array $filters
     * @return Order[]
     */
    public function getAllOrders($filters = array());

    /**
     * Get all completed orders that failed when trying to export to Great Plains accounting
     *
     * @return array
     */
    public function getAllFailedAccountingExports();
}