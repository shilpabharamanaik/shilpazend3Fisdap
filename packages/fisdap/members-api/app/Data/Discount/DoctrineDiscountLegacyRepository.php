<?php namespace Fisdap\Data\Discount;

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineDiscountLegacyRepository
 *
 * @package Fisdap\Data\Coupon
 */
class DoctrineDiscountLegacyRepository extends DoctrineRepository implements DiscountLegacyRepository
{
	/**
	 * Get current discounts for the given program on the current date or given date
	 *
	 * @param $programId
	 * @param null|\DateTime $currentDate
	 * @return array
	 */
	public function getCurrentDiscounts($programId, \DateTime $currentDate = null)
	{
		if (!$currentDate) {
			$currentDate = new \DateTime();
		}

		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('d')
		   ->from('\Fisdap\Entity\DiscountLegacy', 'd')
		   ->where('d.program = ?1')
           ->andWhere('d.start_date <= ?2')
           ->andWhere('d.end_date >= ?2')
		   ->setParameter(1, $programId)
		   ->setParameter(2, $currentDate->format('Y-m-d'));

		return $qb->getQuery()->getResult();
	}
}