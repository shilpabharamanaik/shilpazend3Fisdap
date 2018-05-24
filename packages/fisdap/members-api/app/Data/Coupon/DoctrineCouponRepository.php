<?php namespace Fisdap\Data\Coupon;

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class CouponRepository
 *
 * @package Fisdap\Data\Coupon
 */
class DoctrineCouponRepository extends DoctrineRepository implements CouponRepository
{
	public function getCouponsByDateRange($start, $end)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('c')
		   ->from('\Fisdap\Entity\Coupon', 'c');
		   
		if ($start) {
			$qb->andWhere('c.start_date <= ?1')
			   ->setParameter(1, new \DateTime($end));
		}
		
		if ($end) {
			$qb->andWhere('c.end_date >= ?2')
			   ->setParameter(2, new \DateTime($start));
		}
		
		return $qb->getQuery()->getArrayResult();		
		
	}
	
}
