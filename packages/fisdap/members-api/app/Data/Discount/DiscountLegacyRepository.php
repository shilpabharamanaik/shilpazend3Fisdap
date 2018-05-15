<?php namespace Fisdap\Data\Discount;

use Fisdap\Data\Repository\Repository;

/**
 * Interface DiscountLegacyRepository
 *
 * @package Fisdap\Data\Discount
 */
interface DiscountLegacyRepository extends Repository
{
    /**
     * Get current discounts for the given program on the current date or given date
     *
     * @param $programId
     * @param null|\DateTime $currentDate
     * @return array
     */
    public function getCurrentDiscounts($programId, \DateTime $currentDate = null);
}
