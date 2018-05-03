<?php namespace Fisdap\Data\Order;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Entity\Order;


/**
 * Class DoctrineOrderRepository
 *
 * @package Fisdap\Data\Order
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineOrderRepository extends DoctrineRepository implements OrderRepository
{
    public function getOrders($programId, $filters = array())
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('o')
            ->from('\Fisdap\Entity\Order', 'o')
            ->where("o.order_type = 1")
            ->andWhere("o.program = ?1")
            ->andWhere("o.completed = 1")
            ->orderBy("o.order_date", "DESC")
            ->setParameter(1, $programId);

        if (array_key_exists("startDate", $filters) && $filters['startDate']) {
            $startDate = new \DateTime($filters['startDate']);

            $qb->andWhere("o.order_date > ?2")
                ->setParameter(2, $startDate->format('Y-m-d'));
        }

        if (array_key_exists("endDate", $filters) && $filters['endDate']) {
            $endDate = new \DateTime($filters['endDate']);
            $qb->andWhere("o.order_date <= ?3")
                ->setParameter(3, $endDate->format('Y-m-d 23:59:59'));
        }

        return $qb->getQuery()->getResult();
    }

    public function getAnnualSalesByProgramAndYear($year)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p.id, p.name, p.state, sum(o.total_cost) as total')
            ->from('\Fisdap\Entity\Order', 'o')
            ->join('o.program', 'p')
            ->where("o.completed = 1")
            ->andWhere("o.staff_free_order = 0")
            ->andWhere("o.order_date >= ?1")
            ->andWhere("o.order_date <= ?2")
            ->andWhere("o.total_cost > 0")
            ->groupBy("p.id")
            ->setParameter(1, $year."-01-01 00:00:00")
            ->setParameter(2, $year."-12-31 23:59:59");

        return $qb->getQuery()->getResult();
    }

    public function getYearToDateSalesByProgram($year, $monthDay, $programid)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('sum(o.total_cost) as ytd')
            ->from('\Fisdap\Entity\Order', 'o')
            ->where("o.completed = 1")
            ->andWhere("o.staff_free_order = 0")
            ->andWhere("o.order_date >= ?1")
            ->andWhere("o.order_date <= ?2")
            ->andWhere("o.total_cost > 0")
            ->andWhere("o.program = ?3")
            ->setParameter(1, $year."-01-01 00:00:00")
            ->setParameter(2, $year."-".$monthDay." 23:59:59")
            ->setParameter(3, $programid);

        return $qb->getQuery()->getResult();
    }


    public function getOrderCountBeforeDate($programId, $date)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('count(o.id)')
            ->from('\Fisdap\Entity\Order', 'o')
            ->where('o.order_type = 1')
            ->andWhere('o.payment_method = 1')
            ->andWhere('o.program = ?1')
            ->andWhere('o.order_date < ?2')
            ->andWhere('o.order_date >= ?3')
            ->andWhere('o.completed = 1')
            ->setParameter(1, $programId)
            ->setParameter(2, $date->format("Y-m-d H:i:s"))
            ->setParameter(3, $date->format("Y-m-d 00:00:00"));

        return array_pop($qb->getQuery()->getSingleResult());
    }

    /**
     * @param integer $programId The program we're grabbing orders
     *
     * Only get orders that meet the following requirements:
     *    1). The program is purchasing the accounts
     *    2). The order did not generate product codes
     *    3). The order has been completed
     */
    public function getProgramOrderCount($programId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('count(o.id)')
            ->from('\Fisdap\Entity\Order', 'o')
            ->where('o.order_type = 1')
            ->andWhere('o.individual_purchase = 0')
            ->andWhere('o.program = ?1')
            ->andWhere('o.completed = 1')
            ->setParameter(1, $programId);

        return array_pop($qb->getQuery()->getSingleResult());
    }

	/**
	 * Get all completed orders that are not product codes, filterable by date range
	 *
	 * @param array $filters
	 * @return Order[]
	 */
	public function getAllOrders($filters = array())
	{
		$qb = $this->_em->createQueryBuilder();

        $qb->select("o", "p")
            ->from("\Fisdap\Entity\Order", "o")
            ->leftJoin("o.program", "p")
            ->andWhere("o.order_type != 2 OR o.order_type IS NULL")
            ->andWhere("o.completed = 1")
            ->orderBy("o.order_date", "DESC");

        if (array_key_exists("startDate", $filters) && $filters['startDate']) {
            $startDate = new \DateTime($filters['startDate']);
            $qb->andWhere("o.order_date > :startDate")
                ->setParameter("startDate", $startDate->format('Y-m-d'));
        }

        if (array_key_exists("endDate", $filters) && $filters['endDate']) {
            $endDate = new \DateTime($filters['endDate']);
            $qb->andWhere("o.order_date <= :endDate")
                ->setParameter("endDate", $endDate->format('Y-m-d 23:59:59'));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get all completed orders that failed when trying to export to Great Plains accounting
     *
     * @return array
     */
    public function getAllFailedAccountingExports()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("o.id, p.name, o.order_date")
            ->from('\Fisdap\Entity\Order', 'o')
            ->leftJoin('o.program', 'p')
            ->andWhere('o.completed = 1')
            ->andWhere('o.accounting_processed = 0')
            ->andWhere('o.staff_free_order = 0')
            ->orderBy('o.order_date', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }
}