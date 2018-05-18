<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for Report Categories.
 * 
 * @Entity
 * @Table(name="fisdap2_report_category")
 */
class ReportCategory extends Enumerated
{
	/**
     * @var Profession
     * @ManyToOne(targetEntity="Profession")
     */
    protected $profession;
	
}